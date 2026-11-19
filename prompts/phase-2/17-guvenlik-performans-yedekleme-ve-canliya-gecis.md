# Prompt 17 - Güvenlik Performans Yedekleme ve Canlıya Geçiş

## Ön koşul ve bağlam
Bu prompt tüm modüller tamamlandıktan sonra release hardening içindir. Gerçek hosting ve üretim yük hedefleri müşteriyle netleştirilmiş olmalı.

## Kullanılacak skill'ler
`$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-performance-engineering` (`skills/performance/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`), `$visfor-accessibility-responsive` (`skills/accessibility/SKILL.md`), `$visfor-domain-architecture` (`skills/architecture/SKILL.md`)

## Geliştirme görevleri
1. Threat model ve tüm modüller için OWASP tarzı uygulama güvenliği incelemesi yap.
2. Auth, RBAC, IDOR, portal isolation, file storage, audit, finance, API ve webhook özel testlerini çalıştır.
3. Dependency vulnerability, secret scan, static analysis ve production config kontrolü yap.
4. Query profiling, N+1, index, pagination, dashboard cache, queue ve frontend bundle baseline ölç.
5. Backup DB + file storage planı ve gerçek restore testini otomatikleştir/dokümante et.
6. Worker, scheduler, health check, log, error reporting ve alarm durumunu test et.
7. Staging deploy/rollback planı ve production runbook oluştur.
8. UAT, eğitim, release notes, known limitations ve handover checklist hazırla.
9. Critical/High açık bulgu varsa release blocker olarak işaretle ve düzeltmeden canlıya geçme.

## Beklenen çıktılar
`docs/SECURITY_REVIEW.md`, `docs/PERFORMANCE_BASELINE.md`, `docs/BACKUP_RESTORE.md`, `docs/PRODUCTION_RUNBOOK.md`, UAT/release checklist ve test raporu.

## Sınırlar ve güvenlik kuralları
Gerçek üretim verisini staging'e kopyalama. Performans hedefi uydurma; test ortamını raporla. Yedek var diye restore doğrulanmış sayma. Canlı deploy için açık müşteri onayı ve rollback gerekir.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
