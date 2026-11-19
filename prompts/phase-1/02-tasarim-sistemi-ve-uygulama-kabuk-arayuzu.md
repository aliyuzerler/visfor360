# Prompt 2 - Tasarım Sistemi ve Uygulama Kabuk Arayüzü

## Ön koşul ve bağlam
Çalışan uygulama iskeleti var. Logo/marka renkleri kaynak analizde verilmemiştir. Marka dosyaları yoksa geçici kurumsal tema oluştur; Visfor marka kimliği uydurma.

## Kullanılacak skill'ler
`$visfor-brand-design-system` (`skills/brand-design-system/SKILL.md`), `$visfor-enterprise-ui-ux` (`skills/enterprise-ui-ux/SKILL.md`), `$visfor-accessibility-responsive` (`skills/accessibility/SKILL.md`)

## Geliştirme görevleri
1. Merkezi design tokens oluştur: renk, tipografi, spacing, radius, shadow, focus ring, z-index ve motion.
2. Button, input, select, textarea, form field, badge, card, table, dialog, drawer, tabs, breadcrumb, toast, tooltip, skeleton ve empty-state bileşenleri kur.
3. Admin app shell oluştur: sidebar, topbar, user menu, breadcrumb, page header ve responsive content region.
4. Menüleri `docs/SCREEN_MAP.md` ile eşleştir. Faz 2 modüllerini kilitli/gelecek olarak göstermek yerine ürün kararı doğrultusunda navigation'da kontrollü sun.
5. Gerçek ürün sayfaları yerine başlangıç wireframe/placeholder sayfaları oluştur; sahte KPI koyma.
6. Light theme'i eksiksiz uygula. Dark mode yalnızca müşteri isterse ayrı kapsam.
7. Keyboard focus, responsive davranış ve erişilebilir component state'lerini uygula.
8. `docs/design-system.md` ve component inventory dokümanı üret.

## Beklenen çıktılar
Design token dosyaları, reusable UI kit, responsive app shell, nav config, geçici tema notu, tasarım sistemi dokümantasyonu.

## Sınırlar ve güvenlik kuralları
Harici görsel/marka varlığı olmadan logo üretme. Dashboard'a gerçekmiş gibi sayı koyma. Her sayfaya aynı dekoratif kart şablonunu dayatma. Bileşen paketini yalnızca gerekliyse ekle ve lisans/uyumluluğunu kontrol et.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
