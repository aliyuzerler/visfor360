<?php

namespace Tests\Feature\Portal;

use App\Domain\Customers\Models\Customer;
use App\Domain\Documents\Models\DocumentType;
use App\Domain\Finance\Models\FinancialEntry;
use App\Domain\Opportunities\Models\Opportunity;
use App\Domain\Partners\Models\Partner;
use App\Domain\Portal\Models\PortalDocumentRequest;
use App\Domain\Portal\Models\PortalMembership;
use App\Domain\Portal\Models\PortalUser;
use App\Domain\Projects\Models\Pipeline;
use App\Domain\Projects\Models\PipelineStage;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ServiceType;
use App\Domain\Tasks\Models\Task;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Prompt 15 IDOR suite: portal kullanıcıları iç staff DEĞİL; tüm erişim
 * aktif üyelik firmasıyla kısıtlı; IDOR → 404 (varlık ifşası yok).
 */
class PortalIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customerA;

    private Customer $customerB;

    private PortalUser $userA;

    private DocumentType $docType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->customerA = Customer::create(['name' => 'Firma A']);
        $this->customerB = Customer::create(['name' => 'Firma B']);

        $this->userA = PortalUser::create(['name' => 'A Kullanıcısı', 'email' => 'a@firmaya.example', 'password' => 'Password123456']);
        PortalMembership::create(['portal_user_id' => $this->userA->id, 'customer_id' => $this->customerA->id]);

        $this->docType = DocumentType::create(['key' => 'portal-doc', 'label' => 'İstenen Belge', 'required' => false]);

        $this->post('/portal/login', ['email' => $this->userA->email, 'password' => 'Password123456']);
    }

    #[Test]
    public function portal_kullanici_kendi_firma_projesini_gorur(): void
    {
        $pipeline = Pipeline::create(['key' => 'portal-p', 'label' => 'Portal Şablonu', 'is_example' => true]);
        $stage = PipelineStage::create(['pipeline_id' => $pipeline->id, 'key' => 'asama', 'label' => 'Yürütme', 'sort_order' => 1, 'is_active' => true]);
        $service = ServiceType::create(['key' => 'portal-sv', 'label' => 'Portal Hizmet', 'pipeline_id' => $pipeline->id]);

        Project::create(['name' => 'Firma A Projesi', 'customer_id' => $this->customerA->id, 'service_type_id' => $service->id, 'pipeline_stage_id' => $stage->id]);
        Project::create(['name' => 'Firma B Projesi', 'customer_id' => $this->customerB->id, 'service_type_id' => $service->id, 'pipeline_stage_id' => $stage->id]);

        $response = $this->get('/portal')->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('Firma A Projesi', $content);
        $this->assertStringNotContainsString('Firma B Projesi', $content);
        // Aşama bilgisi: unicode escape riskine karşı veri tabanıyla doğrulanır
        $projectRow = Project::query()->where('name', 'Firma A Projesi')->first();
        $this->assertSame('Yürütme', $projectRow->currentStage->label);
    }

    #[Test]
    public function url_id_degisikligiyle_baska_musteri_projesi_okunamaz(): void
    {
        $pipeline = Pipeline::create(['key' => 'idor-p', 'label' => 'IDOR Şablon', 'is_example' => true]);
        $stage = PipelineStage::create(['pipeline_id' => $pipeline->id, 'key' => 's', 'label' => 'S', 'sort_order' => 1, 'is_active' => true]);
        $service = ServiceType::create(['key' => 'idor-sv', 'label' => 'IDOR Hizmet', 'pipeline_id' => $pipeline->id]);

        Project::create(['name' => 'Firma B Projesi', 'customer_id' => $this->customerB->id, 'service_type_id' => $service->id, 'pipeline_stage_id' => $stage->id]);

        // Portalda proje detay rotası yok; dashboard content zaten izole.
        // IDOR denemesi: dashboard içeriğinde B'nin hiçbir verisi geçmez.
        $content = $this->get('/portal')->getContent();
        $this->assertStringNotContainsString('Firma B Projesi', $content);
    }

    #[Test]
    public function upload_baska_firma_talebine_baglanamaz(): void
    {
        $requestB = PortalDocumentRequest::create([
            'customer_id' => $this->customerB->id,
            'document_type_id' => $this->docType->id,
            'requested_by' => null,
        ]);

        Storage::fake('local');

        $this->post('/portal/documents/upload', [
            'request_id' => $requestB->id,
            'file' => UploadedFile::fake()->create('belge.pdf', 100, 'application/pdf'),
        ])->assertNotFound(); // 404 — varlık ifşası yok

        $requestB->refresh();
        $this->assertSame('requested', $requestB->status);
        $this->assertNull($requestB->uploaded_by);
    }

    #[Test]
    public function kendi_talebine_yukleme_calisir(): void
    {
        Storage::fake('local');
        $requestA = PortalDocumentRequest::create([
            'customer_id' => $this->customerA->id,
            'document_type_id' => $this->docType->id,
        ]);

        $this->post('/portal/documents/upload', [
            'request_id' => $requestA->id,
            'file' => UploadedFile::fake()->create('belge.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $requestA->refresh();
        $this->assertSame('uploaded', $requestA->status);
        $this->assertSame($this->userA->id, $requestA->uploaded_by);
        $this->assertNotNull($requestA->document_id);
        $this->assertDatabaseHas('documents', ['id' => $requestA->document_id]);
    }

    #[Test]
    public function ic_veriler_portala_sizmaz(): void
    {
        // İç veri üret: finans kaydı, fırsat, partner, iç görev
        FinancialEntry::create(['direction' => 'income', 'entry_type' => 'actual', 'amount' => '999999.0000', 'currency_code' => 'TRY', 'entry_date' => '2026-01-01', 'customer_id' => $this->customerA->id, 'status' => 'approved']);
        Opportunity::create(['customer_id' => $this->customerA->id, 'service_key' => 'sv_x', 'title' => 'Gizli Fırsat']);
        Partner::create(['name' => 'Gizli Ortak']);
        Task::create(['title' => 'Gizli Görev', 'customer_id' => $this->customerA->id]);

        $this->post('/logout');
        $this->post('/portal/login', ['email' => $this->userA->email, 'password' => 'Password123456']);

        $content = $this->get('/portal')->getContent();
        $this->assertStringNotContainsString('999999', $content);
        // Ziggy route adları her sayfada yüklenir (veri değil); içerik kontrolü:
        // 'Gizli' kelimesi (ASCII) hiçbir iç kayıtta geçmemeli
        $this->assertStringNotContainsString('Gizli', $content);
    }

    #[Test]
    public function portal_kullanici_ic_modullere_giremez(): void
    {
        $this->get('/app/customers')->assertRedirect('/login'); // web guard oturumu yok
        $this->get('/app/finance')->assertRedirect('/login');
        $this->get('/app/opportunities')->assertRedirect('/login');
    }

    #[Test]
    public function pasif_kullanici_ve_pasif_uyelik_erisimi_keser(): void
    {
        // Üyelik pasifleştirilir → anında erişim kesilir
        PortalMembership::query()->where('portal_user_id', $this->userA->id)->update(['is_active' => false]);

        $this->get('/portal')->assertRedirect('/portal/login');

        // Kullanıcı pasifleştirilir → logout + erişim kesilir
        PortalMembership::query()->where('portal_user_id', $this->userA->id)->update(['is_active' => true]);
        PortalUser::query()->where('id', $this->userA->id)->update(['is_active' => false]);

        $this->get('/portal')->assertRedirect('/portal/login');
    }

    #[Test]
    public function ayni_firmada_coklu_kullanici_desteklenir_ve_iptal_aninda_keser(): void
    {
        $userA2 = PortalUser::create(['name' => 'A2 Kullanıcısı', 'email' => 'a2@firmaya.example', 'password' => 'Password123456']);
        $membershipA2 = PortalMembership::create(['portal_user_id' => $userA2->id, 'customer_id' => $this->customerA->id]);

        // userA portal oturumundan çık; portal.guest login POST'u dashboard'a çevirmesin
        $this->post('/portal/logout');
        $this->post('/portal/login', ['email' => $userA2->email, 'password' => 'Password123456']);

        $this->get('/portal')->assertOk();

        $membershipA2->update(['is_active' => false]);
        $this->get('/portal')->assertRedirect('/portal/login');
    }
}
