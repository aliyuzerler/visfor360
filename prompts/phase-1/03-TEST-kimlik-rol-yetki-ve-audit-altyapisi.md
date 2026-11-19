# Test Prompt 3 - Kimlik Rol Yetki ve Audit Altyapısı Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Unauthenticated kullanıcı korumalı sayfaya/API'ye erişemiyor mu?
2. Yetkisiz rol create/update/archive/approve/export işlemlerini yapamıyor mu?
3. Aynı rolde olup farklı kayıt kapsamındaki kullanıcı başka müşteriyi göremiyor mu?
4. URL/ID değişikliği IDOR oluşturmuyor mu?
5. Kritik güncellemede actor/time/entity/action/before-after audit kaydı oluşuyor mu?
6. Audit kayıtları normal kullanıcı tarafından değiştirilemiyor/silinemiyor mu?
7. Hassas auth secret'ları loglarda görünmüyor mu?
8. Session logout ve password reset eski session/token davranışını doğru yönetiyor mu?
9. Role/permission seed tekrar çalışınca duplicate üretmiyor mu?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
