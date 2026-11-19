---
name: visfor-performance-engineering
description: Measure and improve database, API, queue, dashboard, file and frontend performance using baselines, profiling, pagination and safe caching.
---

# Skill: Performance Engineering

## Ölçüm
SRS eşzamanlı kullanıcı veya yanıt SLA'sı vermiyor. Hedef uydurma. Hosting ve veri hacmi alındıktan sonra baseline/benchmark kur.

## Backend/DB
- Büyük listelerde server-side pagination/filter/sort.
- N+1 ve slow query taraması, uygun indeks.
- Dashboard aggregation/caching kontrollü; cache invalidation net.
- Uzun rapor, dosya ve webhook işini queue'ya al.
- Queue retries idempotent olmalı.
- Transaction kısa tutulur; lock/deadlock incelenir.

## Frontend
- Route/component lazy loading.
- Bundle analysis; gereksiz chart/kanban kütüphanesi ekleme.
- Büyük tabloda sanallaştırma sadece ölçüm gerekiyorsa.
- Gereksiz render ve büyük payloadları önle.

## Ölçüm raporu
p50/p95/p99 latency, error rate, DB query count/slow query, queue wait/duration/failure, bundle size/route load, file transfer. Test dataset ve makine bilgisi yazılır.

## Kabul
Her performans iyileştirmesi ölçüm veya net teknik kanıt içermeli. Cache yetkisiz veya eski finansal veriyi açığa çıkarmamalı.
