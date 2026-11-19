<?php

namespace Tests\Feature\Documents;

use App\Domain\Customers\Models\Customer;
use App\Domain\Documents\Actions\DocumentService;
use App\Domain\Documents\Models\Document;
use App\Domain\Documents\Models\DocumentType;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password']);

        DocumentType::create(['key' => 'vergi-levhasi', 'label' => 'Vergi Levhası', 'required' => true, 'validity_warn_days' => 30]);
        DocumentType::create(['key' => 'kapasite-raporu', 'label' => 'Kapasite Raporu', 'required' => false, 'validity_warn_days' => 30]);
    }

    #[Test]
    public function pdf_yuklemesi_private_diske_yazar_ve_rastgele_anahtar_kullanir(): void
    {
        Storage::fake('local');
        $customer = Customer::create(['name' => 'Firma']);

        $this->post('/app/customers/'.$customer->id.'/documents', [
            'document_type_id' => DocumentType::query()->first()->id,
            'file' => UploadedFile::fake()->create('vergi.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $document = Document::query()->first();
        $this->assertNotNull($document);
        $this->assertStringStartsWith('documents/'.$customer->id.'/', $document->storage_key);
        $this->assertStringNotContainsString('vergi', $document->storage_key); // rastgele ad
        $this->assertSame('vergi.pdf', $document->original_name); // yalnız güvenli metadata
        $this->assertTrue(Storage::disk('local')->exists($document->storage_key));
    }

    #[Test]
    public function izin_verilmeyen_dosya_turu_reddedilir(): void
    {
        $customer = Customer::create(['name' => 'Firma']);

        $this->post('/app/customers/'.$customer->id.'/documents', [
            'document_type_id' => DocumentType::query()->first()->id,
            'file' => UploadedFile::fake()->create('zararli.exe', 100, 'application/octet-stream'),
        ])->assertSessionHasErrors('file');

        $this->assertSame(0, Document::query()->count());
    }

    #[Test]
    public function fazla_buyuk_dosya_reddedilir(): void
    {
        $customer = Customer::create(['name' => 'Firma']);

        $this->post('/app/customers/'.$customer->id.'/documents', [
            'document_type_id' => DocumentType::query()->first()->id,
            'file' => UploadedFile::fake()->create('buyuk.pdf', 10240 + 5, 'application/pdf'),
        ])->assertSessionHasErrors('file');
    }

    #[Test]
    public function yeni_surum_oncekini_supersede_eder_kayit_korunur(): void
    {
        Storage::fake('local');
        $customer = Customer::create(['name' => 'Firma']);
        $type = DocumentType::query()->first();

        $this->post('/app/customers/'.$customer->id.'/documents', [
            'document_type_id' => $type->id,
            'file' => UploadedFile::fake()->create('eski.pdf', 100, 'application/pdf'),
        ]);
        $this->post('/app/customers/'.$customer->id.'/documents', [
            'document_type_id' => $type->id,
            'file' => UploadedFile::fake()->create('yeni.pdf', 100, 'application/pdf'),
        ]);

        $this->assertSame(2, Document::query()->count());
        $old = Document::query()->where('original_name', 'eski.pdf')->first();
        $new = Document::query()->where('original_name', 'yeni.pdf')->first();

        $this->assertNotNull($old->superseded_at);
        $this->assertSame($new->id, $old->superseded_by);
    }

    #[Test]
    public function izinsiz_kullanici_indiremez(): void
    {
        Storage::fake('local');
        $customer = Customer::create(['name' => 'Firma']);
        $document = app(DocumentService::class)->upload(
            $customer,
            DocumentType::query()->first(),
            UploadedFile::fake()->create('gizli.pdf', 100, 'application/pdf'),
            $this->admin,
        );

        $plain = User::factory()->create();
        $this->post('/logout');
        $this->post('/login', ['email' => $plain->email, 'password' => 'password']);

        $this->get('/app/documents/'.$document->id.'/download')->assertForbidden();
    }

    #[Test]
    public function yetkili_kullanici_indirebilir(): void
    {
        Storage::fake('local');
        $customer = Customer::create(['name' => 'Firma']);
        $document = app(DocumentService::class)->upload(
            $customer,
            DocumentType::query()->first(),
            UploadedFile::fake()->create('rapor.pdf', 100, 'application/pdf'),
            $this->admin,
        );

        $this->get('/app/documents/'.$document->id.'/download')->assertOk();
    }

    #[Test]
    public function eksik_ve_suresi_gecen_evrap_ayri_durumlar(): void
    {
        $customer = Customer::create(['name' => 'Firma']);
        $service = app(DocumentService::class);
        $vergi = DocumentType::query()->where('key', 'vergi-levhasi')->first();

        // Henüz yükleme yok → zorunlu evrak eksik
        $rows = $service->statusFor($customer);
        $this->assertSame('missing', collect($rows)->firstWhere('type.id', $vergi->id)['status']);

        // Süresi dolmuş belge → expired
        $service->upload($customer, $vergi, UploadedFile::fake()->create('vergi.pdf', 100, 'application/pdf'), $this->admin, now()->subDay());
        $rows = $service->statusFor($customer);
        $this->assertSame('expired', collect($rows)->firstWhere('type.id', $vergi->id)['status']);

        // Yakında dolacak → expiring_soon
        $service->upload($customer, $vergi, UploadedFile::fake()->create('vergi-yeni.pdf', 100, 'application/pdf'), $this->admin, now()->addDays(10));
        $rows = $service->statusFor($customer);
        $this->assertSame('expiring_soon', collect($rows)->firstWhere('type.id', $vergi->id)['status']);
    }
}
