<?php

namespace Tests\Feature\Goals;

use App\Domain\Goals\Actions\GoalProgressService;
use App\Domain\Goals\Models\GoalDefaultMapping;
use App\Domain\Goals\Models\GoalTaskLink;
use App\Domain\Goals\Models\StrategicGoal;
use App\Domain\Goals\Models\VisionStatement;
use App\Domain\Projects\Models\Pipeline;
use App\Domain\Projects\Models\ServiceType;
use App\Domain\Tasks\Models\Task;
use App\Models\AuditEvent;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\GoalPlaceholderSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoalOkrTest extends TestCase
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
    public function placeholder_set_tam_12_slot_ornetik_etiketli_uretimden_ayri(): void
    {
        $goals = StrategicGoal::query()->orderBy('sort_order')->get();

        $this->assertSame(12, $goals->count());
        $this->assertSame('goal-01', $goals->first()->slot_key);
        $this->assertSame('goal-12', $goals->last()->slot_key);

        // Tüm placeholder'lar is_example=true ve label null (müşteri kararı bekliyor)
        $this->assertTrue($goals->every(fn ($goal) => $goal->is_example === true));
        $this->assertTrue($goals->every(fn ($goal) => $goal->isPlaceholder() === true));

        // Vizyon placeholder'ı onaysız
        $this->assertFalse(VisionStatement::query()->first()->is_approved);
    }

    #[Test]
    public function mapping_departman_ve_hizmete_gore_varsayilan_hedef_bulur(): void
    {
        $goal = StrategicGoal::query()->where('slot_key', 'goal-03')->firstOrFail();
        $department = Department::create(['name' => 'Müşteri Operasyonları', 'code' => 'ops']);
        $service = ServiceType::create([
            'key' => 'sv-x', 'label' => 'Hizmet X',
            'pipeline_id' => Pipeline::create(['key' => 'p-x', 'label' => 'P X'])->id,
        ]);

        GoalDefaultMapping::create(['goal_id' => $goal->id, 'service_type_id' => $service->id, 'department_id' => $department->id]);

        $resolved = GoalDefaultMapping::resolveFor($service->id, $department->id);
        $this->assertNotNull($resolved);
        $this->assertSame($goal->id, $resolved->goal_id);

        // Eşleşmeyen kombinasyon → null
        $this->assertNull(GoalDefaultMapping::resolveFor(null, null));
    }

    #[Test]
    public function gorev_olusturunca_mapping_hedefine_baglanir(): void
    {
        $goal = StrategicGoal::query()->where('slot_key', 'goal-05')->firstOrFail();
        $service = ServiceType::create([
            'key' => 'sv-y', 'label' => 'Hizmet Y',
            'pipeline_id' => Pipeline::create(['key' => 'p-y', 'label' => 'P Y'])->id,
        ]);
        GoalDefaultMapping::create(['goal_id' => $goal->id, 'service_type_id' => $service->id]);

        $this->post('/app/tasks', [
            'title' => 'Hedefli görev',
            'service_type_id' => $service->id,
        ])->assertRedirect();

        $task = Task::query()->where('title', 'Hedefli görev')->firstOrFail();
        $this->assertTrue(GoalTaskLink::query()->where('task_id', $task->id)->where('goal_id', $goal->id)->exists());
    }

    #[Test]
    public function izinsiz_kullanici_gorev_baglayamaz_ve_hedef_detayini_goremez(): void
    {
        $goal = StrategicGoal::query()->where('slot_key', 'goal-01')->firstOrFail();
        $task = Task::create(['title' => 'Gizli görev']);

        // Hicbir role bagli olmayan kullanici: hicbir modul izni yok
        $limited = User::factory()->create();
        $this->post('/logout');
        $this->post('/login', ['email' => $limited->email, 'password' => 'password']);

        $this->post('/app/goals/'.$goal->id.'/tasks', ['task_id' => $task->id])->assertForbidden();
        $this->get('/app/goals/'.$goal->id)->assertForbidden();
        $this->get('/app/goals')->assertForbidden();

        $this->assertFalse(GoalTaskLink::query()->where('task_id', $task->id)->exists());
    }

    #[Test]
    public function formul_olmayan_hedef_needs_configuration_olarak_gosterilir(): void
    {
        $goal = StrategicGoal::query()->where('slot_key', 'goal-02')->firstOrFail();

        $snapshot = app(GoalProgressService::class)->snapshotFor($goal, $this->admin);

        $this->assertSame('needs_configuration', $snapshot->status);
        $this->assertNull($snapshot->progress_value); // uydurma yüzde koruması
        $this->assertNull($snapshot->formula_version);
    }

    #[Test]
    public function onayli_formulle_hesap_yapilir_ve_snapshot_izlenebilir(): void
    {
        $goal = StrategicGoal::query()->where('slot_key', 'goal-04')->firstOrFail();
        $goal->update([
            'calculation_method' => 'linked_tasks_ratio',
            'calculation_config' => ['approved' => true, 'note' => 'TEST FIXTURE'],
        ]);

        $tasks = collect([
            Task::create(['title' => 'G1', 'status' => 'done', 'completed_at' => now()]),
            Task::create(['title' => 'G2', 'status' => 'in_progress']),
            Task::create(['title' => 'G3', 'status' => 'open']),
            Task::create(['title' => 'G4', 'status' => 'done', 'completed_at' => now()]),
        ]);
        $tasks->each(fn ($task) => GoalTaskLink::create(['goal_id' => $goal->id, 'task_id' => $task->id, 'linked_by' => $this->admin->id]));

        $snapshot = app(GoalProgressService::class)->snapshotFor($goal, $this->admin);

        $this->assertSame('calculated', $snapshot->status);
        $this->assertSame(50.0, (float) $snapshot->progress_value); // 2/4
        $this->assertSame(4, $snapshot->source_task_count);
        $this->assertSame(2, $snapshot->source_done_count);
        $this->assertSame('linked_tasks_ratio.v1', $snapshot->formula_version);
        $this->assertNotNull($snapshot->calculated_at);

        // Audit izlenebilirliği
        $this->assertTrue(AuditEvent::query()->where('action', 'progress_snapshot')->where('auditable_id', $goal->id)->exists());
    }

    #[Test]
    public function hedef_icerigi_guncelleme_auditli_ve_onayli_formul_onayli_kalir(): void
    {
        $goal = StrategicGoal::query()->where('slot_key', 'goal-06')->firstOrFail();

        $this->patch('/app/goals/'.$goal->id, [
            'label' => 'Müşteri tarafından verilen hedef adı',
            'description' => 'Müşteri açıklaması',
        ])->assertRedirect();

        $this->assertSame('Müşteri tarafından verilen hedef adı', $goal->fresh()->label);
        $this->assertTrue(AuditEvent::query()->where('action', 'updated')->where('auditable_id', $goal->id)->exists());
    }

    #[Test]
    public function bos_bagli_liste_hesaplanmaz_progress_null_kalir(): void
    {
        // Onaylı formül + 0 görev → 0/0 bölme yok, progress null
        $goal = StrategicGoal::query()->where('slot_key', 'goal-07')->firstOrFail();
        $goal->update([
            'calculation_method' => 'linked_tasks_ratio',
            'calculation_config' => ['approved' => true],
        ]);

        $snapshot = app(GoalProgressService::class)->snapshotFor($goal, $this->admin);

        // Sinir degeri: 0 bagli gorev → 0/0 bolme yok; hesap yapilmaz ve
        // needs_configuration ile korunur (uydurma yuzden korumasi).
        $this->assertSame('needs_configuration', $snapshot->status);
        $this->assertNull($snapshot->progress_value);
        $this->assertSame(0, $snapshot->source_task_count);
    }
}
