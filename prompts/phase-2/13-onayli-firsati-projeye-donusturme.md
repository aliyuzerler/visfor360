# Prompt 13 - Onaylı Fırsatı Projeye Dönüştürme

## Ön koşul ve bağlam
Fırsat Havuzu ve proje/pipeline modülleri hazır olmalı. SRS, yönetici/satış sorumlusu müşteriyle mutabık kaldıktan sonra tek tuşla ilgili departman Kanban panosuna iş aktarmayı ister.

## Kullanılacak skill'ler
`$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. Onaylı fırsat için `convertToProject/Work` use-case'i oluştur.
2. Conversion öncesi approved status, customer agreement data, approver permission, customer/service mapping ve gerekli proje alanlarını doğrula.
3. Fırsatın hizmet türüne karşılık gelen departman ve pipeline'ı seç.
4. Project/Work oluşturma, initial stage, opportunity_conversion relation, opportunity status update ve audit'i tek DB transaction içinde uygula.
5. Idempotency key/unique conversion relation ile çift tıklama, retry veya aynı API isteğinin duplicate proje üretmesini engelle.
6. Başarısız dönüşümde fırsat onaysız/yarım durumda kalmasın; transaction rollback yap.
7. Oluşan proje üzerinden kaynak fırsat ve Check-Up sonucuna geri erişim sağla.

## Beklenen çıktılar
Onaylı fırsattan tekil proje/iş üretme use-case'i, idempotency, transaction, history/audit, UI action ve testler.

## Sınırlar ve güvenlik kuralları
Onaysız fırsat dönüşemez. Fırsat onayı müşteri mutabakatının yerine geçmez; mutabakat kaydı kontrol edilir. Aynı fırsat birden fazla iş üretecekse bu ayrı müşteri kararı ve relation modeli gerektirir.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
