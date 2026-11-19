# Quality Prompt Q02 - Bağımsız Güvenlik Denetimi

## Kullanılacak skill'ler
`$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Görev
Geliştirmeyi yapan ajan değilmiş gibi bağımsız inceleme yap. Threat model'i güncelle; auth/session, role/permission, customer record scope, IDOR, mass assignment, CSRF, XSS, SQL injection, file upload/download, audit, finance, portal, API token ve webhook akışlarını kontrol et. Mümkün olan güvenli testleri çalıştır; canlı sisteme saldırı veya gerçek veri kullanma. Bulguları Critical/High/Medium/Low ayır; reproduction, etkilenen veri/route ve remediation ver. Critical/High bulgu varsa release BLOCKED yaz. Kod değişikliği yapmadan önce bulguları raporla; düzeltme için ayrı onay/iş kalemi öner.

## Çıktı
`docs/SECURITY_REVIEW.md` ve test kanıtları.
