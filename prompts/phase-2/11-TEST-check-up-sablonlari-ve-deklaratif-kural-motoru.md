# Test Prompt 11 - Check-Up Şablonları ve Deklaratif Kural Motoru Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Müşterinin her örnek input-output senaryosu beklenen sonucu üretiyor mu?
2. AND/OR, sınır eşik, null/missing, invalid answer ve priority testleri var mı?
3. Template/rule version değiştirilince eski oturum sonucu değişmeden kalıyor mu?
4. Aynı cevap + aynı sürüm tekrar çalıştırıldığında sonuç aynı mı?
5. Rule engine kullanıcı girdisini kod/SQL olarak çalıştırmıyor mu?
6. Sonuç hangi soru/koşul nedeniyle oluştuğunu açıklıyor mu?
7. Check-Up tamamlanınca fırsat önerisi oluşuyor ama proje/task oluşmuyor mu?
8. Yetkisiz kullanıcı Check-Up sonuçlarını göremiyor mu?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
