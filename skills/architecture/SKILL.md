---
name: visfor-domain-architecture
description: Design modular-monolith boundaries, domain models, transactions, EAV limits, API contracts, ADRs and maintainable application structure.
---

# Skill: Architecture and Domain Design

## Kurallar
- Kaynak gereksinim, onaylı karar, analiz önerisi ve açık soruyu ayır.
- Domain/use-case logic controller ve UI'dan ayrılır.
- Modüller arası yazma, tanımlı application service/event üzerinden.
- DB foreign key, unique index, transaction ve check constraint kullan.
- EAV yalnız customer custom fields için kontrollü.
- Para decimal + currency; saat UTC.
- Pipeline stage, opportunity conversion, financial posting gibi kritik akışlar atomik/auditli.
- Queue jobs retry-safe/idempotent.
- API `/api/v1` ve OpenAPI dokümante.
- Önemli kararlar ADR ile kayıtlı.

## ADR formatı
Context, decision, alternatives, pros/cons, consequences, migration/rollback plan, status, owner/date.

## Kaçınılacaklar
Dev controller/service; her modülden bütün tablolara doğrudan erişim; JSON içine tüm çekirdek iş alanları; eval tabanlı rule engine; onaysız otomatik iş; ilk sürümde gereksiz mikroservis/Kafka/Kubernetes; müşteri kararı olmayan hardcoded iş kuralı.
