# ADR-0002 - Önerilen Teknoloji Seti
- Durum: Kabul edildi (2026-11-19, Prompt 01 kurulumu ile doğrulandı). Hosting, müşteri teyidine bağlıdır.
- Backend: PHP 8.3 + Laravel 13 (framework ^13.17, `composer.lock` sabitlenmiştir).
- Frontend: React 19 + TypeScript + Inertia v3 (`inertiajs/inertia-laravel 3.7`); UI için Tailwind CSS 4 + erişilebilir bileşen seti; Vite 8 build.
- DB: PostgreSQL 17 (local + test + CI aynı motor; hosting desteklemiyorsa MySQL/MariaDB alternatifi ayrıca kararlaştırılır).
- Queue: database queue başlangıç; ihtiyaçta Redis.
- Storage: dev private local disk; production private S3-compatible object storage.
- API: REST `/api/v1` + OpenAPI (Faz 2).
- Tests: PHPUnit 12 + frontend type-check (tsc) + Playwright E2E (kritik akışlar).
- Kalite kapısı: Pint, Larastan 3, tsc, Vite build, migration smoke; hepsi CI'da (.github/workflows/ci.yml).

Neden: Tek uygulama deploy'u, Laravel'in auth/policy/queue/storage/migration ekosistemi ve React'in yoğun dashboard/form/Kanban UI yeteneği. SRS teknoloji dayatmıyor; müşteri/hosting farklı bir kurumsal standart getirirse ADR revize edilir. Sürümler kurulum sırasında Packagist/npm resmi kaynağından doğrulanıp lockfile ile sabitlendi. Alternatif değerlendirme: Laravel 13 resmi React starter kit (wayfinder/vite-plus/chisel araç zinciri) çok yeni olduğu için klasik skeleton + Inertia v3 kablosu seçildi; gerekçe kararsız deneysel araçlara bağımlılık riski.
