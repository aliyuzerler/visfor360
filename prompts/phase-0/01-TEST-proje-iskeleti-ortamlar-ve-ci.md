# Test Prompt 1 - Proje İskeleti Ortamlar ve CI Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Temiz checkout ile README adımlarından uygulama ayağa kalkıyor mu?
2. Migration boş test DB'ye baştan uygulanıyor mu?
3. `.env` Git'te izlenmiyor ve örnek dosya secret içermiyor mu?
4. Backend test, frontend test, lint, type-check ve production build geçiyor mu?
5. CI aynı doğrulamaları otomatik çalıştırıyor mu?
6. DB public port olarak açılmamış mı?
7. Health endpoint hassas konfigürasyon veya stack trace sızdırmıyor mu?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
