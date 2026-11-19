# Prompt 14 - Çözüm Ortağı Komisyon ve Hakediş Yönetimi

## Ön koşul ve bağlam
SRS dış paydaş/çözüm ortaklarının (mimarlık, hukuk vb.) yürüttüğü işler ile yönlendirme, komisyon ve hakediş ödemelerinin takibini ister. Komisyon matrahı/oranı ve ödeme kuralları müşteri örnekleriyle netleşmelidir.

## Kullanılacak skill'ler
`$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`), `$visfor-enterprise-ui-ux` (`skills/enterprise-ui-ux/SKILL.md`)

## Geliştirme görevleri
1. Partner, PartnerAgreement, Referral/PartnerWork, Commission ve Payout modellerini oluştur.
2. Partner list/detail, iletişim, uzmanlık türü ve aktif/pasif kayıt ekranları yap.
3. Partnerin aldığı iş/proje ile müşteri ve ilgili hizmeti ilişkilendir.
4. Sabit tutar/yüzde gibi formülleri yalnız müşteri onaylı agreement kuralıyla etkinleştir.
5. Komisyon matrahı, oran, hesap dönemi, onay, ödeme tarihi ve durumunu izlenebilir tut.
6. Hakediş/ödeme oluşturma ve düzeltme auditli olmalı; finans modülüyle ilişkili rapor sun.
7. Partner login/portal istenmediği için partneri iç kullanıcı kabul etme; dış erişim ayrı karardır.

## Beklenen çıktılar
Partner CRUD, partner iş/referral kayıtları, agreement/commission/payout, finans bağlantısı, audit ve testler.

## Sınırlar ve güvenlik kuralları
Komisyon yüzdesi, KDV, matrah veya ödeme vadesi uydurma. Partner portalı veya otomatik banka ödeme entegrasyonu ekleme. Finans kayıtlarıyla çift kayıt yaratma.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
