# Visfor360 — Bütünleşik Danışmanlık ve İşletme Yönetim Platformu

Visfor'un müşteri kayıtlarını, kurumsal evraklarını, projelerini, departman iş akışlarını, görevlerini, stratejik hedeflerini, finans görünümünü ve çözüm ortaklarını tek bir web uygulamasında yöneten sistem.

> **Durum:** Geliştirme fazları `PROMPT_INDEX.md` sırasına göre yürütülüyor. Aşağıdaki tablo günceldir.

| Faz | Kapsam | Durum |
|---|---|---|
| 0 | Keşif, uygulama planı, proje iskeleti, ortamlar, CI | ✅ Tamamlandı |
| 1 | Tasarım sistemi, kimlik/RBAC/audit, CRM, evrak, pipeline/proje, görev, OKR, finans, dashboard | 🔧 Devam ediyor |
| 2 | Check-Up kural motoru, fırsat havuzu, çözüm ortağı, müşteri portalı, REST API/Webhook, canlıya geçiş | ⏳ Bekliyor |

## Teknoloji

| Katman | Sürüm | Not |
|---|---|---|
| PHP | 8.3 | |
| Laravel | 13.x (framework ^13.17) | Modüler monolit — [ADR-0001](docs/decisions/ADR-0001-modular-monolith.md) |
| Inertia | v3 + React 19 + TypeScript | SPA benzeri deneyim, tek deploy |
| Tailwind CSS | 4.x | Tasarım token'ları `docs/design-system.md` |
| PostgreSQL | 17 | Testler de PostgreSQL ile çalışır (tutarlılık) |
| Vite | 8.x | Production build |
| PHPUnit | 12 | Feature/unit testler |
| Pint + Larastan | latest | Stil + statik analiz |
| GitHub Actions | — | Tüm kalite kapısı CI'da çalışır ([.github/workflows/ci.yml](.github/workflows/ci.yml)) |

## Hızlı başlangıç

```bash
# 1) Bağımlılıklar
composer install
npm install

# 2) Ortam
cp .env.example .env        # PostgreSQL bağlantı bilgilerini düzenleyin
php artisan key:generate

# 3) Veritabanı (yalnızca boş/geliştirme DB'si)
php artisan migrate

# 4) Geliştirme sunucuları
php artisan serve           # http://localhost:8000
npm run dev                 # ayrı terminalde Vite

# Production build
npm run build
```

Sağlık kontrolü: `GET /health` → `{"status":"ok","db":"up"}` (konfigürasyon sızdırmaz).

## Testler ve kalite kapısı

```bash
php artisan test                      # PHPUnit (PostgreSQL test DB: visfor360_test)
vendor/bin/pint --test                # kod stili
vendor/bin/phpstan analyse --no-progress --memory-limit=1G   # statik analiz
npx tsc --noEmit                      # frontend tip kontrolü
npm run build                         # production build
php artisan migrate:fresh --env=testing --force   # migration smoke
```

Her faz kapanışında bu komutların tamamı çalıştırılır; `docs/TEST_STRATEGY.md` ve `docs/DEFINITION_OF_DONE.md` geçiş şartları uygulanır.

## İlk admin hesabı

Kimlik modülü Faz 1 (Prompt 03) ile birlikte gelir; bu noktada ilk sistem yöneticisi **`php artisan app:init-admin`** komutuyla (seed verisiyle değil) oluşturulur ve README güncellenir. Faz 1 kapanana kadar üretim kullanımı planlanmaz.

## Geliştirme süreci ve belgeler

- `AGENTS.md` — proje geliştirme kuralları (her oturumda uygulanır)
- `PROMPT_INDEX.md` — fazlar, geliştirme/test promptu eşlemeleri
- `docs/REPO_ASSESSMENT.md` — keşif ve stack uyum raporu
- `docs/IMPLEMENTATION_PLAN.md` — dosya/modül düzeyi plan
- `docs/OPEN_QUESTIONS.md` — müşteri P0 kararları (yanıtlanmadan iş kuralları sabitlenmez; konfigürasyonla yönetilir)
- `docs/ARCHITECTURE.md` + `docs/decisions/` — mimari ve ADR'ler
- `docs/TEST_STRATEGY.md`, `docs/DEFINITION_OF_DONE.md` — kalite şartları
- [docs/PACKAGE_README.md](docs/PACKAGE_README.md) — ZCode geliştirme paketi kullanımı

## Gizlilik

Bu depo public'tir: müşteri kaynak belgeleri (`references/`) ve tüm gizli değerler (`.env`, anahtarlar) repoya **girmez**. Uygulama içindeki tüm veri sentetik/örnek veridir.
