# Prompt 0 - Repo Keşfi ve Uygulama Planı

## Ön koşul ve bağlam
Önce repo dosya ağacını ve analiz PDF/SRS kaynaklarını incele. Bu prompt keşif ve plan içindir. Repo yeni olabilir veya mevcut uygulama içerebilir.

## Kullanılacak skill'ler
`$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`)

## Geliştirme görevleri
1. Framework, runtime, package manager, DB, auth, test, CI, deploy ve mevcut kod kalitesini incele.
2. Ürün analizindeki tüm modülleri Faz 1/Faz 2 olarak sınıflandır; açık kararları ve bağımlılıkları listele.
3. Önerilen Laravel + React/TypeScript/Inertia + PostgreSQL stack'inin hosting ve mevcut repo ile uyumunu değerlendir. Uyuşmazlık varsa alternatif ve gerekçe sun.
4. P0 soruları `docs/OPEN_QUESTIONS.md` ile kontrol et; eksikleri ekle.
5. Dosya/modül düzeyinde implementasyon planı, test stratejisi, migration ve deploy riskleri oluştur.
6. Mevcut kod varsa korunacak parçaları, refactor ihtiyacını ve silinmemesi gerekenleri açıkla.

## Beklenen çıktılar
`docs/REPO_ASSESSMENT.md`, `docs/IMPLEMENTATION_PLAN.md`, mimari uygunluk değerlendirmesi ve gerekiyorsa ADR taslak güncellemesi.

## Sınırlar ve güvenlik kuralları
Bu promptta büyük kod yazma, framework değiştirme, paketleri yükseltme veya migration çalıştırma. 12 ana hedefi, Check-Up kurallarını, finans hesaplarını, pipeline aşamalarını ve izin matrisini uydurma. Bilinmeyenleri soru olarak bırak.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
