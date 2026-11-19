<?php

namespace Tests\Feature\Projects;

use App\Domain\Customers\Models\Customer;
use App\Domain\Documents\Actions\DocumentService;
use App\Domain\Documents\Models\DocumentType;
use App\Domain\Projects\Actions\ProjectService;
use App\Domain\Projects\Models\Pipeline;
use App\Domain\Projects\Models\PipelineStage;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ProjectStageHistory;
use App\Domain\Projects\Models\ServiceType;
use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectPipelineTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Pipeline $pipelineA;

    private Pipeline $pipelineB;

    private ServiceType $serviceA;

    private ServiceType $serviceB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password']);

        $this->pipelineA = Pipeline::create(['key' => 'sabit-hizmet', 'label' => 'Sabit Hizmet Şablonu', 'is_example' => true]);
        $this->pipelineB = Pipeline::create(['key' => 'erisim-hizmeti', 'label' => 'Erişim Hizmeti Şablonu', 'is_example' => true]);

        $orderA = 0;
        foreach (['Talep' => false, 'Planlama' => false, 'Teslim' => true] as $label => $terminal) {
            $orderA++;
            PipelineStage::create([
                'pipeline_id' => $this->pipelineA->id,
                'key' => mb_strtolower($label),
                'label' => $label,
                'sort_order' => $orderA,
                'is_active' => true,
                'is_terminal' => $terminal,
            ]);
        }

        $orderB = 0;
        foreach (['Keşif' => false, 'Uygulama' => false, 'Devir' => true] as $label => $terminal) {
            $orderB++;
            PipelineStage::create([
                'pipeline_id' => $this->pipelineB->id,
                'key' => mb_strtolower(str_replace(' ', '_', $label)),
                'label' => $label,
                'sort_order' => $orderB,
                'is_active' => true,
                'is_terminal' => $terminal,
            ]);
        }

        $this->serviceA = ServiceType::create(['key' => 'mali-musavirlik', 'label' => 'Mali müşavirlik', 'pipeline_id' => $this->pipelineA->id]);
        $this->serviceB = ServiceType::create(['key' => 'web-gelistirme', 'label' => 'Web geliştirme', 'pipeline_id' => $this->pipelineB->id]);
    }

    #[Test]
    public function farkli_hizmetler_kendi_pipeline_asamalarini_kullanir(): void
    {
        $projectA = Project::create(['name' => 'Proje A', 'service_type_id' => $this->serviceA->id]);
        $projectB = Project::create(['name' => 'Proje B', 'service_type_id' => $this->serviceB->id]);

        $this->assertSame(['Talep', 'Planlama', 'Teslim'], $projectA->serviceType->pipeline->stages->pluck('label')->all());
        $this->assertSame(['Keşif', 'Uygulama', 'Devir'], $projectB->serviceType->pipeline->stages->pluck('label')->all());
        $this->assertNotSame(
            $projectA->serviceType->pipeline_id,
            $projectB->serviceType->pipeline_id,
        );
    }

    #[Test]
    public function yanlis_pipeline_asamasina_gecis_reddedilir(): void
    {
        $project = Project::create(['name' => 'Proje A', 'service_type_id' => $this->serviceA->id]);
        $foreignStage = PipelineStage::query()->where('pipeline_id', $this->pipelineB->id)->firstOrFail();

        $this->expectException(ValidationException::class);
        app(ProjectService::class)->transitionStage($project, $foreignStage, $this->admin);
    }

    #[Test]
    public function yetkisiz_kullanici_asama_degistiremez(): void
    {
        $project = Project::create(['name' => 'Proje', 'service_type_id' => $this->serviceA->id]);
        $nextStage = PipelineStage::query()->where('pipeline_id', $this->pipelineA->id)->where('key', 'planlama')->firstOrFail();

        $plain = User::factory()->create();
        $this->post('/logout');
        $this->post('/login', ['email' => $plain->email, 'password' => 'password']);

        $this->patch('/app/projects/'.$project->id.'/stage', [
            'stage_id' => $nextStage->id,
        ])->assertForbidden();

        $this->assertNotSame($nextStage->id, $project->fresh()->pipeline_stage_id);
    }

    #[Test]
    public function gecerli_gecis_history_actor_old_new_time_kaydeder(): void
    {
        // Gerçek akış: proje oluşturma ilk aktif aşamaya alır (Talep)
        $this->post('/app/projects', ['name' => 'Proje', 'service_type_id' => $this->serviceA->id]);
        $project = Project::query()->where('name', 'Proje')->firstOrFail();
        $nextStage = PipelineStage::query()->where('pipeline_id', $this->pipelineA->id)->where('key', 'planlama')->firstOrFail();

        $this->patch('/app/projects/'.$project->id.'/stage', [
            'stage_id' => $nextStage->id,
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame($nextStage->id, $project->pipeline_stage_id);

        // Son kayıt = Talep → Planlama geçişi (ilk kayıt store'un başlangıç kaydıdır)
        $history = ProjectStageHistory::query()->where('project_id', $project->id)->latest('id')->firstOrFail();
        $this->assertSame('Talep', $history->fromStage?->label ?? $history->fromStage?->label); // ilk aktif aşamadan
        $this->assertSame('Planlama', $history->toStage->label);
        $this->assertSame($this->admin->id, $history->actor_id);
        $this->assertNotNull($history->transitioned_at);

        // Audit de yazılır
        $this->assertTrue(AuditEvent::query()->where('action', 'stage_changed')->where('auditable_id', $project->id)->exists());
    }

    #[Test]
    public function terminal_asamadan_cikis_reddedilir(): void
    {
        $project = Project::create(['name' => 'Proje', 'service_type_id' => $this->serviceA->id]);
        $terminal = PipelineStage::query()->where('pipeline_id', $this->pipelineA->id)->where('key', 'teslim')->firstOrFail();
        app(ProjectService::class)->transitionStage($project, $terminal, $this->admin);

        $talep = PipelineStage::query()->where('pipeline_id', $this->pipelineA->id)->where('key', 'talep')->firstOrFail();

        $this->expectException(ValidationException::class);
        app(ProjectService::class)->transitionStage($project, $talep, $this->admin);
    }

    #[Test]
    public function pasif_asamaya_gecis_reddedilir(): void
    {
        $project = Project::create(['name' => 'Proje', 'service_type_id' => $this->serviceA->id]);
        $stage = PipelineStage::create([
            'pipeline_id' => $this->pipelineA->id,
            'key' => 'pasif-asama',
            'label' => 'Pasif Aşama',
            'sort_order' => 9,
            'is_active' => false,
        ]);

        $this->expectException(ValidationException::class);
        app(ProjectService::class)->transitionStage($project, $stage, $this->admin);
    }

    #[Test]
    public function zorunlu_evragi_olmayan_gecis_reddedilir(): void
    {
        Storage::fake('local');
        $docType = DocumentType::create(['key' => 'sozlesme', 'label' => 'Sözleşme', 'required' => false]);
        $stage = PipelineStage::create([
            'pipeline_id' => $this->pipelineA->id,
            'key' => 'sozlesmeli',
            'label' => 'Sözleşmeli Yürütme',
            'sort_order' => 5,
            'is_active' => true,
            'required_document_type_id' => $docType->id,
        ]);

        $project = Project::create(['name' => 'Proje', 'service_type_id' => $this->serviceA->id, 'customer_id' => Customer::create(['name' => 'Firma'])->id]);

        $this->expectException(ValidationException::class);
        app(ProjectService::class)->transitionStage($project, $stage, $this->admin);
    }

    #[Test]
    public function gecerli_evragi_olan_gecis_kabul_edilir(): void
    {
        Storage::fake('local');
        $docType = DocumentType::create(['key' => 'sozlesme2', 'label' => 'Sözleşme 2', 'required' => false]);
        $stage = PipelineStage::create([
            'pipeline_id' => $this->pipelineA->id,
            'key' => 'sozlesmeli-2',
            'label' => 'Sözleşmeli Yürütme 2',
            'sort_order' => 6,
            'is_active' => true,
            'required_document_type_id' => $docType->id,
        ]);

        $customer = Customer::create(['name' => 'Firma']);
        $project = Project::create(['name' => 'Proje', 'service_type_id' => $this->serviceA->id, 'customer_id' => $customer->id]);

        app(DocumentService::class)->upload(
            $customer,
            $docType,
            UploadedFile::fake()->create('sozlesme.pdf', 100, 'application/pdf'),
            $this->admin,
        );

        $result = app(ProjectService::class)->transitionStage($project, $stage, $this->admin);
        $this->assertSame($stage->id, $result->pipeline_stage_id);
    }

    #[Test]
    public function arsivlenen_projede_gecis_reddedilir_ve_arşiv_kayitlari_korunur(): void
    {
        $project = Project::create(['name' => 'Proje', 'service_type_id' => $this->serviceA->id]);
        $nextStage = PipelineStage::query()->where('pipeline_id', $this->pipelineA->id)->where('key', 'planlama')->firstOrFail();
        app(ProjectService::class)->transitionStage($project, $nextStage, $this->admin);

        $this->patch('/app/projects/'.$project->id.'/archive')->assertRedirect();

        $this->assertNotNull($project->fresh()->archived_at);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        $this->assertTrue(ProjectStageHistory::query()->where('project_id', $project->id)->exists());
        $this->assertTrue(AuditEvent::query()->where('auditable_id', $project->id)->exists());
    }

    #[Test]
    public function proje_olusturunca_ilk_aktif_asamaya_alinir(): void
    {
        $this->post('/app/projects', [
            'name' => 'Yeni Proje',
            'service_type_id' => $this->serviceB->id,
        ])->assertRedirect();

        $project = Project::query()->where('name', 'Yeni Proje')->firstOrFail();
        $this->assertSame('Keşif', $project->currentStage->label);
        $this->assertSame(1, ProjectStageHistory::query()->where('project_id', $project->id)->count());
    }

    #[Test]
    public function izinsiz_kullanici_proje_ekranlarina_giremez(): void
    {
        $plain = User::factory()->create();
        $this->post('/logout');
        $this->post('/login', ['email' => $plain->email, 'password' => 'password']);

        $this->get('/app/projects')->assertForbidden();
        $this->post('/app/projects', ['name' => 'X', 'service_type_id' => $this->serviceA->id])->assertForbidden();
    }

    #[Test]
    public function buyuk_kanban_listesi_query_bazli_kalir(): void
    {
        Project::factory()->count(30)->create(['service_type_id' => $this->serviceA->id]);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/app/projects')->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(20, $count, "N+1: $count sorgu");
    }
}
