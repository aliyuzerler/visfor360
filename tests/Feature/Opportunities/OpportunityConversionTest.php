<?php

namespace Tests\Feature\Opportunities;

use App\Domain\Checkups\Actions\CheckupService;
use App\Domain\Checkups\Models\CheckupQuestion;
use App\Domain\Checkups\Models\CheckupRule;
use App\Domain\Checkups\Models\CheckupSession;
use App\Domain\Checkups\Models\CheckupTemplate;
use App\Domain\Customers\Models\Customer;
use App\Domain\Opportunities\Models\Opportunity;
use App\Domain\Projects\Models\Pipeline;
use App\Domain\Projects\Models\PipelineStage;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ProjectStageHistory;
use App\Domain\Projects\Models\ServiceType;
use App\Models\AuditEvent;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OpportunityConversionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private CheckupTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password']);

        $this->template = CheckupTemplate::create(['key' => 'conv-template', 'title' => 'ÖRNEK Şablon', 'status' => 'published']);
        CheckupQuestion::create(['template_id' => $this->template->id, 'key' => 'cihaz_sayisi', 'label' => 'Cihaz sayısı', 'type' => 'number', 'required' => true, 'sort_order' => 1]);
        CheckupRule::create([
            'template_id' => $this->template->id,
            'label' => 'Altyapı önerisi',
            'priority' => 10,
            'condition_groups' => [['conditions' => [['question_key' => 'cihaz_sayisi', 'operator' => 'gte', 'value' => 50]]]],
            'recommendation_service_key' => 'sv_altyapi',
            'recommendation_label' => 'Altyapı Değerlendirmesi (ÖRNEK)',
        ]);
    }

    private function approvedOpportunity(Customer $customer): Opportunity
    {
        $session = CheckupSession::create([
            'template_id' => $this->template->id,
            'template_version' => $this->template->version,
            'customer_id' => $customer->id,
            'started_by' => $this->admin->id,
        ]);
        app(CheckupService::class)->complete($session, ['cihaz_sayisi' => 80], $this->admin);

        $this->post('/app/checkups/'.$session->results()->firstOrFail()->id.'/import')->assertRedirect();
        $opportunity = Opportunity::query()->where('customer_id', $customer->id)->firstOrFail();

        $this->patch('/app/opportunities/'.$opportunity->id.'/decide', [
            'status' => 'approved',
            'discussion_note' => 'Müşteriyle birebir görüşme yapıldı; kapsamı onayladı.',
        ])->assertRedirect();

        return $opportunity->refresh();
    }

    private function createServiceType(string $key = 'sv_altyapi'): ServiceType
    {
        $pipeline = Pipeline::create(['key' => 'conv-pipe-'.substr(md5($key), 0, 6), 'label' => 'Dönüşüm Şablonu', 'is_example' => true]);
        PipelineStage::create(['pipeline_id' => $pipeline->id, 'key' => 'baslangic', 'label' => 'Başlangıç', 'sort_order' => 1, 'is_active' => true]);
        $department = Department::create(['name' => 'Altyapı Ekibi', 'code' => 'alt-'.substr(md5($key), 0, 4)]);

        return ServiceType::create(['key' => $key, 'label' => 'Altyapı Hizmeti', 'pipeline_id' => $pipeline->id, 'department_id' => $department->id]);
    }

    #[Test]
    public function onaysiz_firsat_donusemez(): void
    {
        $customer = Customer::create(['name' => 'Onaysız Firma']);
        $session = CheckupSession::create([
            'template_id' => $this->template->id,
            'template_version' => $this->template->version,
            'customer_id' => $customer->id,
            'started_by' => $this->admin->id,
        ]);
        app(CheckupService::class)->complete($session, ['cihaz_sayisi' => 80], $this->admin);
        $this->post('/app/checkups/'.$session->results()->firstOrFail()->id.'/import')->assertRedirect();
        $opportunity = Opportunity::query()->firstOrFail(); // review_pending

        $this->createServiceType();
        $this->post('/app/opportunities/'.$opportunity->id.'/convert')->assertSessionHasErrors('convert');

        $this->assertSame(0, Project::query()->count());
        $this->assertNull($opportunity->refresh()->converted_project_id);
    }

    #[Test]
    public function mutabakat_kaydi_eksikse_donusemez(): void
    {
        $customer = Customer::create(['name' => 'Mutabakatsız Firma']);
        $session = CheckupSession::create([
            'template_id' => $this->template->id,
            'template_version' => $this->template->version,
            'customer_id' => $customer->id,
            'started_by' => $this->admin->id,
        ]);
        app(CheckupService::class)->complete($session, ['cihaz_sayisi' => 80], $this->admin);
        $this->post('/app/checkups/'.$session->results()->firstOrFail()->id.'/import')->assertRedirect();
        $opportunity = Opportunity::query()->firstOrFail();

        // Statusu kabaca approved'a çekersek bile mutabakat notu boş → red
        $opportunity->update(['status' => 'approved']);

        $this->createServiceType();
        $this->post('/app/opportunities/'.$opportunity->id.'/convert')->assertSessionHasErrors('convert');

        $this->assertSame(0, Project::query()->count());
    }

    #[Test]
    public function yetkisiz_rol_donusemez(): void
    {
        $customer = Customer::create(['name' => 'Yetki Firması']);
        $opportunity = $this->approvedOpportunity($customer);
        $this->createServiceType();

        // Finans kullanıcısı: opportunities.approve ve projects.create yok
        $finans = User::factory()->create();
        $finans->roles()->attach(Role::query()->where('key', 'finans-kullanicisi')->first());
        $this->post('/logout');
        $this->post('/login', ['email' => $finans->email, 'password' => 'password']);

        $this->post('/app/opportunities/'.$opportunity->id.'/convert')->assertForbidden();
        $this->assertSame(0, Project::query()->count());
    }

    #[Test]
    public function ayni_istek_tekrarlandiginda_tek_proje_olusur_idempotent(): void
    {
        $customer = Customer::create(['name' => 'Idempotent Firma']);
        $opportunity = $this->approvedOpportunity($customer);
        $this->createServiceType();

        $this->post('/app/opportunities/'.$opportunity->id.'/convert')->assertRedirect();
        $this->post('/app/opportunities/'.$opportunity->id.'/convert')->assertRedirect(); // retry/çift tıklama
        $this->post('/app/opportunities/'.$opportunity->id.'/convert')->assertRedirect();

        $this->assertSame(1, Project::query()->count());
        $this->assertNotNull($opportunity->refresh()->converted_project_id);
    }

    #[Test]
    public function proje_dogru_musteri_hizmet_departman_pipeline_ile_olusur(): void
    {
        $customer = Customer::create(['name' => 'Doğru Bağlantı Firması']);
        $opportunity = $this->approvedOpportunity($customer);
        $serviceType = $this->createServiceType();

        $this->post('/app/opportunities/'.$opportunity->id.'/convert')->assertRedirect();

        $project = Project::query()->firstOrFail();
        $this->assertSame($customer->id, $project->customer_id);
        $this->assertSame($serviceType->id, $project->service_type_id);
        $this->assertSame($serviceType->department_id, $project->department_id);
        $this->assertSame($serviceType->pipeline_id, $project->currentStage->pipeline_id);
        $this->assertSame('Başlangıç', $project->currentStage->label);

        // Kaynak geri erişimi: proje → fırsat → check-up oturumu
        $this->assertSame($opportunity->id, Opportunity::query()->where('converted_project_id', $project->id)->firstOrFail()->id);
    }

    #[Test]
    public function hata_durumunda_rollback_yapilir_yarim_veri_kalmaz(): void
    {
        $customer = Customer::create(['name' => 'Rollback Firması']);
        $opportunity = $this->approvedOpportunity($customer);

        // Geçersiz mapping: service_key eşleşen ServiceType yok → exception + rollback
        $this->post('/app/opportunities/'.$opportunity->id.'/convert')->assertSessionHasErrors('convert');

        $this->assertSame(0, Project::query()->count());
        $this->assertSame(0, ProjectStageHistory::query()->count());
        $this->assertNull($opportunity->refresh()->converted_project_id);
        $this->assertSame('approved', $opportunity->status); // fırsat onaysız/yarım kalmadı
    }

    #[Test]
    public function donusum_actor_time_ve_kaynak_auditli(): void
    {
        $customer = Customer::create(['name' => 'Audit Dönüşüm']);
        $opportunity = $this->approvedOpportunity($customer);
        $this->createServiceType();

        $this->post('/app/opportunities/'.$opportunity->id.'/convert')->assertRedirect();

        $opportunity->refresh();
        $this->assertNotNull($opportunity->converted_at);
        $this->assertSame($this->admin->id, $opportunity->converted_by);
        $this->assertTrue(AuditEvent::query()->where('action', 'opportunity_converted')->where('auditable_id', $opportunity->id)->exists());

        // Proje aşama geçmişi conversion kaynağıyla
        $this->assertDatabaseHas('project_stage_history', ['project_id' => $opportunity->converted_project_id, 'from_stage_id' => null]);
    }
}
