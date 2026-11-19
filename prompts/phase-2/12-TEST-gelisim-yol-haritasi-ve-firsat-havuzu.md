# Test Prompt 12 - Gelişim Yol Haritası ve Fırsat Havuzu Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Check-Up tespitinin doğru müşteri ve hizmete bağlı Opportunity oluşturduğu doğrulanıyor mu?
2. Opportunity status geçişleri izinli mi?
3. İnsan onayı olmadan iş/proje oluşmuyor mu?
4. Karar actor/time/mutabakat notu auditleniyor mu?
5. Ret/erteleme nedeni kaydedilebiliyor mu?
6. Mükerrer tespit aynı müşteri/hizmette kararlaştırılan kurala göre ele alınıyor mu?
7. Fırsat listesi filtre/pagination ve yetki scope koruyor mu?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
