# Teknik Mimari Taslağı

Durum: Önerilen başlangıç mimarisi; hosting, kullanıcı/veri hacmi ve SaaS kararı sonrası onaylanmalı.

## Mimari stil
İlk sürümde modüler monolit. Tek deploy edilebilir uygulama içinde domain sınırları korunur. Mikroservis, cluster, Kubernetes veya dağıtık event bus kaynak SRS tarafından zorunlu tutulmuyor.

## Önerilen teknoloji seti
| Katman | Öneri | Gerekçe |
|---|---|---|
| Backend | Laravel - geliştirme tarihinde desteklenen kararlı sürüm | Auth, validation, policy, ORM, queue, scheduler, storage, API |
| Frontend | React + TypeScript + Inertia | Zengin dashboard/form/Kanban ve tek uygulama deploy'u |
| UI | Tailwind CSS + shadcn/ui veya erişilebilir eşdeğeri | Tutarlı ve hızlı UI bileşenleri |
| DB | PostgreSQL desteklenen kararlı sürüm | Transaction, relational integrity, JSONB |
| ORM | Eloquent | Laravel ile doğal migration/model ilişkisi |
| Queue | İlk sürüm database queue; ihtiyaçta Redis | Gereksiz servis karmaşasını önler |
| Files | Dev private local disk; prod S3-compatible private object storage | Dosyalar public web root dışında |
| API | REST `/api/v1` + OpenAPI | Dış uygulamalara dokümante erişim |
| Tests | PHPUnit/Pest + frontend tests + Playwright E2E | Domain, UI ve uçtan uca kalite |
| Deploy | Docker Compose/reverse proxy veya mevcut hosta uyumlu kontrollü deploy | Tek VPS'te tekrarlanabilir işletim |

Sürüm numaralarını prompt sırasında doğrula; resmi doküman ve uyumluluk kontrolü yap; lockfile commit et. Mevcut hosting PostgreSQL'i desteklemiyorsa MySQL/MariaDB alternatifi ayrıca ADR ile kararlaştırılmalıdır.

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
