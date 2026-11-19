# Test Prompt 4 - CRM ve Dinamik Sicil Alanları Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Müşteri create/edit/view/archive akışları yetki kurallarına uyuyor mu?
2. Custom Field definition create/update/deactivate işlemi auditli mi?
3. Her veri tipi için geçerli/geçersiz değer testleri var mı?
4. Zorunlu alan validation hem frontend hem backend çalışıyor mu?
5. Alan tipi değişince mevcut veri güvenle korunuyor mu?
6. Arama/filter/pagination query bazında çalışıyor mu; tüm kayıtları belleğe almıyor mu?
7. Başka müşterinin ID'si ile detail endpoint erişimi engelleniyor mu?
8. Müşteri arşivlenince ilişkili proje ve audit geçmişi kaybolmuyor mu?
9. Büyük liste testinde N+1 var mı?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
