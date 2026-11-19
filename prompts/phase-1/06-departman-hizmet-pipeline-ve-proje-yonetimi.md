# Prompt 6 - Departman Hizmet Pipeline ve Proje Yönetimi

## Ön koşul ve bağlam
SRS her hizmet kalemine özel farklı Kanban pipeline ister. Departman, hizmet kataloğu, aşama adları ve geçiş kuralları müşteri girdisidir. Bu veri gelmediyse yapılandırılabilir altyapıyı sentetik fixture ile test et.

## Kullanılacak skill'ler
`$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-enterprise-ui-ux` (`skills/enterprise-ui-ux/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. Department, ServiceType, Pipeline, PipelineStage, Project ve ProjectStageHistory domain nesnelerini oluştur.
2. Hizmet türünün seçilen pipeline şablonuna bağlanmasını sağla.
3. Pipeline stage'leri sıralanabilir, active/inactive ve terminal flag destekli tasarla.
4. Müşterinin hizmet başına pipeline şablonu tanımlayabileceği admin ekranı yap; onaylanmamış aşamaları örnek olarak etiketle.
5. Proje create/edit/list/detail ve service/dept/customer association akışlarını kur.
6. Kanban panosunda stage bazlı kartlar, filtre, arama, sorumlu ve tarih görünümü yap.
7. Stage geçişinde permission, varsa transition rule ve zorunlu alan/evrak validation çalıştır.
8. Her geçişte actor, old stage, new stage, timestamp ve audit history kaydet.
9. Bir projenin çok departmanlı hizmete bağlı olması durumunu müşteri kararı bekleyen model/ADR olarak işaretle.

## Beklenen çıktılar
Department/service CRUD; pipeline designer; project CRUD/detail; per-service Kanban; stage history/audit; testler.

## Sınırlar ve güvenlik kuralları
SRS dışından örnek Kanban aşamalarını gerçek kabul etme. Tüm hizmetler için tek Kanban kullanma. Drag/drop ile backend validation'ı atlama. Stage change atomik ve auditli olmalı.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
