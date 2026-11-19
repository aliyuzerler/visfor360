# Test Prompt 16 - REST API ve Webhook Altyapısı Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. OpenAPI spec endpoint ve request/response ile tutarlı mı?
2. Auth olmayan/yanlış scope'lu API isteği reddediliyor mu?
3. Rate limit ve max page size çalışıyor mu?
4. Webhook payload HMAC ile doğrulanıyor mu?
5. Replay/timestamp süresi geçmiş istekler reddediliyor mu?
6. Retry aynı side-effect'i çift oluşturmuyor mu?
7. Delivery attempt, response code ve failure nedeni loglanıyor mu?
8. Secret rotation/revoke sonrası eski secret geçersiz mi?
9. Gerçek dış servis entegrasyonları yanlışlıkla yapılmış mı?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
