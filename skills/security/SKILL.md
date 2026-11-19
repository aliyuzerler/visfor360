---
name: visfor-application-security
description: Threat-model and secure authentication, authorization, customer data isolation, file handling, audit logs, finance, APIs and webhooks; require evidence-based tests.
---

# Skill: Application Security

## Threat model
Her modülde veri varlıklarını, actor'ları, trust boundary ve suistimal senaryolarını belirle. Customer, Document, Finance, Opportunity, Audit ve Portal yüksek hassasiyettedir.

## Zorunlu kontroller
- Frameworkün güvenli auth/session yaklaşımı; özel kriptografi/auth protokolü yazma.
- Her route/action/API için authentication + policy + record scope.
- IDOR: farklı customer, department ve user ID'siyle erişim testi.
- CSRF, XSS, SQL injection, mass assignment, open redirect, SSRF, path traversal, insecure file access ve privilege escalation kontrolleri.
- File upload: MIME + extension + size allowlist, random storage path, private disk/bucket, authorized download.
- API: token scopes, expiration/rotation, rate limit, validation; secret query string'de olmaz.
- Webhook: HMAC signature, timestamp/replay guard, idempotency, retries/backoff, delivery log.
- Audit append-only; actor/time/record/action/before-after; secrets redact.
- Finance: decimal, transaction, approval/audit, race condition kontrolü.
- Check-Up: deklaratif allowlist rules; user input as code çalıştırılmaz.
- Portal: her ilişkili nesnede customer ownership; UI filter güvenlik değildir.

## Secure defaults
Deny by default; least privilege; fail closed; secrets never in git/log/client bundle; production errors don't expose stack traces; HTTPS/Secure cookies; archive before hard-delete.

## Çıktı
Bulgu severity, reproduction, affected route/data, remediation, otomatik regression test. Critical/High bulgular kapanmadan release önerme.
