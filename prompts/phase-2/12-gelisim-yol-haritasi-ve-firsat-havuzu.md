# Prompt 12 - Gelişim Yol Haritası ve Fırsat Havuzu

## Ön koşul ve bağlam
Check-Up rule engine hazır. SRS tespit edilen hizmet ihtiyaçlarının doğrudan göreve değil Fırsat Havuzu'na düşmesini ister. İnsan incelemesi ve müşteri mutabakatı zorunludur.

## Kullanılacak skill'ler
`$visfor-enterprise-ui-ux` (`skills/enterprise-ui-ux/SKILL.md`), `$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. Check-Up sonuçlarından RoadmapItem ve Opportunity kayıtları üret.
2. Roadmap ekranında gelişim alanı, tespit gerekçesi, önerilen hizmet, öncelik ve öneri açıklaması göster.
3. Opportunity status akışını müşteriyle onaylanan statülerle kur (ör. review_pending, customer_discussion, approved, rejected, deferred yalnız örnek olabilir).
4. Fırsat listesi/detail ekranı; müşteri, hizmet, kaynak Check-Up oturumu, tespit nedeni, sorumlu, tahmini değer varsa ve tarihleri göster.
5. Müşteri mutabakat notu/kanıtı ve karar veren kullanıcıyı kaydet.
6. Onay/red/erteleme rollerini permission ile kontrol et.
7. Mükerrer fırsat kontrolünü müşteri kararına göre uygula.
8. Fırsat durumu ve karar değişikliklerini audit/history ile sakla.

## Beklenen çıktılar
Gelişim yol haritası, fırsat list/detail, karar/approval flow, history/audit, permission ve testler.

## Sınırlar ve güvenlik kuralları
Fırsatı otomatik projeye veya göreve dönüştürme. Müşteri mutabakatını boş bir checkbox gibi varsayılan kabul etme; kanıt/nota ilişkin iş kuralı müşteriyle netleştirilmeli. Tahmini satış değeri ancak müşteri isterse eklenir.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
