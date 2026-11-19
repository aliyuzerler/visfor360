# Prompt 1 - Proje İskeleti Ortamlar ve CI

## Ön koşul ve bağlam
Prompt 00 raporu ve stack tercihi onaylanmış olmalı. Varsayılan stack Laravel + React + TypeScript + Inertia + Tailwind + PostgreSQL. Mevcut repo varsa onu koru.

## Kullanılacak skill'ler
`$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. Resmi dokümanlardan uyumlu kararlı framework/runtime sürümlerini doğrula; versiyon ve nedenlerini raporla.
2. Uygulama iskeletini, React/Inertia frontend'i, Vite build'i ve PostgreSQL bağlantısını kur.
3. Local development için Docker Compose kullanımı gerekiyorsa yalnızca gerekli servisleri ekle. DB portunu public açma.
4. `.env.example`, `.gitignore`, formatter/linter, static analysis, type-check ve test komutlarını oluştur.
5. Health check ve güvenli global error handling ekle.
6. CI pipeline'da dependency install, lint, static/type analysis, tests, build ve migration smoke çalıştır.
7. README'ye setup, migration, seed, test, build ve ilk admin bootstrap yöntemini yaz.
8. `docs/ARCHITECTURE.md` ve ADR'leri kurulumda alınan kararlara göre güncelle.

## Beklenen çıktılar
Çalışan temel uygulama, lockfile, baseline migration, `.env.example`, CI config, README, health endpoint, test skeleton ve seçildiyse Docker Compose.

## Sınırlar ve güvenlik kuralları
Gerçek secret/production DB kullanma. `.env` commit etme. PostgreSQL'i internete açma. Sürümleri tahmin etme. Production deploy yapma. Var olan çalışan uygulamayı izinsiz sıfırdan üretme.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
