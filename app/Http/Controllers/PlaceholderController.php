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
        'projects' => [
            'title' => 'Projeler',
            'description' => 'Hizmet türüne göre farklı Kanban/pipeline ile yürütülen proje yönetimi. Her hizmet kaleminin kendi aşama şablonu vardır (müşteri kararı ile tanımlanır).',
            'subScreens' => [
                'Proje listesi ve arama',
                'Hizmet kataloğu ve pipeline şablonları',
                'Hizmete özel Kanban panosu ve aşama geçmişi',
            ],
            'phase' => '1',
        ],
        'tasks' => [
            'title' => 'Görevler',
            'description' => 'Ekip iş yönetimi: görevler atanır, ilerletilir, geçmişi saklanır; stratejik hedeflerle ilişkilendirilir.',
            'subScreens' => ['Benim görevlerim', 'Ekip görevleri', 'Görev detayı, atama ve geçmiş'],
            'phase' => '1',
        ],
        'goals' => [
            'title' => 'Stratejik Hedefler',
            'description' => '5 yıllık vizyon ve 12 ana hedef bağlantılı OKR takibi. İlerleme yalnızca onaylı ölçüm formülü ile hesaplanır; formül yoksa yüzde gösterilmez.',
            'subScreens' => ['Vizyon ekranı', '12 ana hedef listesi ve detayı', 'Görev-hedef bağlantıları ve ilerleme'],
            'phase' => '1',
        ],
        'finance' => [
            'title' => 'Finans',
            'description' => 'Yönetimsel finans takibi: gelir-gider, tahsilat/ödeme, P&L ve nakit akışı. Resmi muhasebe aracı değildir; tahmini ve gerçekleşen açıkça ayrılır.',
            'subScreens' => ['Gelir/gider kayıtları', 'P&L görünümü', 'Nakit akışı ve runway (onaylı formülle)'],
            'phase' => '1',
        ],
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
