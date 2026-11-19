# Prompt 16 - REST API ve Webhook Altyapısı

## Ön koşul ve bağlam
SRS gelecekte e-posta, WhatsApp API ve resmi muhasebe gibi sistemlerle konuşabilecek dokümante API/Webhook altyapısı ister. Bu altyapının teslimi, her gerçek servisin entegrasyonunun tamamlandığı anlamına gelmez.

## Kullanılacak skill'ler
`$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-performance-engineering` (`skills/performance/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. REST API `/api/v1` namespace oluştur; OpenAPI/Swagger dokümantasyonu üret.
2. API authentication seçimini ADR ile açıkla; token scopes, expiry/rotation ve revoke desteği uygula.
3. Endpoint'leri version, validation, pagination, filtering, consistent error response ve rate limit standardına al.
4. Domain event -> webhook event mapping'i oluştur; hangi olayların yayınlanacağı müşteri onayına bağlıdır.
5. Webhook endpoint yönetimi, secret generation/rotation, HMAC signing, timestamp ve delivery attempt loglarını ekle.
6. Retry/backoff, dead-letter/failed delivery görünümü ve manuel redrive işlemi tasarla.
7. Idempotency ve replay koruması ekle.
8. API erişimlerini ve webhook konfigürasyon değişikliklerini audit et.
9. Sadece test sink/mock ile doğrula. Gerçek WhatsApp/e-posta/muhasebe sağlayıcısı entegrasyonunu ayrı kapsam olarak bırak.

## Beklenen çıktılar
Versiyonlu API iskeleti, OpenAPI docs, API credentials/scopes, webhook endpoint/delivery, retry/idempotency, audit ve testler.

## Sınırlar ve güvenlik kuralları
API secret'larını frontend bundle veya URL query'de gösterme. Webhook imzasız/kimliği doğrulanmamış event kabul etme. Retry duplicate side-effect oluşturmamalı. Gerçek provider credential kullanma.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
