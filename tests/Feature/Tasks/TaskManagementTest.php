<?php

namespace Tests\Feature\Tasks;

use App\Domain\Customers\Models\Customer;
use App\Domain\Projects\Models\Pipeline;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ServiceType;
use App\Domain\Tasks\Models\Task;
use App\Domain\Tasks\Models\TaskAssignment;
use App\Domain\Tasks\Models\TaskHistory;
use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $assignee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());
        $this->assignee = User::factory()->create();
        $this->assignee->roles()->attach(Role::query()->where('key', 'satis-sorumlusu')->first());
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password']);
    }

    private function createTask(array $attributes = []): Task
    {
        $task = Task::create($attributes + ['title' => 'Örnek görev', 'created_by' => $this->admin->id]);

        if ($attributes['assignee'] ?? null) {
            TaskAssignment::create([
                'task_id' => $task->id,
                'user_id' => $attributes['assignee']->id,
                'assigned_by' => $this->admin->id,
                'assigned_at' => now(),
            ]);
        }

        return $task;
    }

    #[Test]
    public function izinsiz_kullanici_gorev_ekranlarina_giremez(): void
    {
        $plain = User::factory()->create();
        $this->post('/logout');
        $this->post('/login', ['email' => $plain->email, 'password' => 'password']);

        $this->get('/app/tasks')->assertForbidden();
        $this->post('/app/tasks', ['title' => 'X'])->assertForbidden();
    }

    #[Test]
    public function benim_gorevlerim_sadece_atananlari_gosterir(): void
    {
        $mine = $this->createTask(['assignee' => $this->assignee]);
        $other = $this->createTask(['title' => 'Başkasının görevi']);

        $this->post('/logout');
        $this->post('/login', ['email' => $this->assignee->email, 'password' => 'password']);

        $this->post('/logout');
        $resp = $this->post('/login', ['email' => $this->assignee->email, 'password' => 'password']);
        fwrite(STDERR, 'login status: '.$resp->status().' authuser: '.(Auth::user()?->email ?? 'guest').PHP_EOL);
        $response = $this->get('/app/tasks?view=my');
        fwrite(STDERR, 'auth after get: '.(Auth::user()?->email ?? 'guest').' perm-now: '.var_export(optional(Auth::user())->hasPermission('tasks.view'), true).PHP_EOL);
        $this->get('/app/tasks?view=my')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('tasks/index')
                ->where('view', 'my')
                ->where('counts.my', 1));

        $titles = Task::query()->whereHas('assignments', fn ($q) => $q->where('user_id', $this->assignee->id))->pluck('title')->all();
        $this->assertContains($mine->title, $titles);
        $this->assertNotContains($other->title, $titles);
    }

    #[Test]
    public function durum_degisikligi_history_ve_audit_yazar(): void
    {
        $task = $this->createTask();

        $this->patch('/app/tasks/'.$task->id.'/status', ['status' => 'done'])->assertRedirect();

        $task->refresh();
        $this->assertSame('done', $task->status);
        $this->assertNotNull($task->completed_at);

        $history = TaskHistory::query()->where('task_id', $task->id)->where('field', 'status')->firstOrFail();
        $this->assertSame('open', $history->old_value);
        $this->assertSame('done', $history->new_value);
        $this->assertSame($this->admin->id, $history->actor_id);

        $this->assertTrue(AuditEvent::query()->where('action', 'status_changed')->where('auditable_id', $task->id)->exists());
    }

    #[Test]
    public function atama_degisikligi_history_yazar(): void
    {
        $task = $this->createTask(['assignee' => $this->assignee]);
        $newAssignee = User::factory()->create();

        $this->patch('/app/tasks/'.$task->id.'/assign', ['user_id' => $newAssignee->id])->assertRedirect();

        $this->assertTrue(
            TaskAssignment::query()->where('task_id', $task->id)->where('user_id', $newAssignee->id)->exists()
        );
        $this->assertFalse(
            TaskAssignment::query()->where('task_id', $task->id)->where('user_id', $this->assignee->id)->exists()
        );

        $history = TaskHistory::query()->where('task_id', $task->id)->where('field', 'assignment')->latest('id')->firstOrFail();
        $this->assertSame((string) $this->assignee->id, $history->old_value);
        $this->assertSame((string) $newAssignee->id, $history->new_value);
    }

    #[Test]
    public function tarih_degisikligi_history_yazar_ve_timezone_uyumlu(): void
    {
        $task = $this->createTask(['due_date' => '2026-12-01']);

        $this->assertSame('Europe/Istanbul', config('app.timezone'));

        $this->put('/app/tasks/'.$task->id, [
            'title' => $task->title,
            'due_date' => '2026-12-15',
        ])->assertRedirect();

        $this->assertSame('2026-12-15', $task->refresh()->due_date->toDateString());

        $history = TaskHistory::query()->where('task_id', $task->id)->where('field', 'due_date')->firstOrFail();
        $this->assertSame('2026-12-01', $history->old_value);
        $this->assertSame('2026-12-15', $history->new_value);
    }

    #[Test]
    public function geciken_gorev_hesabi_tamamlanan_ve_iptali_hariç_tutar(): void
    {
        $overdue = $this->createTask(['due_date' => now()->subDays(3)->toDateString(), 'status' => 'open']);
        $done = $this->createTask(['due_date' => now()->subDays(3)->toDateString(), 'status' => 'done', 'completed_at' => now()]);
        $cancelled = $this->createTask(['due_date' => now()->subDays(3)->toDateString(), 'status' => 'cancelled']);
        $future = $this->createTask(['due_date' => now()->addDays(3)->toDateString(), 'status' => 'open']);

        $this->assertTrue($overdue->isOverdue());
        $this->assertFalse($done->isOverdue());
        $this->assertFalse($cancelled->isOverdue());
        $this->assertFalse($future->isOverdue());

        // Ekip ekranındaki gecikmiş filtresi de aynı kuralı uygular
        $overdueList = Task::query()
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->pluck('id')
            ->all();

        $this->assertContains($overdue->id, $overdueList);
        $this->assertNotContains($done->id, $overdueList);
        $this->assertNotContains($cancelled->id, $overdueList);
    }

    #[Test]
    public function gorev_baglamlari_musteri_proje_hizmet_departman(): void
    {
        $service = ServiceType::create(['key' => 'sv1', 'label' => 'Hizmet 1', 'pipeline_id' => Pipeline::create(['key' => 'p1', 'label' => 'P1'])->id]);
        $project = Project::create(['name' => 'Proje X', 'service_type_id' => $service->id]);
        $customer = Customer::create(['name' => 'Müşteri Y']);

        $task = $this->createTask([
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'service_type_id' => $service->id,
        ]);

        $this->assertSame($project->id, $task->project->id);
        $this->assertSame($customer->id, $task->customer->id);
        $this->assertSame($service->id, $task->serviceType->id);
    }

    #[Test]
    public function gecikmis_sayaci_gercek_query_uretir(): void
    {
        $this->createTask(['due_date' => now()->subDay()->toDateString()]);
        $this->createTask(['due_date' => now()->subDay()->toDateString(), 'status' => 'done', 'completed_at' => now()]);

        $this->get('/app/tasks?view=team')->assertOk();

        $expected = Task::query()
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->count();

        $this->post('/logout');
        $resp = $this->post('/login', ['email' => $this->assignee->email, 'password' => 'password']);
        fwrite(STDERR, 'login status: '.$resp->status().' authuser: '.(Auth::user()?->email ?? 'guest').PHP_EOL);
        $response = $this->get('/app/tasks?view=my');
        fwrite(STDERR, 'auth after get: '.(Auth::user()?->email ?? 'guest').' perm-now: '.var_export(optional(Auth::user())->hasPermission('tasks.view'), true).PHP_EOL);
        $this->get('/app/tasks?view=my')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('counts.overdue', $expected));
    }

    #[Test]
    public function buyuk_gorev_listesi_query_bazli_kalir(): void
    {
        $users = User::factory()->count(5)->create();
        for ($i = 0; $i < 30; $i++) {
            $task = $this->createTask(['title' => 'Görev '.$i]);
            TaskAssignment::create([
                'task_id' => $task->id,
                'user_id' => $users->random()->id,
                'assigned_by' => $this->admin->id,
                'assigned_at' => now(),
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/app/tasks?view=team')->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(20, $count, "N+1: $count sorgu");
    }
}
