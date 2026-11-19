---
name: visfor-enterprise-ui-ux
description: Design and review role-aware enterprise workflows, CRM tables, forms, Kanban, dashboards, client portal screens, responsive states and usability.
---

# Skill: Enterprise UI/UX

## Amaç
CRM, proje, görev, finans ve müşteri portalında düşük öğrenme maliyeti, yüksek veri görünürlüğü ve tutarlı iş akışı sağla.

## İlkeler
- Her ekranın bir ana kullanıcı görevi ve primary action'ı açık olmalı.
- Dashboard dekoratif KPI koleksiyonu değil, gerçek aksiyon ve risk özeti olmalı.
- Uzun formları mantıklı bölümlere ayır; validation, kaydetme, iptal ve unsaved-changes davranışı tasarla.
- Liste ekranlarında server-side pagination/filter/sort.
- Detail ekranında bağlantılı kayıtlar ve aktivite geçmişi görünür.
- Empty state açıklayıcı ve izin varsa ilk aksiyona yönlendirici olmalı.
- Error state ne olduğunu ve güvenli sonraki adımı anlatmalı; stack trace yok.
- Loading, empty, error, success, disabled ve permission-denied durumlarını tasarla.

## Akış
1. Persona/rol ve kullanıcı görevi.
2. Bilgi mimarisi ve ana akış.
3. Wireframe düşüncesi/yerleşim.
4. Design system bileşenlerini kullan.
5. Tüm UI durumlarını tanımla.
6. Desktop/tablet/mobile kontrolü.
7. Klavye ve ekran okuyucu kontrolü.

## Bu ürüne özel
- Firma detayında Genel, Yetkililer, Özel Alanlar, Evraklar, Projeler, Aktivite sekmeleri başlangıç önerisidir.
- Kanban stage değişimi backend permission, transition rule ve audit çalıştırmalıdır.
- Finans ekranında tahmini ve gerçekleşen açıkça ayrılır; para birimi ve dönem görünür.
- OKR'de formülü açıklanamayan yüzde gösterilmez.
- Check-Up'ta cevap, kural sonucu ve hizmet önerisinin gerekçesi izlenebilir.
- Fırsat ekranında müşteri mutabakatı ve insan onayı temel adımdır.
- Portal sade olmalı ve sadece müşterinin yetkili olduğu kayıtları göstermelidir.

## Anti-pattern
Sahte KPI, çalışmayan buton, her sayfada aynı kart tasarımı, sadece renkle status, modal zinciri, permission hatasını sessiz bırakma.
