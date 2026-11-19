---
name: visfor-test-engineering
description: Create and run unit, integration, policy, security, component and end-to-end tests for Visfor business workflows; report real outcomes.
---

# Skill: Test Engineering

- Her gereksinim için beklenen davranış ve negatif/sınır durumu belirle.
- Backend: domain unit, request validation, policy/scope, DB transaction, queue/storage tests.
- Finance: decimal fixtures, rounding, date boundary, currency cases.
- Check-Up: AND/OR, empty/invalid answers, threshold, priority, version tests.
- Frontend: form, table, loading/empty/error/success/forbidden states.
- E2E: one critical path per role plus portal IDOR test per resource type.
- Isolated test DB and deterministic fake data; production credentials/data never used.
- Tests assert side effects (audit, event, job) not just HTTP 200.
- Failed/skipped tests must be listed; don't claim pass without execution.
