# Prompt 10 - Dashboard Raporlar ve Faz 1 Uçtan Uca Birleştirme

## Ön koşul ve bağlam
Faz 1 modülleri ayrı ayrı geliştirildi. Şimdi genel bakış, temel raporlar ve modüller arası akışlar tamamlanır. KPI listesi müşterinin önceliklerine göre sonlandırılmalıdır.

## Kullanılacak skill'ler
`$visfor-enterprise-ui-ux` (`skills/enterprise-ui-ux/SKILL.md`), `$visfor-performance-engineering` (`skills/performance/SKILL.md`), `$visfor-accessibility-responsive` (`skills/accessibility/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. Dashboard'u role göre kur: bekleyen onay, geciken işler, yaklaşan evrak, proje/iş durumları ve finans özeti (finans permission varsa).
2. KPI ve chart'ları yalnız gerçek aggregate sorgularından besle; veri yoksa empty state.
3. Müşteri/proje/departman/görev/hedef/finans temel raporlarında tarih/filtre/sıralama ve yetki kapsamı uygula.
4. Büyük export varsa queue ve audit; export permission gerektir.
5. CRM -> evrak -> proje -> görev -> hedef ve finans akışlarını entegre et.
6. Faz 1 UAT senaryolarını `docs/TEST_STRATEGY.md` ve `templates/test-case.md` ile bağla.
7. Faz 1 bilinen sınırlama, eğitim ve kullanıcı kılavuzu taslağı hazırla.

## Beklenen çıktılar
Role-aware dashboard, temel rapor ekranları, aggregate query/service, UAT senaryoları, Faz 1 release notes.

## Sınırlar ve güvenlik kuralları
Sahte KPI veya statik grafik üretme. Finans verisini yetkisiz rollere cache üzerinden sızdırma. Export kapsamı ve müşteri veri scope'u aynen korunmalı.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
