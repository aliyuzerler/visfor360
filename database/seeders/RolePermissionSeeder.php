<?php

namespace Database\Seeders;

use App\Domain\Identity\PermissionRegistry;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Rol/izin seed (Prompt 03).
 * DİKKAT — ÖNERİ: Müşteri rol matrisi (docs/ROLES_AND_PERMISSIONS.md, D-04) henüz
 * onaylanmadı. Bu seed yalnızca geliştirme/kabul içindir; izin sınırları
 * yönetim ekranından değiştirilebilir. updateOrCreate kullanır: tekrar
 * çalıştırıldığında duplicate üretmez (Test 03 madde 9).
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionRegistry::keys() as $key) {
            [$module, $action] = explode('.', $key, 2);

            Permission::query()->updateOrCreate(
                ['key' => $key],
                [
                    'module' => $module,
                    'action' => $action,
                    'label' => PermissionRegistry::label($key),
                ],
            );
        }

        $allPermissionIds = Permission::query()->pluck('id')->all();

        $roles = [
            'sistem-yoneticisi' => ['label' => 'Sistem Yöneticisi', 'is_system' => true, 'permissions' => 'all'],
            'isletme-yoneticisi' => ['label' => 'İşletme Yöneticisi', 'is_system' => true, 'permissions' => 'all_without:portal.manage_settings'],
            'satis-sorumlusu' => ['label' => 'Satış / Müşteri Sorumlusu', 'is_system' => true, 'permissions' => 'list:customers.view,customers.create,customers.update,customers.export,documents.view,documents.create,documents.download,projects.view,projects.create,projects.update,checkups.view,checkups.create,checkups.update,opportunities.view,opportunities.update,tasks.view,tasks.create,tasks.update,goals.view,departments.view,users.view'],
            'departman-yoneticisi' => ['label' => 'Departman Yöneticisi', 'is_system' => true, 'permissions' => 'list:projects.view,projects.update,projects.approve,tasks.view,tasks.create,tasks.update,tasks.archive,goals.view,departments.view,users.view,customers.view,documents.view,documents.download'],
            'danisman-personel' => ['label' => 'Danışman / Personel', 'is_system' => true, 'permissions' => 'list:tasks.view,tasks.update,projects.view,customers.view,documents.view,documents.download,goals.view'],
            'finans-kullanicisi' => ['label' => 'Finans Kullanıcısı', 'is_system' => true, 'permissions' => 'list:finance.view,finance.create,finance.update,finance.archive,finance.approve,finance.export,customers.view,projects.view,audit.view'],
        ];

        foreach ($roles as $key => $definition) {
            $role = Role::query()->updateOrCreate(
                ['key' => $key],
                ['label' => $definition['label'], 'is_system' => $definition['is_system']],
            );

            $role->permissions()->sync($this->resolvePermissions($definition['permissions']));
        }
    }

    /**
     * @return list<int>
     */
    private function resolvePermissions(string $spec): array
    {
        if ($spec === 'all') {
            return Permission::query()->pluck('id')->all();
        }

        if (str_starts_with($spec, 'all_without:')) {
            $excluded = explode(',', substr($spec, strlen('all_without:')));

            return Permission::query()->whereNotIn('key', $excluded)->pluck('id')->all();
        }

        if (str_starts_with($spec, 'list:')) {
            $keys = explode(',', substr($spec, strlen('list:')));

            return Permission::query()->whereIn('key', $keys)->pluck('id')->all();
        }

        return [];
    }
}
