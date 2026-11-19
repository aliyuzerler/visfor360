<?php

namespace App\Http\Controllers\Settings;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreUserRequest;
use App\Http\Requests\Settings\UpdateUserRequest;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->with('roles:id,key,label', 'departmentMemberships.department:id,name')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('settings/users/index', [
            'users' => $users,
            'filters' => ['search' => $request->string('search')->toString()],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('settings/users/create', [
            'roles' => Role::query()->orderBy('label')->get(['id', 'key', 'label']),
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'is_active' => true,
        ]);

        $user->roles()->sync($request->input('role_ids', []));

        $memberships = collect($request->input('department_ids', []))
            ->mapWithKeys(fn (int $id) => [$id => ['is_manager' => false, 'joined_at' => now()]]);
        $user->departments()->sync($memberships);

        // Parola değeri audit'te maskelenir.
        app(AuditLogger::class)->logModelChange('created', $user);

        return redirect()->route('settings.users.index')
            ->with('success', 'Kullanıcı oluşturuldu.');
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('settings/users/edit', [
            'userData' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'role_ids' => $user->roles()->pluck('id')->all(),
                'department_ids' => $user->departmentMemberships()->pluck('department_id')->all(),
            ],
            'roles' => Role::query()->orderBy('label')->get(['id', 'key', 'label']),
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $original = $user->getAttributes();

        $data = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'is_active' => $request->boolean('is_active'),
        ];

        // users.update izni yoksa (kendi profili düzenleme) aktiflik değiştirilemez.
        if (! $request->user()->can('users.update')) {
            $data['is_active'] = $user->is_active;
        }

        if ($request->filled('password')) {
            $data['password'] = $request->string('password')->toString();
            $data['password_changed_at'] = now();
        }

        $user->update($data);

        if ($user->id !== $request->user()->id || $request->user()->can('users.update')) {
            $user->roles()->sync($request->input('role_ids', []));

            $memberships = collect($request->input('department_ids', []))
                ->mapWithKeys(fn (int $id) => [$id => ['is_manager' => false, 'joined_at' => now()]]);
            $user->departments()->sync($memberships);
        }

        app(AuditLogger::class)->logModelChange('updated', $user, $original);

        return redirect()->route('settings.users.index')
            ->with('success', 'Kullanıcı güncellendi. Parola değiştirildiyse eski oturumlar kapatıldı.');
    }

    /**
     * Silme yerine arşiv: is_active=false + soft delete yok; actor geçmişi korunur.
     */
    public function archive(Request $request, User $user): RedirectResponse
    {
        $this->authorize('archive', $user);

        $original = $user->getAttributes();
        $user->update(['is_active' => false]);
        app(AuditLogger::class)->logModelChange('archived', $user, $original);

        return redirect()->route('settings.users.index')
            ->with('success', 'Kullanıcı devre dışı bırakıldı.');
    }
}
