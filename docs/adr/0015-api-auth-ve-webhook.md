# ADR-0015: API kimlik doğrulaması ve webhook altyapısı

## Bağlam
SRS gelecek entegrasyonlar için dokümante API/Webhook altyapısı ister; hangi olayların yayınlanacağı ve hangi API kapsamlarının açılacağı müşteri kararıdır.

## Karar
- **Kimlik:** Bearer token; yalnız sha256 hash saklanır; scopes allowlist (customers.read, customers.write, projects.read, webhooks.manage); expiry + revocation. Token oluşturma iç yönetimden (settings) olur; secret frontend bundle/URL'de gösterilmez.
- **Rate limit:** 60 istek/dakika, IP bazlı, cache tabanlı RateLimiter (Redis zorunlu değil). Max page size 100.
- **Webhook:** endpoint secret'ı sha256 hash; HMAC-SHA256 imza (secret.timestamp.body); ±300 sn replay penceresi; event uuid deterministik (idempotency); delivery unique (endpoint,event) — retry duplicate side-effect üretmez; backoff 60s*2^n, 5 deneme sonrası dead-letter; manuel redrive sonraki sürümde.
- **Kapsam:** Yalnız test sink ile doğrulanır; gerçek WhatsApp/e-posta/muhasebe sağlayıcı entegrasyonu AYRI kapsamdır. Webhook yönetim ekranı (endpoint CRUD UI) sonraki sürümde; altyapı tamam.
- Yayınlacak olay listesi müşteri onayına bağlı (enabled_events boş → yayınlanmaz).

## Sonuç
Versiyonlu API iskeleti + OpenAPI (public/api/openapi.json) + webhook delivery altyapısı testlerle doğrulandı.
