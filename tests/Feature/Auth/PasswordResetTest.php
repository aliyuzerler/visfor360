<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    #[Test]
    public function sifirlama_bagliktasi_gonderilir(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    #[Test]
    public function olmayan_epostada_hesap_bilgisi_sizdirilmez(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'yok@visfor.example'])
            ->assertSessionHasNoErrors(); // aynı nötr mesaj

        Notification::assertNothingSent();
    }

    #[Test]
    public function guclu_parolayla_sifirlama_calisir(): void
    {
        $user = User::factory()->create();
        $oldHash = $user->password;
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'YeniParola12345',
            'password_confirmation' => 'YeniParola12345',
        ]);

        $response->assertRedirect('/login');
        $this->assertNotSame($oldHash, $user->fresh()->password);
        $this->assertNotNull($user->fresh()->password_changed_at);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('YeniParola12345', $user->fresh()->password));
    }

    #[Test]
    public function zayif_parola_reddedilir(): void
    {
        $user = User::factory()->create();
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'kısa1',
            'password_confirmation' => 'kısa1',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    #[Test]
    public function parola_degisimi_eski_oturumu_kapatir(): void
    {
        $user = User::factory()->create();

        // Gerçek login akışı ile oturum aç (session'da password hash damgası oluşur)
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($user);

        // Parola başka bir yerden değiştirilir
        $user->forceFill(['password' => 'BaskaParola12345', 'password_changed_at' => now()])->saveQuietly();

        $this->get('/app/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }
}
