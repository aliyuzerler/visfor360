# Prompt 7 - Görev ve Ekip İş Yönetimi

## Ön koşul ve bağlam
SRS departman bazlı Kanban/görev yönetimi ve görevlerin hedeflerle ilişkilendirilmesini ister. Yorum, alt görev, efor ve tekrar eden görev gibi ilaveler müşteri teyidine bağlıdır.

## Kullanılacak skill'ler
`$visfor-enterprise-ui-ux` (`skills/enterprise-ui-ux/SKILL.md`), `$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. Task, TaskAssignment, TaskHistory ve gerekiyorsa TaskComment/TaskAttachment modellerini kur.
2. Görevi müşteri, proje, hizmet türü ve departmanla bağla.
3. Başlık/açıklama/sorumlu/durum/öncelik/başlangıç/son tarih alanlarını konfigüre edilebilir zorunlulukla uygula.
4. Tek veya çoklu atanan kararını müşteri cevabına göre uygula; cevap yoksa modelin genişletilebilirliğini koru, varsayımı işaretle.
5. Benim görevlerim, ekip görevleri, görev detay ve Kanban/list view ekranlarını yap.
6. Durum değişikliği, atama ve tarih güncellemesini history + audit'e yaz.
7. Gecikmiş görevleri hesapla; bildirim kanalını yalnızca onaylanmış kanallarla etkinleştir.
8. Yorum/ek/alt görev gibi ek özellikleri sadece müşteri onayı ve kapsam kararıyla aç.

## Beklenen çıktılar
Task CRUD, assignment, history, list/Kanban, permission policy, gecikmiş görev görünümü, testler.

## Sınırlar ve güvenlik kuralları
Görev kapatıldığında OKR ilerlemesini henüz formül yoksa otomatik artırma. Efor ve tekrar eden görevleri varsayılan dahil etme. Task access'i customer/project scope ile sınırla.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
