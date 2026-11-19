# ADR-0007: Çok departmanlı hizmet modeli (karar bekleniyor)

## Bağlam
Bir projenin birden fazla departmana hizmet vermesi durumu SRS'de netleşmedi.

## Karar
Şimdilik projects.department_id tekil; çok departmanlı ihtiyaç onaylanırsa M:N ara tablo (project_departments) eklenir. ServiceType.department_id da tekil.

## Sonuç
Müşteri kararı beklenir; şema değişikliği kolaydır.
