# Teknik Mimari Taslağı

Durum: Önerilen başlangıç mimarisi; hosting, kullanıcı/veri hacmi ve SaaS kararı sonrası onaylanmalı.

## Mimari stil
İlk sürümde modüler monolit. Tek deploy edilebilir uygulama içinde domain sınırları korunur. Mikroservis, cluster, Kubernetes veya dağıtık event bus kaynak SRS tarafından zorunlu tutulmuyor.

## Önerilen teknoloji seti
| Katman | Karar (2026-11-19, ADR-0002) | Gerekçe |
|---|---|---|
| Backend | PHP 8.3 + Laravel 13 (^13.17) | Auth, validation, policy, ORM, queue, scheduler, storage, API |
| Frontend | React 19 + TypeScript + Inertia v3 | Zengin dashboard/form/Kanban ve tek uygulama deploy'u |
| UI | Tailwind CSS 4 + erişilebilir bileşen seti | Tutarlı ve hızlı UI bileşenleri |
| DB | PostgreSQL 17 | Transaction, relational integrity, JSONB; local/test/CI aynı motor |
| ORM | Eloquent | Laravel ile doğal migration/model ilişkisi |
| Queue | İlk sürüm database queue; ihtiyaçta Redis | Gereksiz servis karmaşasını önler |
| Files | Dev private local disk; prod S3-compatible private object storage | Dosyalar public web root dışında |
| API | REST `/api/v1` + OpenAPI | Dış uygulamalara dokümante erişim |
| Tests | PHPUnit 12 + tsc + Playwright E2E | Domain, UI ve uçtan uca kalite |
| Deploy | Docker Compose/reverse proxy veya mevcut hosta uyumlu kontrollü deploy | Tek VPS'te tekrarlanabilir işletim |

Sürüm numaraları kurulum sırasında Packagist/npm resmi kaynağından doğrulandı; lockfile'lar (composer.lock, package-lock.json) commit edildi. Alternatif değerlendirme: resmi React starter kit'in deneysel araç zinciri (wayfinder/vite-plus/chisel) yerine klasik skeleton seçildi (ADR-0002). Mevcut hosting PostgreSQL'i desteklemiyorsa MySQL/MariaDB alternatifi ayrıca ADR ile kararlaştırılmalıdır.

## Domain modülleri
Identity; Customers; Documents; Organization; Services; Projects; Tasks; Strategy; Finance; Checkups; Opportunities; Partners; Portal; Audit; Integrations; Notifications.

Her modül kendi Models, Policies, Requests, Actions/Services, Events ve Tests sınırlarına sahip olmalıdır. Controller'lar orchestration/HTTP bağlamıyla sınırlı kalır.

## Veri modelleme
- Çekirdek alanlar normal relational kolonlarda.
- Custom Fields yalnızca esnek müşteri sicil alanlarında kontrollü EAV/metadata modeliyle.
- Alan tanımları ve değerleri ayrı tutulur; tipi, required, validation, options, visibility, searchable/filterable/reportable metadata'da tanımlanır.
- Para `DECIMAL` ve currency code ile saklanır.
- Zaman UTC saklanır, kullanıcı timezone'una çevrilir.
- Audit append-only ve gizli değerleri maskeleyen yapıda.

## Customer izolasyonu / SaaS farkı
İlk kurulum tek Visfor içindir. `Customer` Visfor'un hizmet verdiği firmadır; portal erişim sınırı olarak kullanılır. Portal sorgusu her zaman authenticated user -> customer membership -> customer scope ile daraltılır. Bu, tek başına farklı danışmanlık şirketlerine SaaS tenant mimarisi anlamına gelmez. SaaS istenirse ayrı tenant ADR gerekir.

## Atomik akışlar
Pipeline stage change + history + audit; finance post/cancel; opportunity conversion; partner payout; OKR snapshot işlemleri transaction içinde gerçekleştirilir. Queue job'ları retry-safe ve idempotent olur.

## Ortamlar
Local: Docker/SQLite değil, mümkünse PostgreSQL ile tutarlı DB; Test: izole DB ve fake mail/storage; Staging: prod benzeri ve sentetik veri; Production: HTTPS, secret injection, private storage, worker/scheduler, backup ve monitoring.
