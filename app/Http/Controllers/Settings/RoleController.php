<?php

namespace App\Http\Controllers\Settings;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Role::class);

        return Inertia::render('settings/roles/index', [
            'roles' => Role::query()
                ->with('permissions:id,key,module,action,label')
                ->orderBy('label')
                ->get(),
            'permissions' => Permission::query()->orderBy('key')->get(['id', 'key', 'module', 'action', 'label']),
            'userCounts' => Role::query()->withCount('users')->get()->pluck('users_count', 'id'),
        ]);
    }

    /**
     * Rol izin matrisi güncelleme. Tüm değişim audit'e yazılır.
     */
    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'permission_ids' => ['present', 'array'],
            'permission_ids.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $original = $role->permissions()->pluck('id')->all();
        $role->permissions()->sync($validated['permission_ids']);

        app(AuditLogger::class)->log('updated', $role, [
            'permission_ids' => $original,
        ], [
            'permission_ids' => $validated['permission_ids'],
        ]);

        return redirect()->route('settings.roles.index')
            ->with('success', 'Rol izinleri güncellendi.');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:roles,key'],
            'label' => ['required', 'string', 'max:120'],
        ]);

        $role = Role::create($validated + ['is_system' => false]);
        app(AuditLogger::class)->logModelChange('created', $role);

        return redirect()->route('settings.roles.index')
            ->with('success', 'Rol oluşturuldu.');
    }

    /**
     * Sistem rolleri arşivlenemez (RolePolicy); kullanıcıya bağlı rol arşivlenirse
     * atama pivot kayıtları korunur ve kullanıcı izinleri doğal olarak daralır.
     */
    public function archive(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('archive', $role);

        $original = $role->getAttributes();
        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();

        app(AuditLogger::class)->logModelChange('archived', $role, $original);

        return redirect()->route('settings.roles.index')
            ->with('success', 'Rol arşivlendi.');
    }
}
