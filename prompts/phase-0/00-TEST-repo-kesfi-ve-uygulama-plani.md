# Test Prompt 0 - Repo Keşfi ve Uygulama Planı Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Analizdeki tüm ürün modüllerinin planda karşılığı var mı?
2. Faz 1/Faz 2 sınırı ve portal Faz 2/3 belirsizliği korunmuş mu?
3. P0 kararlar ile etkiledikleri modüller gösterilmiş mi?
4. Repo incelemesi sırasında izinsiz destructive değişiklik yapılmamış mı?
5. Stack uyumluluğu hosting/runtime bilgisiyle doğrulanmış mı, yoksa varsayım mı olduğu yazılmış mı?
6. Her geliştirme fazına test promptu atanmış mı?
7. Kaynakta olmayan özellikler öneri olarak etiketlenmiş mi?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
