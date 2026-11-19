# Prompt 9 - Finans ve Nakit Akışı Yönetimi

## Ön koşul ve bağlam
SRS gelir/gider, P&L ve tahmini nakit dengesi/runway ister; resmi muhasebe/fatura aracı olmadığını açıkça belirtir. Hesap formülleri ve para birimi/ödeme davranışı müşteri girdisidir.

## Kullanılacak skill'ler
`$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-performance-engineering` (`skills/performance/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. FinancialEntry, FinancialCategory, CashForecast ve gerekiyorsa CashBalanceSnapshot modellerini oluştur.
2. Gelir/gider, tahmini/gerçekleşen, tarih, durum, para birimi, müşteri/proje/partner bağlantıları için model kur.
3. Para değerlerini decimal ve currency code ile tut; float kullanma.
4. Tahsilat/ödeme, kısmi ödeme, iptal/düzeltme ve onay akışı kararlarını `ADR-0005-finance.md` içinde netleştir. Cevap yoksa sadece CRUD/rapor iskeleti ve sentetik fixture yap.
5. P&L ve nakit akışını ayrı raporlar olarak tasarla; formüller müşteri örnek hesaplarıyla doğrulanmadan production hesaplamasını etkinleştirme.
6. Runway için kullanılan nakit, aylık burn, tahmin dönemi ve varsayımları açıkça sakla/göster.
7. Finans görüntüleme/ekleme/düzeltme/onay/export izinlerini ayrı uygula.
8. Finansal kritik değişiklikleri audit et; düzeltme geçmişini koru.
9. Dashboard finans özetlerini sadece finans izni olan kullanıcılara göster.

## Beklenen çıktılar
Finans CRUD, kategori yönetimi, P&L/nakit/runway UI iskeleti, hesaplama servisleri yalnız onaylı formüle bağlı, audit ve testler.

## Sınırlar ve güvenlik kuralları
Bu uygulama resmi muhasebe değildir. E-fatura/fatura kesme veya mevzuat beyanı ekleme. Nakit/tahakkuk muhasebe yaklaşımını, vergi ve döviz hesaplarını uydurma. Finansal kayıtları float ile saklama.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
