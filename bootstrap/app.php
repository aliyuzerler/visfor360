<?php

use App\Http\Middleware\EnsurePortalAccess;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectIfPortalAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Parola değişince eski oturumları geçersiz kılan framework middleware'i.
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AuthenticateSession::class,
        ]);

        // Müşteri portalı (Prompt 15): ayrı guard middleware'leri
        $middleware->alias([
            'portal.access' => EnsurePortalAccess::class,
            'portal.guest' => RedirectIfPortalAuthenticated::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
