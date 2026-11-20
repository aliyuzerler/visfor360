# ADR-0012: Onaylı fırsat → proje dönüşümü

## Bağlam
SRS, mutabık kalınan fırsatın tek tuşla departman Kanban panosuna aktarılmasını ister. Hizmet anahtarı → ServiceType (departman/pipeline) eşlemesi ve çoklu iş üretimi belirsizdir.

## Karar
- Dönüşüm ön koşulları: status=approved + müşteri mutabakat notu dolu (onay mutabakatın yerine geçmez) + henüz dönmemiş + service_key eşleşen aktif ServiceType.
- Idempotency: opportunities.converted_project_id UNIQUE — çift tıklama/retry aynı isteği duplicate proje ÜRETMEZ, mevcut projeyi döner.
- Şema 1:1 (bir fırsat → bir iş). Aynı fırsattan çoklu iş üretimi ayrı müşteri kararıdır; gelirse relation modeli genişletilir.
- Mapping: opportunity.service_key → ServiceType.key (aktif). Eşleşme yoksa dönüşüm reddedilir (müşteri hizmet katalogunu kalıcılaştırdığında çalışır).
- Tüm işlem tek DB transaction: Project + initial ProjectStageHistory + opportunity update + audit. Hata → rollback; fırsat yarım kalmaz.
- Kaynak geri erişimi: proje detayında kaynak fırsat gösterilir; fırsat → converted_project.

## Sonuç
Deterministik, idempotent, atomik dönüşüm; üretimde service_key eşlemesi müşteri hizmet kataloğuyla kalıcılaşır.
