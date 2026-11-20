<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Portal erişim middleware (Prompt 15): auth:portal + aktif kullanıcı +
 * aktif üyelik. Pasif kullanıcı/üyelik → anında erişim kesilir (403).
 */
class EnsurePortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('portal')->user();

        if ($user === null) {
            return redirect()->route('portal.login');
        }

        if (! $user->is_active) {
            Auth::guard('portal')->logout();

            return redirect()->route('portal.login')->withErrors(['email' => 'Hesabınız pasifleştirildi.']);
        }

        if (! $user->activeMemberships()->exists()) {
            Auth::guard('portal')->logout();

            return redirect()->route('portal.login')->withErrors(['email' => 'Aktif firma üyeliğiniz bulunmuyor.']);
        }

        return $next($request);
    }
}
