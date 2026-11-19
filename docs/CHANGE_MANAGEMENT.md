# Kapsam ve Değişiklik Yönetimi

Bu proje şartnamesi modüler olmakla birlikte bazı iş kuralları keşif öncesi belirsizdir. Sonradan gelen yeni talebin mevcut kapsamı sessizce büyütmemesi için aşağıdaki akış kullanılır.

1. Talep ID, talep sahibi, iş gerekçesi ve istenen davranış yazılır.
2. İlgili modül, ekran, veri modeli, izin, entegrasyon, test ve deployment etkisi analiz edilir.
3. Talep şu şekilde sınıflandırılır: mevcut kapsam içindeki hata; mevcut kapsamın açıklığa kavuşturulması; yeni özellik; üçüncü taraf entegrasyonu; operasyon/lisans maliyeti.
4. Yeni özellikte efor, takvim, risk ve ücret etkisi belirtilir.
5. Müşteri yazılı onay vermeden yeni özellik geliştirme promptlarına eklenmez.
6. Onaylanan talep `docs/decisions/` altındaki ADR/decision log ve kabul kriterlerine eklenir.
7. Eski kabul kriterleri değişiyorsa sürüm ve tarih korunur.

## Değişiklik isteği şablonu
- ID / başlık:
- Talep sahibi / tarih:
- Problem ve iş değeri:
- Beklenen davranış:
- Etkilenen modüller/veriler/roller:
- Güvenlik ve KVKK etkisi:
- Entegrasyon / hosting / lisans etkisi:
- Efor ve takvim etkisi:
- Fiyat etkisi:
- Kabul kriterleri:
- Müşteri onayı:
