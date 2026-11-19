# Prompt 4 - CRM ve Dinamik Sicil Alanları

## Ön koşul ve bağlam
CRM Faz 1 kapsamıdır. Müşteri sicilindeki temel alanlar ve müşteri tipleri açık müşteri girdisidir. Henüz sağlanmadıysa yalnız minimum teknik çekirdek ve test fixture kullan; nihai zorunlu alanları uydurma.

## Kullanılacak skill'ler
`$visfor-enterprise-ui-ux` (`skills/enterprise-ui-ux/SKILL.md`), `$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. Customer/Organization ve Contact modelini oluştur. Tek firmada birden fazla yetkili kişi ilişkisini destekleyecek şekilde tasarla; bu öneriyi müşteri teyidine açık tut.
2. Müşteri listeleme, arama, filtreleme, sayfalama ve create/edit/detail ekranlarını yap.
3. Firma detayını sekmeli düzenle: Genel, Yetkililer, Özel Alanlar, Evraklar, Projeler, Aktivite (sekme kapsamı teyide açık).
4. Custom Field Definitions ve Values için metadata-temelli EAV tasarımı uygula. Tip allowlist: text, textarea, integer/decimal, date, boolean, select, multi-select.
5. Admin'in alan adı, label, veri tipi, required, options, validation, display order, active, searchable/filterable metadata tanımlamasını sağla.
6. Değerleri typed/validatable şekilde sakla. Tip değişikliğinde veri dönüştürme/ret kararı gerekir; mevcut değerleri sessizce bozma.
7. Firma arşivleme, aktivite geçmişi ve audit entegrasyonu yap.
8. İzinleri müşteri ve özel alan seviyesinde uygula; hassas sicil alanlarının kimlere görüneceği desteklenebilir olsun.

## Beklenen çıktılar
Müşteri CRUD, Contact ilişkisi, müşteri detay sayfası, Custom Field builder/value UI, migrations, policies, audit ve testler.

## Sınırlar ve güvenlik kuralları
Customer alanları, müşteri türleri ve zorunlu alanlar müşteri tarafından sağlanmadan hardcode edilmesin. EAV modelini tüm modüllere yayma. Müşterileri kalıcı silme. Arama/filtrede yetkiyi atlama.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
