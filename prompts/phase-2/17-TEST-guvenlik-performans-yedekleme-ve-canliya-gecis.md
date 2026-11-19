# Test Prompt 17 - Güvenlik Performans Yedekleme ve Canlıya Geçiş Doğrulaması

Aşağıdaki kontrolleri yalnızca kodu okuyarak değil, mümkün olan yerlerde test komutlarını çalıştırarak doğrula. Gerçek sonuçları yaz; çalıştırılmayan testi geçmiş gibi gösterme.

1. Critical/High güvenlik bulgusu kaldı mı? Kaldıysa release BLOCKED.
2. Portal IDOR ve file authorization E2E testleri geçti mi?
3. Secret scan ve dependency audit sonucu nedir?
4. Backup restore gerçekten test edildi mi; DB ve dosya eşleşmesi doğrulandı mı?
5. p95/p99 ve DB/queue/bundle metrikleri raporlandı mı?
6. Worker/scheduler/health checks çalışıyor mu?
7. Rollback runbook staging'de prova edildi mi?
8. UAT kabul kriterleri müşteri imzasına bağlandı mı?
9. `docs/DEFINITION_OF_DONE.md` tüm maddeleri tamam mı?

## Sonuç raporu
- PASS / FAIL / BLOCKED olarak her maddeyi işaretle.
- FAIL maddeleri için kök neden, dosya ve düzeltme önerisini ver.
- Testleri yeniden çalıştır ve son sonucu ekle.
- Critical/High güvenlik açığı, kayıp veri veya ana iş akışında hata varsa sonraki prompta geçme.
