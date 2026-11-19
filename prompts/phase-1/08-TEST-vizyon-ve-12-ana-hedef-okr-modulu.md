# Test Prompt 8 - Vizyon ve 12 Ana Hedef OKR Modülü Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Tam 12 hedef kaydı yalnız müşteri sağladığında seed ediliyor mu; demo fixture üretimden ayrılmış mı?
2. Mapping departman/hizmet türüne göre çalışıyor mu?
3. Bir task-gol linki yetkiyle yönetiliyor mu?
4. Formül yokken ilerleme undefined/needs-configuration olarak gösteriliyor mu; uydurma yüzde görünmüyor mu?
5. Formül sağlanmışsa sınır değer, ağırlık ve rounding testleri var mı?
6. Progress snapshot geçmişi/audit tutuluyor mu?
7. Hedef izni olmayan kullanıcı hassas hedef detayını göremiyor mu?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
