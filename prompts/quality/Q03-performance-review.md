# Quality Prompt Q03 - Performans Denetimi

## Kullanılacak skill'ler
`$visfor-performance-engineering` (`skills/performance/SKILL.md`), `$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Görev
Gerçekçi sentetik veri seti oluştur veya mevcut test fixture'larını kullan. Müşterinin onayladığı yük hedefleri varsa uygula; yoksa hedef icat etmeden baseline ölç. Büyük listelerde pagination/filter/sort, CRM detail eager loading, Kanban query, dashboard aggregation, finans raporları, queue süreleri, file upload ve frontend bundle ölç. N+1, slow query, eksik index, gereksiz re-render, cache invalidation ve queue retry sorunlarını tespit et. Önce ölçüm raporu hazırla; geniş kapsamlı optimizasyonu onaysız yapma.

## Çıktı
`docs/PERFORMANCE_REVIEW.md`: test dataset/ortam, p50/p95/p99, query count, queue metrikleri, bundle size, bulgu ve düzeltme planı.
