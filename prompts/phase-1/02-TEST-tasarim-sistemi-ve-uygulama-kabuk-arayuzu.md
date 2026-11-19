# Test Prompt 2 - Tasarım Sistemi ve Uygulama Kabuk Arayüzü Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Tokenlar merkezi mi; component içinde dağınık raw color var mı?
2. Menü ve shell tüm ekranlarda tutarlı mı?
3. 1440px, 1024px, 768px ve 390px genişliklerde taşma/çakışma var mı?
4. Tüm form kontrollerinde label, hint, validation ve focus görünür mü?
5. Klavye ile sidebar, dropdown, dialog ve tabs kullanılabiliyor mu?
6. Empty/loading/error/disabled/forbidden durumları için bileşen var mı?
7. Sahte KPI/çalışmayan buton mevcut mu?
8. Design-system dokümanı kodla tutarlı mı?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
