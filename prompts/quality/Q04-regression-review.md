# Quality Prompt Q04 - Fazlar Arası Regresyon Denetimi

## Kullanılacak skill'ler
`$visfor-test-engineering` (`skills/testing/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-domain-architecture` (`skills/architecture/SKILL.md`)

## Görev
Son değişikliklerin önceki modülleri bozup bozmadığını kontrol et. Tüm migration'ları boş test DB üzerinde baştan uygula. Auth/RBAC, müşteri, custom fields, belge, pipeline/proje, görev, OKR, finans; Faz 2'de Check-Up, opportunity, conversion, partner, portal, API ve webhook akışlarının ilgili testlerini çalıştır. Özellikle permission ve customer scope değişikliklerinin eski endpoint'lere etkisini denetle. Build, lint, type-check, unit, feature ve kritik E2E testlerini çalıştır. Kırık testleri düzelt veya açıkça BLOCKED raporla.

## Çıktı
`docs/REGRESSION_REPORT.md`, başarısız test listesi, release önerisi.
