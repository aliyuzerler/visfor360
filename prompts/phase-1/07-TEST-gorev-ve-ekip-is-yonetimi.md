# Test Prompt 7 - Görev ve Ekip İş Yönetimi Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Kullanıcı sadece yetkili olduğu görevleri görebiliyor/değiştirebiliyor mu?
2. Atama ve durum değişiklikleri geçmişe yazılıyor mu?
3. Tarih sınırı ve timezone doğru mu?
4. Geciken görev hesabı tamamlanmış/iptal görevleri hariç tutuyor mu?
5. Çoklu atama kararı verilmişse her atanan kullanıcı için izin/notification doğru mu?
6. Task attachment varsa file security testleri var mı?
7. UI'da empty/loading/error ve permission-denied state var mı?
8. Dashboard/task count gerçek query'lerden mi geliyor?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
