# Visfor360 Tasarım Sistemi (v1 — Faz 1, Prompt 02)

> ⚠️ **GEÇİCİ NÖTR TEMA:** Logo/marka renkleri/fontu müşteri analizinde verilmemiştir
> (OPEN_QUESTIONS P2-29/30). Marka varlığı gelene kadar nötr kurumsal mavi-gri tema kullanılır;
> bileşenlere raw renk yazılmaz, tüm değişim `resources/css/app.css` içindeki token bloğunda yapılır.
> **Logo uydurulmamış; "V" yer tutucu işarettir.**

## 1. Token sözlüğü (`resources/css/app.css` `@theme` bloğu)

| Grup | Token | Değer (oklch) | Kullanım |
|---|---|---|---|
| Zemin | `--color-background` | 0.985 0.002 247.9 | Uygulama arka planı |
| Zemin | `--color-surface` | 1 0 0 | Kart, panel, menü yüzeyi |
| Zemin | `--color-surface-muted` | 0.967 0.003 247.9 | Hover, tablo başlığı, skeleton |
| Metin | `--color-foreground` | 0.208 0.042 265.8 | Ana metin |
| Metin | `--color-muted-foreground` | 0.446 0.03 256.8 | İkincil metin, açıklama |
| Çizgi | `--color-border` | 0.929 0.013 255.5 | Kenarlıklar, ayraçlar |
| Marka | `--color-primary` / `-hover` / `-foreground` | mavi | Birincil aksiyonlar, aktif nav |
| Durum | `--color-success(-bg)`, `--color-warning(-bg)`, `--color-danger(-bg)`, `--color-info(-bg)` | yeşil/turuncu/kırmızı/mavi | Badge ve bildirimler; **status asla yalnız renkle anlatılmaz, her zaman metinle birlikte** |
| Tipografi | `--font-sans` | sistem yığını | Marka fontu müşteri kararı |
| Radius | `--radius-sm..xl` | 0.25–0.75rem | Bileşen köşeleri |
| Gölge | `--shadow-card`, `--shadow-popover` | sınırlı elevation | Kartlar ve yüzen katmanlar |
| Z-index | `--z-dropdown:50`, `--z-dialog:100`, `--z-toast:150` | katman sırası | Overlay çakışmasını önler |
| Hareket | `--duration-fast:120ms`, `--duration-base:180ms`, `--ease-default` | geçişler | `prefers-reduced-motion` ile sıfırlanır |
| Odak | `:focus-visible` outline | 2px primary | Klavye odak halkası (WCAG 2.2) |

Spacing Tailwind'in 4px tabanlı ölçeği kullanır (`gap-2`, `px-4`…); keyfi değer yazılmaz.

## 2. Bileşen envanteri (`resources/js/components/ui/`)

| Bileşen | Dosya | Varyantlar / notlar |
|---|---|---|
| Button | `button.tsx` | primary/secondary/tertiary/destructive × sm/md/lg/icon + `loading` (spinner + `aria-busy` + disabled) |
| Input / Textarea / Select | `inputs.tsx` | disabled, placeholder stilleri; native select (erişilebilirlik) |
| FormField | `form-field.tsx` | label ↔ kontrol `html_for` eşleme, hint (`aria-describedby`), hata (`role="alert"`, `aria-invalid`), zorunlu yıldız |
| Checkbox | `form-field.tsx` | label ile paketli |
| Badge | `indicators.tsx` | neutral/info/success/warning/danger — metin + renk |
| Card | `indicators.tsx` | Card/Header/Title/Body |
| Skeleton | `indicators.tsx` | `aria-hidden` yükleme iskeleti |
| Table | `table.tsx` | Table/THead/TH(scope=col)/TBody/TR/TD + yatay kaydırma kapsayıcı |
| Dialog | `dialog.tsx` | Radix: odak yakalama, ESC, başlayıp `title`/`description` zorunlu |
| Tabs | `tabs-tooltip.tsx` | Radix: ok tuşlarıyla gezinme |
| Tooltip | `tabs-tooltip.tsx` | hover **ve focus** |
| Breadcrumbs | `page.tsx` | `aria-label="Sayfa yolu"`, son öğe `aria-current` |
| PageHeader | `page.tsx` | başlık + açıklama + primary action alanı |
| EmptyState | `page.tsx` | açıklama + ilk aksiyon; sahte veri yerine kullanılır |
| Toast | `toast.tsx` | sonner; sağ üst, async sonuç duyurusu |
| UserMenu | `app/user-menu.tsx` | Radix dropdown; oturum yoksa isimsiz avatar (ölü buton yok) |
| AppShell | `app/app-shell.tsx` | sidebar (≥lg), mobil dialog menü, topbar, content max-width |

## 3. Do / Don't

**Yap:**
- Tüm renk/spacing/radius değerleri token'dan; theme değişimi tek dosyada.
- Her liste ekranında loading (skeleton), empty (EmptyState), error (toast + hata metni), success (toast) durumları tasarla.
- Form kontrolleri her zaman FormField ile: label, hint, error ilişkilendirilmiş.
- Kanban/aşama değişimlerinde klavye alternatifi (menü ile taşı) sun.

**Yapma:**
- Raw hex/oklch değeri bileşen içinde.
- Sahte KPI, çalışmayan buton, `href="#"` ölü bağlantı.
- Status'u yalnız renkle ifade etme.
- Modal zinciri (dialog içinden dialog açma).

## 4. Navigasyon (`resources/js/nav.ts`)

`docs/SCREEN_MAP.md` ile birebir eşleşir; Faz 2 ekranları (Check-Up, Fırsat Havuzu, Çözüm Ortakları) **"Faz 2" rozetiyle kontrollü sunulur** (gizli değil, placeholder sayfa). Kilitli/gelecek göstermek yerine ürün kararı gereği navigation'da görünüp wireframe içerir.

## 5. Responsive davranış

- ≥1024px: sabit sidebar 264px + içerik max-w-7xl.
- <1024px: sidebar Radix dialog ile açılır (focus yönetimi dahil), topbar'da menü butonu.
- Tablolar `overflow-x-auto` kapsayıcıda; 390px'te yatay taşma yok (Test 02 madde 3'te doğrulanır).
