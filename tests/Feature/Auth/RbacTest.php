<?php

namespace Tests\Feature\Auth;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * RBAC doğrulaması (Test 03 maddeleri 2, 3, 4):
 * yetkisiz rol işlem yapamaz, izin bazlı erişim çalışır, IDOR/rol koruması.
 */
class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function loginAs(User $user): void
    {
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
    }

    private function userWithPermissions(array $keys, ?string $roleKey = null): User
    {
        $user = User::factory()->create();
        $role = Role::create(['key' => 'test-rol-'.uniqid(), 'label' => 'Test Rolü', 'is_system' => false]);
        $role->permissions()->sync(Permission::query()->whereIn('key', $keys)->pluck('id')->all());
        $user->roles()->attach($role);

        return $user;
    }

    #[Test]
    public function izinsiz_kullanici_ayar_ekranlarina_giremez(): void
    {
        $user = User::factory()->create(); // hiç rol yok

        $this->loginAs($user);
        $this->get('/app/settings/users')->assertForbidden();
        $this->loginAs($user);
        $this->get('/app/settings/roles')->assertForbidden();
        $this->loginAs($user);
        $this->get('/app/settings/audit-logs')->assertForbidden();
    }

    #[Test]
    public function sadece_view_izni_olan_kullanici_olusturamaz(): void
    {
        $user = $this->userWithPermissions(['users.view']);
        $this->loginAs($user);

        $this->get('/app/settings/users')->assertOk();
        $this->post('/app/settings/users', [
            'name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'password' => 'GüçlüParola12345',
        ])->assertForbidden();
    }

    #[Test]
    public function update_izni_olan_kullanici_guncelleyebilir(): void
    {
        $admin = $this->userWithPermissions(['users.view', 'users.update', 'users.create']);
        $target = User::factory()->create();

        $this->actingAs($admin)->put('/app/settings/users/'.$target->id, [
            'name' => 'Güncellenmiş Ad',
            'email' => $target->email,
            'is_active' => true,
            'role_ids' => [],
            'department_ids' => [],
        ])->assertRedirect('/app/settings/users')->assertSessionHasNoErrors();

        $this->assertSame('Güncellenmiş Ad', $target->fresh()->name);
    }

    #[Test]
    public function sistem_yoneticisi_tum_izinlere_sahiptir(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());

        $this->loginAs($admin);
        $this->get('/app/settings/users')->assertOk();
        $this->loginAs($admin);
        $this->get('/app/settings/audit-logs')->assertOk();
    }

    #[Test]
    public function kendi_hesabini_arşivleyemez(): void
    {
        $admin = $this->userWithPermissions(['users.archive', 'users.update']);

        $this->actingAs($admin)
            ->patch('/app/settings/users/'.$admin->id.'/archive')
            ->assertForbidden();

        $this->assertTrue($admin->fresh()->is_active);
    }

    #[Test]
    public function sistem_yoneticisi_degil_sistem_yoneticisini_duzenleyemez(): void
    {
        $plainManager = $this->userWithPermissions(['users.view', 'users.update']);
        $superAdmin = User::factory()->create();
        $superAdmin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());

        $this->loginAs($plainManager);
        $this->put('/app/settings/users/'.$superAdmin->id, [
            'name' => 'Ele Geçirilmiş Ad',
            'email' => $superAdmin->email,
            'is_active' => true,
        ])
            ->assertForbidden();

        $this->assertSame($superAdmin->getOriginal('name') ?? $superAdmin->name, $superAdmin->fresh()->name);
    }

    #[Test]
    public function departments_kapsami_izin_ayridir(): void
    {
        // departments.view izni users ekranına erişim vermez (izinden ayrı kapsam).
        $user = $this->userWithPermissions(['departments.view']);
        $department = Department::create(['name' => 'Test D.', 'code' => 'td']);

        $this->loginAs($user);
        $this->get('/app/settings/departments')->assertOk();
        $this->loginAs($user);
        $this->get('/app/settings/users')->assertForbidden();
    }
}
