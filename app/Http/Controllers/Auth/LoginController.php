<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Domain\Audit\AuditLogger;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends \App\Http\Controllers\Controller
{
    public function show(): Response
    {
        return Inertia::render('auth/login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $credentials = $request->only('email', 'password');
        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            $request->hitRateLimiter();

            return back()->withErrors([
                'email' => __('Verilen bilgilerle eşleşen kayıt bulunamadı.'),
            ])->onlyInput('email');
        }

        $user = Auth::user();

        // Pasif/arşivlenmiş hesap girişi reddedilir.
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => __('Hesabınız devre dışı. Yöneticinizle iletişime geçin.'),
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->clearRateLimiter();

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        app(AuditLogger::class)->log('auth.login');

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(LoginRequest $request): RedirectResponse
    {
        Auth::logout();

        // Session fixation koruması: oturum tamamen yok edilir ve token yenilenir.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
