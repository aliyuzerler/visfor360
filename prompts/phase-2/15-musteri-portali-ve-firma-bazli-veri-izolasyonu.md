# Prompt 15 - Müşteri Portalı ve Firma Bazlı Veri İzolasyonu

## Ön koşul ve bağlam
Portal kaynak SRS'de müşteri login, kendi projelerinin Kanban aşamalarını görme ve istenen eksik evrakı yükleme olarak tanımlanmıştır. Bölüm 2.2 Faz 2/3, teslimat tablosu Faz 2 der; müşteri kararı almadan canlı kapsamı sabitleme.

## Kullanılacak skill'ler
`$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-enterprise-ui-ux` (`skills/enterprise-ui-ux/SKILL.md`), `$visfor-accessibility-responsive` (`skills/accessibility/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. Customer portal user/membership ilişkisi oluştur; aynı firmadan çoklu kullanıcı konusu açık kararsa ona göre tasarla.
2. Portal için ayrılmış sade route/layout ve auth akışını kur.
3. Portal anasayfasında yalnız customer scope'taki projelerin gerekli aşama/özet bilgilerini göster.
4. İç kullanıcı tarafından istenen belge listesi ve durumunu göster; müşteri yalnız ilgili belge talebine dosya yükleyebilsin.
5. Belge yükleme sonrasında iç kullanıcı incelemesi/onayı gerekiyorsa müşteri kararıyla uygula.
6. Portal endpoint ve query'lerinde customer scope mandatory global policy/service ile uygula.
7. Hata yanıtlarında başka müşterinin varlığını bile ifşa etmeyen 404/403 davranışını tasarla.
8. Portal kullanıcılarının finans, internal comments, staff notes, fırsat, diğer müşteriler ve iç yönetim menülerine erişimini engelle.

## Beklenen çıktılar
Portal authentication/layout, my-projects view, document requests/upload, membership scope, file security, IDOR test suite ve müşteri kullanım kısa rehberi.

## Sınırlar ve güvenlik kuralları
Portal fazı müşteri kararı olmadan Faz 2 teslim edildi sayılmamalı. Portal kullanıcıları iç staff gibi yetkilendirilmemeli. API parametresinden gelen customer_id tek başına güvenilir kabul edilmez.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
