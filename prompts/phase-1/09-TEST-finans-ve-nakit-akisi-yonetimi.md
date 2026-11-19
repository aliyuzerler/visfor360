# Test Prompt 9 - Finans ve Nakit Akışı Yönetimi Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Finans kaydı decimal precision/rounding ile doğru saklanıyor mu?
2. Tahmini ve gerçekleşen kayıtlar filtre/raporda ayrı mı?
3. P&L ve nakit akışı aynı metrikmiş gibi sunulmuyor mu?
4. Formül onayı yoksa gerçek finansal total/runway hesaplanmıyor mu?
5. Finans izni olmayan rol tutar, export ve detaylara erişemiyor mu?
6. Düzeltme/iptal old-new audit üretiyor mu?
7. Eşzamanlı güncellemede çift kayıt veya kayıp update var mı?
8. Tarih aralığı, para birimi ve kısmi tahsilat kararları testli mi?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
