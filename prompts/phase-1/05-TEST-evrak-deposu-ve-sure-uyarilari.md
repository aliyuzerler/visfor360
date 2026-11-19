# Test Prompt 5 - Evrak Deposu ve Süre Uyarıları Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Yetkisiz kullanıcı belge indirebiliyor mu? Denemeli ve engellendiğini kanıtla.
2. Yanlış MIME/extension, aşırı boyut ve path traversal denemeleri reddediliyor mu?
3. Dosya storage path'i public erişime açık değil mi?
4. Süresi dolan ve eksik belge farklı badge/status üretiyor mu?
5. Geçerlilik tarihi/timezone sınırları doğru mu?
6. Belge sürümü değişince eski sürüm/audit korunuyor mu?
7. Scheduler/job tekrar çalıştığında duplicate bildirim üretmiyor mu?
8. Portal/customer scope henüz yoksa bile backend document policy gelecekte scope eklemeye uygun mu?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
