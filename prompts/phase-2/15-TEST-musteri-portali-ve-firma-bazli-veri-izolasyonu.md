# Test Prompt 15 - Müşteri Portalı ve Firma Bazlı Veri İzolasyonu Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Portal user A müşteri A kayıtlarına erişiyor mu?
2. URL/ID/customer_id değiştirerek müşteri B projesi/belgesi okunamıyor mu?
3. Upload endpoint başka müşterinin document request'ine dosya bağlayamıyor mu?
4. İç notlar, finans ve fırsat verileri portala sızmıyor mu?
5. Private file download için membership/permission kontrolü var mı?
6. Pasif portal kullanıcısı erişemiyor mu?
7. Aynı firmada çoklu üyelik kararı uygulandıysa üyelik iptali anında erişim kesiliyor mu?
8. Portal responsive ve klavye erişilebilir mi?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
