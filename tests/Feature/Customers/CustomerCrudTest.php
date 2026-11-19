<?php

namespace Tests\Feature\Customers;

use App\Domain\Customers\Models\Contact;
use App\Domain\Customers\Models\Customer;
use App\Domain\Customers\Models\CustomerType;
use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
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
    }

    #[Test]
    public function izinsiz_kullanici_musteri_ekranlarina_giremez(): void
    {
        $plain = User::factory()->create();
        $this->post('/logout');
        $this->post('/login', ['email' => $plain->email, 'password' => 'password']);

        $this->get('/app/customers')->assertForbidden();
        $this->get('/app/customers/create')->assertForbidden();
        $this->post('/app/customers', ['name' => 'X'])->assertForbidden();
    }

    #[Test]
    public function musteri_olusturma_calisir_ve_audit_yazar(): void
    {
        $type = CustomerType::create(['key' => 'test-tip', 'label' => 'Test Tipi']);

        $this->post('/app/customers', [
            'name' => 'Örnek Ltd. Şti.',
            'customer_type_id' => $type->id,
            'tax_number' => '1234567890',
            'city' => 'İstanbul',
        ])->assertRedirect();

        $customer = Customer::query()->where('name', 'Örnek Ltd. Şti.')->first();
        $this->assertNotNull($customer);
        $this->assertSame($type->id, $customer->customer_type_id);

        $this->assertTrue(
            AuditEvent::query()->where('action', 'created')->where('auditable_id', $customer->id)->exists()
        );
    }

    #[Test]
    public function arama_filtre_sayfalama_sorgu_seviyesinde_calisir(): void
    {
        Customer::create(['name' => 'Ankara Yapi']);
        Customer::create(['name' => 'Izmir Gida']);

        $this->get('/app/customers?search=Ankara')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('customers/index')
                ->where('customers.data.0.name', 'Ankara Yapi'));
    }

    #[Test]
    public function musteri_arsivlenince_kayit_ve_audit_gecmisi_korunur(): void
    {
        $customer = Customer::create(['name' => 'Arsivlenecek']);
        $this->patch('/app/customers/'.$customer->id.'/archive')->assertRedirect();

        $this->assertNotNull($customer->fresh()->archived_at);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]); // hard delete yok

        // Arşiv öncesi audit olayları hâlâ erişilebilir
        $this->assertTrue(AuditEvent::query()->where('auditable_id', $customer->id)->exists());
    }

    #[Test]
    public function yetkili_ekleme_guncelleme_arşivleme_akisi(): void
    {
        $customer = Customer::create(['name' => 'Firma A']);

        $this->post('/app/customers/'.$customer->id.'/contacts', [
            'name' => 'Ali Yetkili',
            'email' => 'ali@firma.example',
            'is_primary' => true,
        ])->assertRedirect();

        $contact = Contact::query()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($contact);

        $this->put('/app/contacts/'.$contact->id, [
            'name' => 'Ali Yetkili Güncel',
        ])->assertRedirect();

        $this->assertSame('Ali Yetkili Güncel', $contact->fresh()->name);

        $this->patch('/app/contacts/'.$contact->id.'/archive')->assertRedirect();
        $this->assertSoftDeleted($contact);
    }

    #[Test]
    public function buyuk_listede_query_sayisi_sabit_kalir_n1_yok(): void
    {
        Customer::factory()->count(30)->create();

        DB::enableQueryLog();
        $this->get('/app/customers?page=1')->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Query sayısı kayıt sayısıyla artmamalı (eager loading'li sabit maliyet)
        $this->assertLessThan(15, $queries, "N+1 saptandı: $queries sorgu");
    }
}
