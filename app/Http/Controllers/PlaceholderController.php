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
        'checkups' => [
            'title' => 'Check-Up',
            'description' => 'Şirket Check-Up şablonları, soru bankası, oturumlar ve deklaratif kural motoru. Soru/kural seti müşteri girdisiyle tanımlanır; eval çalıştırılmaz.',
            'subScreens' => ['Şablon ve sürüm yönetimi', 'Soru bankası', 'Oturum, sonuç ve gelişim yol haritası'],
            'phase' => '2',
        ],
        'opportunities' => [
            'title' => 'Fırsat Havuzu',
            'description' => 'Check-Up tespitleri insan onayına düşer. Müşteri mutabakatı ve yetkili onayı olmadan işe dönüşmez.',
            'subScreens' => ['Fırsat listesi ve inceleme', 'Müşteri mutabakat kaydı', 'Onay/red ve tekil proje dönüşümü'],
            'phase' => '2',
        ],
        'partners' => [
            'title' => 'Çözüm Ortakları',
            'description' => 'Dış paydaşların yürüttüğü işler, yönlendirme, komisyon ve hakediş takibi. Oran/matrah kuralları müşteri kararıdır.',
            'subScreens' => ['Partner listesi ve sözleşme bilgisi', 'İş/referral kayıtları', 'Komisyon tahakkuku ve hakediş ödemesi'],
            'phase' => '2',
        ],
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
