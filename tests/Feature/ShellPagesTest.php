<?php

namespace Tests\Feature;

use App\Http\Controllers\PlaceholderController;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShellPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());
        // AuthenticateSession oturum hash damgası nedeniyle gerçek login akışı kullanılır.
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);
    }

    #[Test]
    public function dashboard_kabugu_ile_acilir(): void
    {
        $this->get('/app/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('dashboard'));
    }

    #[Test]
    public function kok_dashboarda_yonlendirir(): void
    {
        $this->get('/')->assertRedirect('/app/dashboard');
    }

    public static function pageProvider(): array
    {
        return collect(PlaceholderController::pageKeys())
            ->mapWithKeys(fn (string $key) => [$key => [$key]])
            ->all();
    }

    #[Test]
    #[DataProvider('pageProvider')]
    public function her_ekran_kabugu_deterministik_acar(string $pageKey): void
    {
        $this->get('/app/'.$pageKey)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('placeholder')
                ->hasAll(['title', 'description', 'subScreens', 'phase']));
    }

    #[Test]
    public function bilinmeyen_ekran_404_doner(): void
    {
        $this->get('/app/olmayan-ekran')->assertNotFound();
    }

    #[Test]
    public function placeholder_ekranlar_sahte_kpi_icermez(): void
    {
        // Placeholder (Faz 2 bekleyen) ekranlarda sahte KPI/çalışmayan buton bulunmaz.
        // Dashboard artık gerçek aggregate KPI'ları sunar (Prompt 10) — bu yüzden
        // kontrol placeholder ekranlar üzerinden yapılır.
        $reports = $this->get('/app/reports')->getContent();
        $this->assertStringNotContainsStringIgnoringCase('kpi', $reports);

        // Dashboard'da wireframe etiketi kalmamıştır; gerçek kpi props'u vardır.
        $dashboard = $this->get('/app/dashboard')->getContent();
        $this->assertStringNotContainsStringIgnoringCase('Wireframe', $dashboard);
        $this->assertStringContainsString('"kpi"', $dashboard);
    }
}
