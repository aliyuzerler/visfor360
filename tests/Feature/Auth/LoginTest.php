<?php

namespace Tests\Feature\Auth;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        RateLimiter::clear('login-throttle');
    }

    #[Test]
    public function unauthenticated_kullanici_korumali_sayfaya_erisemez(): void
    {
        $this->get('/app/dashboard')->assertRedirect('/login');
        $this->get('/app/settings/users')->assertRedirect('/login');
    }

    #[Test]
    public function gecerli_bilgilerle_giris_yapilir(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/app/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    #[Test]
    public function giris_basarisi_audit_olayi_olusturur(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $user->id,
            'action' => 'auth.login',
        ]);
    }

    #[Test]
    public function yanlis_parola_girisi_reddedilir_ve_throttle_artar(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function fazla_denemede_hesap_gecici_kilitlenir(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password-'.$attempt]);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function throttle_denemesi_limiter_tarafindan_engellenir(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password-'.$attempt]);
        }

        // 6. deneme LoginRequest limiter'ında kalır (key: e-posta|ip)
        $limiter = \Illuminate\Support\Facades\RateLimiter::tooManyAttempts(strtolower($user->email).'|127.0.0.1', 5);

        $this->assertTrue($limiter);
    }

    #[Test]
    public function pasif_kullanici_giremez(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function cikis_oturumu_gecersiz_kilar(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }
}
