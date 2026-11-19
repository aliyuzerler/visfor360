<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Parola sıfırlama: token'lar password_reset_tokens tablosunda, varsayılan
 * 60 dakika geçerli ve tek kullanımlıktır (framework davranışı).
 */
class PasswordResetController extends Controller
{
    public function requestForm(): Response
    {
        return Inertia::render('auth/forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        // Yanıt, hesabın varlığı hakkında bilgi sızdırmaz (enumerate koruması).
        Password::sendResetLink($request->only('email'));

        return back()->with('status', __('Şifre sıfırlama bağlantısı e-posta adresinize gönderildiyse, birkaç dakika içinde ulaşacaktır.'));
    }

    public function resetForm(Request $request, string $token): Response
    {
        return Inertia::render('auth/reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'password_changed_at' => now(),
                ])->saveQuietly();

                // AuthenticateSession middleware, eski oturumları geçersiz kılar
                // (parola hash değişimini oturumdaki hash ile karşılaştırır).
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return redirect()->route('login')->with('status', __($status));
    }
}
