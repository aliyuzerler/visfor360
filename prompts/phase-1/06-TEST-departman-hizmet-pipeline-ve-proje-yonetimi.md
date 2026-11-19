# Test Prompt 6 - Departman Hizmet Pipeline ve Proje Yönetimi Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Farklı ServiceType kayıtları kendi PipelineStage listesini kullanıyor mu?
2. Yetkisiz kullanıcı stage değiştiremiyor mu?
3. Geçersiz geçiş backend'de reddediliyor mu?
4. Stage change history actor/time/old/new saklıyor mu?
5. Terminal stage ve archive davranışı doğru mu?
6. Çok sayıda kartta pagination/performans kabul edilebilir mi?
7. Aynı projede yanlış pipeline kullanımı engelleniyor mu?
8. UI klavye ile stage move alternatifi sunuyor mu?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
