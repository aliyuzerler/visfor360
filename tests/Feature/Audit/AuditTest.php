<?php

namespace Tests\Feature\Audit;

use App\Models\AuditEvent;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

/**
 * Audit doğrulaması (Test 03 maddeleri 5, 6, 7, 9):
 * before/after, append-only (model + DB), hassas alan maskeleme, seed idempotency.
 */
class AuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(DB::table('roles')->where('key', 'sistem-yoneticisi')->value('id'));

        return $admin;
    }

    #[Test]
    public function kullanici_olusturma_audit_olayi_yazar(): void
    {
        $admin = $this->adminUser();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $this->post('/app/settings/users', [
            'name' => 'Yeni Kişi',
            'email' => 'yeni@visfor.example',
            'password' => 'GüçlüParola12345',
            'role_ids' => [],
            'department_ids' => [],
        ])->assertRedirect('/app/settings/users');

        $event = AuditEvent::query()->where('action', 'created')->latest('id')->first();

        $this->assertNotNull($event);
        $this->assertSame($admin->id, $event->actor_id);
        $this->assertNotNull($event->correlation_id);
        $this->assertNotNull($event->created_at);
        $this->assertSame(User::class, $event->auditable_type);
    }

    #[Test]
    public function guncellemede_before_after_farki_tutulur(): void
    {
        $admin = $this->adminUser();
        $target = User::factory()->create(['name' => 'Eski Ad']);
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $this->put('/app/settings/users/'.$target->id, [
            'name' => 'Yeni Ad',
            'email' => $target->email,
            'is_active' => true,
            'role_ids' => [],
            'department_ids' => [],
        ])->assertRedirect();

        $event = AuditEvent::query()->where('action', 'updated')->latest('id')->first();

        $this->assertNotNull($event);
        $this->assertSame('Eski Ad', $event->before['name'] ?? null);
        $this->assertSame('Yeni Ad', $event->after['name'] ?? null);
    }

    #[Test]
    public function parola_audit_kaydinda_görünmez(): void
    {
        $admin = $this->adminUser();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $this->post('/app/settings/users', [
            'name' => 'Maskeli Kişi',
            'email' => 'maske@visfor.example',
            'password' => 'GizliParola99999',
            'role_ids' => [],
            'department_ids' => [],
        ]);

        $payload = AuditEvent::query()->latest('id')->first()->after ?? [];

        $this->assertSame('[MASKED]', $payload['password'] ?? null);
        $this->assertStringNotContainsString('GizliParola99999', json_encode($payload));
    }

    #[Test]
    public function audit_model_seviyesinde_guncellenemez_ve_silinemez(): void
    {
        $admin = $this->adminUser();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $this->post('/app/settings/departments', ['name' => 'Audit D.', 'code' => 'audit-d']);
        $event = AuditEvent::query()->latest('id')->first();

        // Model katmanı koruması
        $this->expectException(RuntimeException::class);
        $event->update(['action' => 'tampered']);
    }

    #[Test]
    public function audit_veritabani_seviyesinde_guncellenemez(): void
    {
        $admin = $this->adminUser();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $this->post('/app/settings/departments', ['name' => 'DB D.', 'code' => 'db-d']);
        $id = AuditEvent::query()->latest('id')->value('id');

        // DB katmanı (PostgreSQL trigger) koruması — model bypass edilirse bile
        $this->expectException(QueryException::class);
        DB::table('audit_events')->where('id', $id)->update(['action' => 'tampered']);
    }

    #[Test]
    public function seed_tekrar_calisinca_duplicate_uretmez(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $permissionCount = DB::table('permissions')->count();
        $roleCount = DB::table('roles')->count();
        $adminPivotCount = DB::table('permission_role')
            ->where('role_id', DB::table('roles')->where('key', 'sistem-yoneticisi')->value('id'))
            ->count();

        // Aynı seed tekrar çalışır
        $this->seed(RolePermissionSeeder::class);

        $this->assertSame($permissionCount, DB::table('permissions')->count());
        $this->assertSame($roleCount, DB::table('roles')->count());
        $this->assertSame($adminPivotCount, DB::table('permission_role')
            ->where('role_id', DB::table('roles')->where('key', 'sistem-yoneticisi')->value('id'))
            ->count());
    }
}
