# Test Prompt 10 - Dashboard Raporlar ve Faz 1 Uçtan Uca Birleştirme Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Her dashboard KPI gerçek backend sorgusuna bağlı mı?
2. Veri yokken sayısal sahte değer gösteriliyor mu?
3. Farklı rollerin dashboard içerikleri doğru mu?
4. Müşteri/departman filtreleri rapor ve export'ta korunuyor mu?
5. Büyük listeler sayfalı mı; N+1 ve slow query kontrol edildi mi?
6. E2E CRM -> document -> project -> task -> goal flow geçiyor mu?
7. Finance permission olmadan finans widget/API yanıtı alınamıyor mu?
8. Faz 1 Definition of Done'ın tüm maddeleri tamam mı?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
