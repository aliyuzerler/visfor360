# Prompt 8 - Vizyon ve 12 Ana Hedef OKR Modülü

## Ön koşul ve bağlam
SRS 5 yıllık vizyon ve önceden belirlenmiş 12 ana hedef ile görevlerin otomatik/varsayılan hedef eşleştirmesini ister. Ancak hedef isimleri, tanımları ve ilerleme formülü kaynak PDF'de yoktur.

## Kullanılacak skill'ler
`$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-enterprise-ui-ux` (`skills/enterprise-ui-ux/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. Vision, StrategicGoal, optional KeyResult, GoalTaskLink ve ProgressSnapshot modellerini tasarla.
2. Yönetim ekranlarında 5 yıllık vizyon ve 12 hedefi yönet; gerçek 12 hedef listesi müşteri tarafından gelmediyse yalnız placeholder/sentetik veriyle geliştirme/test yap.
3. Hedef alanlarında description, owner, period, baseline, target, unit ve calculation method gibi metadata önerilerini müşteri teyidine aç.
4. Departman/service type -> default goal mapping yönetilebilir kuralını oluştur.
5. Görev oluşturulurken ilgili hedef varsayılan atanabilir; kullanıcı düzeltme yetkisi müşteri kararına bağlıdır.
6. İlerleme motorunu strategy interface ile tasarla; sadece onaylı formül ve test fixture verilince hesaplamayı etkinleştir.
7. Bir görevin birden fazla hedefe katkısı ve ağırlıklandırma kararı gelmeden % üretme.
8. Her hesap snapshot'ı, hesap zamanı, kaynak görevler ve formül sürümüyle izlenebilir olsun.

## Beklenen çıktılar
Vizyon/hedef CRUD, hedef detay/ilerleme görünümü, task-goal mapping, calculation abstraction, snapshot/audit ve testler.

## Sınırlar ve güvenlik kuralları
12 hedef adını veya formülü üretme. `tamamlanan görev sayısı / toplam görev` gibi bir varsayımı gerçek ilerleme formülü olarak kullanma. Müşteri onayı olmadan hedef skorunu raporlama.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
