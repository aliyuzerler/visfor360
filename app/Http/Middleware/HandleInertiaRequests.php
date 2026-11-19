<?php

namespace App\Http\Middleware;

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
                'user' => fn () => $request->user()
                    ? $request->user()->only(['id', 'name', 'email'])
                    : null,
            ],
        ];
    }
}
