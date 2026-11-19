<?php

namespace Tests\Feature;

use App\Http\Controllers\PlaceholderController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShellPagesTest extends TestCase
{
    public static function pageProvider(): array
    {
        return collect(PlaceholderController::pageKeys())
            ->mapWithKeys(fn (string $key) => [$key => [$key]])
            ->all();
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
    public function welcome_sayfasi_herkeze_acik_tamamlanmis_dongu_icermez(): void
    {
        $content = $this->get('/app/dashboard')->getContent();

        // Placeholder ekranlarda sahte KPI/çalışmayan buton bulunmaz.
        $this->assertStringNotContainsStringIgnoringCase('KPI', $content);
    }
}
