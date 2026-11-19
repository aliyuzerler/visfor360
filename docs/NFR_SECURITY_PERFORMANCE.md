# Güvenlik, Performans ve İşletim Gereksinimleri

## Güvenlik
- HTTPS; Secure/HttpOnly/SameSite cookie; CSRF koruması; brute-force/rate-limit.
- Güvenli session/password-reset yaklaşımı; yönetici MFA müşteriyle teyit edilerek önerilir.
- RBAC + record scope server-side policy.
- Portalda her istekte müşteri üyeliği ve customer scope.
- Dosyalar private storage; allowlist MIME/uzantı, boyut limiti, random storage key, güvenli indirme.
- Audit append-only; before/after ve actor/time; secret redaction.
- API auth scope, rate limit, versioning; webhook HMAC signature, replay/idempotency, retry ve delivery log.
- Input validation, output encoding, ORM parameterization; production hata ekranında stack trace yok.
- Secrets `.env`/secret store'da; repoya/loga yazılmaz.

## KVKK / veri yaşam döngüsü
Veri sorumlusu/veri işleyen rolleri, aydınlatma, saklama, silme/anonimleştirme ve başvuru süreçleri müşteri/hukuk danışmanıyla kararlaştırılır. Yazılım teknik kontrol sağlar; hukuki metin ve yükümlülük varsayılmaz.

## Performans
SRS eşzamanlı kullanıcı, kayıt hacmi veya yanıt süresi SLA'sı tanımlamıyor. Hedef uydurma. Önce beklentiyi al, sonra staging baseline ölç.
- Büyük listelerde pagination/server-side filter/sort.
- N+1 ve slow query incelemesi; gerekli indeksler.
- Dashboard aggregation/caching kontrollü; cache invalidation tanımlı.
- Ağır rapor, dosya, webhook işleri queue.
- API page size/rate limit.
- Frontend bundle/lazy load; büyük tabloda sanallaştırma ölçüme göre.
- p50/p95/p99, error rate, DB query count, queue wait/duration ve bundle ölç.

## Backup ve gözlemlenebilirlik
DB ve dosyalar ayrı yedeklenir; otomatik yedek ve periyodik restore testi şart. RPO/RTO, sıklık, saklama, şifreleme ve işletim sorumlusu kararlaştırılır. Uygulama/worker/scheduler hata logları, health check ve alarm olmalıdır. Hassas veri loglanmamalıdır.
