<?php

namespace Tests\Feature\Dashboard;

use App\Domain\Customers\Models\Customer;
use App\Domain\Documents\Models\DocumentType;
use App\Domain\Goals\Actions\GoalProgressService;
use App\Domain\Goals\Models\GoalTaskLink;
use App\Domain\Goals\Models\StrategicGoal;
use App\Domain\Projects\Models\Pipeline;
use App\Domain\Projects\Models\PipelineStage;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ServiceType;
use App\Domain\Tasks\Models\Task;
use App\Domain\Tasks\Models\TaskAssignment;
use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\GoalPlaceholderSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Faz 1 uçtan uca (Prompt 10): CRM → evrak → proje → görev → hedef akışı,
 * role-aware dashboard KPI'ları ve finans izin sızıntısı yokluğu.
 */
class DashboardE2eTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(GoalPlaceholderSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password']);
    }

    #[Test]
    public function uctan_uca_akis_crm_evrak_proje_gorev_hedef(): void
    {
        Storage::fake('local');

        // 1) Müşteri oluştur (CRM)
        $this->post('/app/customers', ['name' => 'E2E Firma A.Ş.'])->assertRedirect();
        $customer = Customer::query()->where('name', 'E2E Firma A.Ş.')->firstOrFail();

        // 2) Evrak yükle (Prompt 05)
        DocumentType::create(['key' => 'sozlesme-e2e', 'label' => 'Sözleşme', 'required' => false, 'validity_warn_days' => 30]);
        $this->post('/app/customers/'.$customer->id.'/documents', [
            'document_type_id' => DocumentType::query()->where('key', 'sozlesme-e2e')->first()->id,
            'file' => UploadedFile::fake()->create('sozlesme.pdf', 100, 'application/pdf'),
            'valid_until' => now()->addDays(20)->toDateString(),
        ])->assertRedirect();

        // 3) Hizmet + pipeline + proje (Prompt 06)
        $pipeline = Pipeline::create(['key' => 'e2e-p', 'label' => 'E2E Şablon', 'is_example' => true]);
        $stage = PipelineStage::create(['pipeline_id' => $pipeline->id, 'key' => 'baslangic', 'label' => 'Başlangıç', 'sort_order' => 1, 'is_active' => true]);
        $service = ServiceType::create(['key' => 'e2e-sv', 'label' => 'E2E Hizmet', 'pipeline_id' => $pipeline->id]);

        $this->post('/app/projects', [
            'name' => 'E2E Projesi',
            'customer_id' => $customer->id,
            'service_type_id' => $service->id,
        ])->assertRedirect();
        $project = Project::query()->where('name', 'E2E Projesi')->firstOrFail();
        $this->assertSame($stage->id, $project->pipeline_stage_id);

        // 3b) Gerçek aşama geçişi (Prompt 06): Başlangıç → Yürütme
        $nextStage = PipelineStage::create(['pipeline_id' => $pipeline->id, 'key' => 'yurutme', 'label' => 'Yürütme', 'sort_order' => 2, 'is_active' => true]);
        $this->patch('/app/projects/'.$project->id.'/stage', ['stage_id' => $nextStage->id])->assertRedirect();

        // 4) Görev oluştur + ata (Prompt 07)
        $this->post('/app/tasks', [
            'title' => 'E2E Görevi',
            'customer_id' => $customer->id,
            'project_id' => $project->id,
        ])->assertRedirect();
        $task = Task::query()->where('title', 'E2E Görevi')->firstOrFail();

        TaskAssignment::create([
            'task_id' => $task->id,
            'user_id' => $this->admin->id,
            'assigned_by' => $this->admin->id,
            'assigned_at' => now(),
        ]);

        // 5) Hedef bağla (Prompt 08)
        $goal = StrategicGoal::query()->where('slot_key', 'goal-01')->firstOrFail();
        $goal->update([
            'label' => 'E2E Hedef (müşteri verisi)',
            'calculation_method' => 'linked_tasks_ratio',
            'calculation_config' => ['approved' => true],
        ]);
        GoalTaskLink::create(['goal_id' => $goal->id, 'task_id' => $task->id, 'linked_by' => $this->admin->id]);

        // 6) Görevi tamamla → hedef ilerlemesi hesaplanabilir
        $this->patch('/app/tasks/'.$task->id.'/status', ['status' => 'done'])->assertRedirect();

        $snapshot = app(GoalProgressService::class)->snapshotFor($goal, $this->admin);
        $this->assertSame('calculated', $snapshot->status);
        $this->assertSame(100.0, (float) $snapshot->progress_value); // 1/1 görev tamam

        // Akıştaki her adım auditli
        $this->assertTrue(AuditEvent::query()->where('action', 'created')->where('auditable_id', $customer->id)->exists());
        $this->assertTrue(AuditEvent::query()->where('action', 'stage_changed')->where('auditable_id', $project->id)->exists());
    }

    #[Test]
    public function dashboard_kpi_gercek_sorgu_sonuclari_gosterir(): void
    {
        // Veri yok: tüm KPI'lar 0 (sahte değer yok)
        $this->get('/app/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('kpi.tasks.my_open', 0)
                ->where('kpi.projects.active', 0)
                ->where('kpi.customers.active', 0)
                ->where('kpi.documents.expiring_soon', 0)
                ->where('kpi.goals.needs_configuration', 12));

        // Gerçek veri: müşteri + geciken görev
        $this->post('/app/customers', ['name' => 'KPI Firma'])->assertRedirect();
        $task = Task::create(['title' => 'Gecikmiş', 'due_date' => now()->subDay()->toDateString(), 'status' => 'open']);
        TaskAssignment::create(['task_id' => $task->id, 'user_id' => $this->admin->id, 'assigned_by' => $this->admin->id, 'assigned_at' => now()]);

        $this->get('/app/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('kpi.customers.active', 1)
                ->where('kpi.tasks.my_overdue', 1)
                ->where('kpi.tasks.my_open', 1));
    }

    #[Test]
    public function finans_izni_olmayan_kullaniciya_finans_verisi_hic_gonderilmez(): void
    {
        $limited = User::factory()->create();
        $limited->roles()->attach(Role::query()->where('key', 'danisman-personel')->first());

        $this->post('/logout');
        $this->post('/login', ['email' => $limited->email, 'password' => 'password']);

        $response = $this->get('/app/dashboard')->assertOk();
        $content = $response->getContent();

        // Inertia JSON payload'ında finans anahtarı ve kayıt verisi bulunmaz
        $this->assertStringNotContainsString('pending_approvals', $content);
        $this->assertStringNotContainsString('FinanceEntry', $content);
    }

    #[Test]
    public function farkli_rollerin_dashboard_kpi_kumesi_farkli(): void
    {
        // Admin: tüm modüller
        $adminResponse = $this->get('/app/dashboard');
        $adminResponse->assertOk();
        $adminContent = $adminResponse->getContent();

        // Danışman: dar izin kümesi
        $limited = User::factory()->create();
        $limited->roles()->attach(Role::query()->where('key', 'danisman-personel')->first());
        $this->post('/logout');
        $this->post('/login', ['email' => $limited->email, 'password' => 'password']);

        $limitedResponse = $this->get('/app/dashboard');
        $limitedContent = $limitedResponse->getContent();

        $this->assertStringContainsString('kpi', $adminContent);
        $this->assertNotSame($adminContent, $limitedContent);
    }

    #[Test]
    public function dashboard_aggregate_sorgulari_n1_olmadan_calisir(): void
    {
        User::factory()->count(10)->create();
        $this->seed(GoalPlaceholderSeeder::class);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/app/dashboard')->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(30, $count, "Dashboard query bollugu: $count");
    }
}
