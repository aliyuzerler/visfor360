# Fazlar ve Bağımlılıklar

## Geliştirme kapısı
P0: tek Visfor kurulumu mu/SaaS mı; rol-yetki matrisi; departman ve hizmet kataloğu; hizmet bazlı Kanban aşamaları; müşteri zorunlu alan ve evrak matrisi; 12 hedef ve ölçüm formülleri; finans P&L/runway örneği; hosting/veri işletimi. Check-Up soru/kural seti Faz 2 öncesi alınmalıdır.

## Faz 1 bağımlılıkları
- 01 temel repo -> tüm modüller.
- 02 tasarım sistemi/app shell -> tüm UI.
- 03 kimlik/RBAC/audit -> tüm modüller.
- 04 CRM/custom fields -> evrak, proje, finans, portal ve Check-Up.
- 05 evrak deposu -> CRM ve portal.
- 06 departman/hizmet/pipeline/proje -> görev ve fırsat dönüşümü.
- 07 görev -> OKR ve dashboard.
- 08 OKR + 09 finans -> 10 dashboard/rapor ve Faz 1 UAT.

## Faz 2 bağımlılıkları
- 11 Check-Up -> 12 Fırsat -> 13 onaylı dönüşüm.
- 14 Partner modülü proje ve finans ile ilişkilidir.
- 15 Portal, CRM/proje/belge/RBAC altyapısına bağlıdır.
- 16 API/Webhook, domain event, audit ve güvenli kimlik doğrulamaya bağlıdır.
- 17 release hardening her fazda tekrarlanır.

## Faz kapatma
Kod review, migration, unit/feature/E2E testleri, güvenlik regresyonu, performans baseline, UAT, dokümantasyon ve bilinen sorun listesi tamamlanır.
