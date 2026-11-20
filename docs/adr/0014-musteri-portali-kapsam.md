# ADR-0014: Müşteri Portalı kapsam ve canlıya alma kararı

## Bağlam
SRS portalı müşteri login + proje Kanban görüntüleme + eksik evrak yükleme olarak tanımlar; ancak portalın Faz 2'de canlıya alınması müşteri kararıdır.

## Karar
- Portal kullanıcıları iç staff'tan AYRI tablodadır (portal_users + ayrı guard): iç modüllerin Gate'leri bu modeli tanımaz; finans/fırsat/iç not verisi portala hiç üretilmez.
- Çoklu kullanıcı: portal_memberships M:N hazır (aynı firmadan çoklu kullanıcı müşteri kararına açık); üyelik is_active=false → anında erişim keser.
- Belge talebi: iç kullanıcı portal_document_requests açar; müşteri yalnız kendi firmasının açık talebine yükler (IDOR → 404, varlık ifşası yok). Yükleme sonrası iç inceleme/onay akışı müşteri kararıyla eklenir.
- Canlıya alma: portal özellikleri geliştirildi ama müşteri kararı olmadan canlıya açılmaz (Prompt 17'de feature flag ile kontrol edilir).

## Sonuç
Firma bazlı izolasyon testleri (IDOR suite) PASS; portal canlıya alınması müşteri kararı bekler.
