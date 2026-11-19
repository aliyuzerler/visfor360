# Prompt 5 - Evrak Deposu ve Süre Uyarıları

## Ön koşul ve bağlam
SRS müşteri profiline vergi levhası/kapasite raporu gibi evrak yüklemeyi ve eksik/süresi dolan evrak uyarısını ister. Evrak türleri, zorunluluk ve uyarı periyotları müşteri tarafından verilecek.

## Kullanılacak skill'ler
`$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-enterprise-ui-ux` (`skills/enterprise-ui-ux/SKILL.md`), `$visfor-performance-engineering` (`skills/performance/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. DocumentType, Document, DocumentVersion ve gerekiyorsa DocumentRequest modellerini oluştur.
2. Belgeyi Customer ile ilişkilendir; belge kategorisi, yükleyen, yüklenme zamanı, geçerlilik tarihi, durum ve storage key tut.
3. Dosyayı private storage'a yükle; public URL üretme. İndirme öncesi server-side customer ve role permission kontrolü yap.
4. Upload'da size, extension, MIME allowlist ve random file name uygula. Orijinal dosya adını sadece güvenli metadata olarak sakla.
5. Eksik belge ile süresi dolan belgeyi ayrı durumlar olarak göster.
6. Belge geçerlilik uyarıları için scheduler/queue tasarla. Uyarı periyodu müşteri onayına kadar konfigüre edilebilir olsun.
7. Belge değişim/sürüm işlemlerinde önceki sürümleri ve audit kayıtlarını koru.
8. Firma detayında evrak listesi, preview/download, upload ve status badge'leri yap.

## Beklenen çıktılar
Private file storage entegrasyonu, belge türü/kayıt modelleri, upload/download, geçerlilik durumu ve uyarı job'ı, testler.

## Sınırlar ve güvenlik kuralları
Dosya depolamayı DB blob olarak yapma. Dosyaları public web root'a koyma. Virüs tarama servisi yoksa varmış gibi gösterme; eklenmesi için opsiyon notu oluştur. Müşterinin belge saklama süresi/izinli dosya türü kararını varsayma.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
