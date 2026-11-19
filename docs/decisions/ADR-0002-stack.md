# ADR-0002 - Önerilen Teknoloji Seti
- Durum: Hosting ve müşteri teyidine bağlı öneri.
- Backend: Laravel'in başlangıç tarihinde desteklenen kararlı sürümü.
- Frontend: React + TypeScript + Inertia; UI için Tailwind ve erişilebilir bileşen seti.
- DB: PostgreSQL; hosting desteklemiyorsa MySQL/MariaDB alternatifi ayrıca kararlaştırılır.
- Queue: database queue başlangıç; ihtiyaçta Redis.
- Storage: dev private local; production private S3-compatible object storage.
- API: REST `/api/v1` + OpenAPI.
- Tests: PHPUnit/Pest, frontend testleri, Playwright E2E.

Neden: Tek uygulama deploy'u, Laravel'in auth/policy/queue/storage/migration ekosistemi ve React'in yoğun dashboard/form/Kanban UI yeteneği. SRS teknoloji dayatmıyor; müşteri/hosting farklı bir kurumsal standart getirirse ADR revize edilir. Exact paket sürümleri kurulum sırasında resmi dokümandan doğrulanır.
