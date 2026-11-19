# ZCode Prompt İndeksi

Geliştirme ve test dosyaları numara bazında eşleştirilmiştir. Her geliştirme promptundan hemen sonra aynı numaralı `TEST` promptu çalıştırılmalıdır.

## Hazırlık

| No | Geliştirme promptu | Test promptu |
|---|---|---|
| 00 | [Repo keşfi ve uygulama planı](prompts/phase-0/00-repo-kesfi-ve-uygulama-plani.md) | [Test 00](prompts/phase-0/00-TEST-repo-kesfi-ve-uygulama-plani.md) |
| 01 | [Proje iskeleti, ortamlar ve CI](prompts/phase-0/01-proje-iskeleti-ortamlar-ve-ci.md) | [Test 01](prompts/phase-0/01-TEST-proje-iskeleti-ortamlar-ve-ci.md) |

## Faz 1 - Çekirdek sistem

| No | Geliştirme promptu | Test promptu |
|---|---|---|
| 02 | [Tasarım sistemi ve uygulama kabuğu](prompts/phase-1/02-tasarim-sistemi-ve-uygulama-kabuk-arayuzu.md) | [Test 02](prompts/phase-1/02-TEST-tasarim-sistemi-ve-uygulama-kabuk-arayuzu.md) |
| 03 | [Kimlik, rol, yetki ve audit](prompts/phase-1/03-kimlik-rol-yetki-ve-audit-altyapisi.md) | [Test 03](prompts/phase-1/03-TEST-kimlik-rol-yetki-ve-audit-altyapisi.md) |
| 04 | [CRM ve dinamik sicil alanları](prompts/phase-1/04-crm-ve-dinamik-sicil-alanlari.md) | [Test 04](prompts/phase-1/04-TEST-crm-ve-dinamik-sicil-alanlari.md) |
| 05 | [Evrak deposu ve süre uyarıları](prompts/phase-1/05-evrak-deposu-ve-sure-uyarilari.md) | [Test 05](prompts/phase-1/05-TEST-evrak-deposu-ve-sure-uyarilari.md) |
| 06 | [Departman, hizmet, pipeline ve proje](prompts/phase-1/06-departman-hizmet-pipeline-ve-proje-yonetimi.md) | [Test 06](prompts/phase-1/06-TEST-departman-hizmet-pipeline-ve-proje-yonetimi.md) |
| 07 | [Görev ve ekip iş yönetimi](prompts/phase-1/07-gorev-ve-ekip-is-yonetimi.md) | [Test 07](prompts/phase-1/07-TEST-gorev-ve-ekip-is-yonetimi.md) |
| 08 | [Vizyon ve 12 ana hedef / OKR](prompts/phase-1/08-vizyon-ve-12-ana-hedef-okr-modulu.md) | [Test 08](prompts/phase-1/08-TEST-vizyon-ve-12-ana-hedef-okr-modulu.md) |
| 09 | [Finans ve nakit akışı](prompts/phase-1/09-finans-ve-nakit-akisi-yonetimi.md) | [Test 09](prompts/phase-1/09-TEST-finans-ve-nakit-akisi-yonetimi.md) |
| 10 | [Dashboard, raporlar ve Faz 1 birleştirme](prompts/phase-1/10-dashboard-raporlar-ve-faz-1-uctan-uca-birlestirme.md) | [Test 10](prompts/phase-1/10-TEST-dashboard-raporlar-ve-faz-1-uctan-uca-birlestirme.md) |

## Faz 2 - Gelişmiş otomasyon

| No | Geliştirme promptu | Test promptu |
|---|---|---|
| 11 | [Check-Up ve deklaratif kural motoru](prompts/phase-2/11-check-up-sablonlari-ve-deklaratif-kural-motoru.md) | [Test 11](prompts/phase-2/11-TEST-check-up-sablonlari-ve-deklaratif-kural-motoru.md) |
| 12 | [Gelişim yol haritası ve fırsat havuzu](prompts/phase-2/12-gelisim-yol-haritasi-ve-firsat-havuzu.md) | [Test 12](prompts/phase-2/12-TEST-gelisim-yol-haritasi-ve-firsat-havuzu.md) |
| 13 | [Onaylı fırsatı projeye dönüştürme](prompts/phase-2/13-onayli-firsati-projeye-donusturme.md) | [Test 13](prompts/phase-2/13-TEST-onayli-firsati-projeye-donusturme.md) |
| 14 | [Çözüm ortağı, komisyon ve hakediş](prompts/phase-2/14-cozum-ortagi-komisyon-ve-hakedis-yonetimi.md) | [Test 14](prompts/phase-2/14-TEST-cozum-ortagi-komisyon-ve-hakedis-yonetimi.md) |
| 15 | [Müşteri portalı ve firma veri izolasyonu](prompts/phase-2/15-musteri-portali-ve-firma-bazli-veri-izolasyonu.md) | [Test 15](prompts/phase-2/15-TEST-musteri-portali-ve-firma-bazli-veri-izolasyonu.md) |
| 16 | [REST API ve Webhook](prompts/phase-2/16-rest-api-ve-webhook-altyapisi.md) | [Test 16](prompts/phase-2/16-TEST-rest-api-ve-webhook-altyapisi.md) |
| 17 | [Güvenlik, performans, yedekleme ve canlıya geçiş](prompts/phase-2/17-guvenlik-performans-yedekleme-ve-canliya-gecis.md) | [Test 17](prompts/phase-2/17-TEST-guvenlik-performans-yedekleme-ve-canliya-gecis.md) |

## Tekrarlanan kalite promptları

| Konu | Review promptu | Test promptu |
|---|---|---|
| UI/UX ve tasarım | [Q01](prompts/quality/Q01-design-review.md) | [Q01 test](prompts/quality/Q01-design-review-test.md) |
| Güvenlik | [Q02](prompts/quality/Q02-security-review.md) | [Q02 test](prompts/quality/Q02-security-review-test.md) |
| Performans | [Q03](prompts/quality/Q03-performance-review.md) | [Q03 test](prompts/quality/Q03-performance-review-test.md) |
| Regresyon | [Q04](prompts/quality/Q04-regression-review.md) | [Q04 test](prompts/quality/Q04-regression-review-test.md) |

## Skill kataloğu

- `skills/brand-design-system/SKILL.md`
- `skills/enterprise-ui-ux/SKILL.md`
- `skills/security/SKILL.md`
- `skills/performance/SKILL.md`
- `skills/architecture/SKILL.md`
- `skills/testing/SKILL.md`
- `skills/accessibility/SKILL.md`
