# Test Prompt 13 - Onaylı Fırsatı Projeye Dönüştürme Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Onaysız/eksik mutabakatlı fırsat dönüşemiyor mu?
2. Yetkisiz rol convert çağrısını yapamıyor mu?
3. Aynı request tekrarlandığında sadece bir proje oluşuyor mu?
4. Proje doğru müşteri, hizmet, departman ve pipeline ile mi oluşuyor?
5. DB transaction ortasında hata olursa kısmi veri kalmıyor mu?
6. Conversion actor/time ve source opportunity bağı auditleniyor mu?
7. UI sonucu kullanıcıya doğru gösteriyor mu?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
