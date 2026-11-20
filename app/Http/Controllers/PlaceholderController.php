<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Faz 1/2 placeholder ekranlarının merkezi konfigürasyonu.
 * Her modül gerçek sayfası geldiğinde kendi controller/route'u ile değiştirilir
 * ve bu listeden çıkarılır (nav.ts ile SCREEN_MAP eşleşmesi korunur).
 */
class PlaceholderController extends Controller
{
    /**
     * @var array<string, array{title: string, description: string, subScreens: string[], phase: string}>
     */
    private const PAGES = [
        'reports' => [
            'title' => 'Raporlar',
            'description' => "Müşteri, proje, departman, görev, hedef ve finans raporları. Faz 1'de temel raporlar; Faz 2'de gelişmiş kırılımlar.",
            'subScreens' => ['Müşteri ve proje raporları', 'Departman/görev raporları', 'Hedef ve finans raporları'],
            'phase' => '1',
        ],
    ];

    public function show(string $page): Response
    {
        abort_unless(isset(self::PAGES[$page]), 404);

        return Inertia::render('placeholder', self::PAGES[$page]);
    }

    /**
     * Placeholder konfigürasyonu — rota tanımları ve testler için erişilebilir.
     */
    public static function pageKeys(): array
    {
        return array_keys(self::PAGES);
    }
}
