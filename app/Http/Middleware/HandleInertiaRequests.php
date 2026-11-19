<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Kök şablon değişkeni.
     */
    protected $rootView = 'app';

    /**
     * Paylaşılan Inertia verileri.
     * auth.user.permissions yalnızca UI görünürlüğü içindir; gerçek yetki
     * kontrolü her istekte server-side Gate/Policy ile yapılır.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
                'env' => app()->environment(),
            ],
            'auth' => [
                'user' => fn () => $request->user() ? $this->userData($request->user()) : null,
            ],
        ];
    }

    /**
     * @return array{id: int, name: string, email: string, roles: list<string>, permissions: list<string>}
     */
    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles()->pluck('key')->all(),
            'permissions' => $user->permissionKeys(),
        ];
    }
}
