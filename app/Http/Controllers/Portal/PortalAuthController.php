<?php

namespace App\Http\Controllers\Portal;

use App\Domain\Portal\Models\PortalUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PortalAuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('portal/login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('portal')->attempt($credentials + ['is_active' => true])) {
            return back()->withErrors(['email' => 'Bilgiler hatalı ya da hesap pasif.']);
        }

        $request->session()->regenerate();

        PortalUser::query()->where('id', Auth::guard('portal')->id())->update(['last_login_at' => now()]);

        return redirect()->route('portal.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('portal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
