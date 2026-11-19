# IMPLEMENTATION_PLAN.md — Dosya/Modül Düzeyi Uygulama Planı (Prompt 00)

> Kapsam: Prompt 00 keşif çıktısı. Plan, `PROMPT_INDEX.md` sırasını ve `docs/PHASES_AND_DEPENDENCIES.md` bağımlılıklarını izler. Her adımın sonunda eşleşen TEST promptu çalıştırılır; geçilmeden sıradaki adıma geçilmez.

## 0. Ortak mimari kurallar (tüm fazlar)

```
app/
├── Domain/<Modül>/Models        # Eloquent modelleri (modül klasörü)
├── Domain/<Modül>/Policies      # server-side yetki + kayıt kapsamı
├── Domain/<Modül>/Actions       # use-case / application service (iş mantığı)
├── Domain/<Modül>/Data          # request DTO / validasyon verileri
├── Http/Controllers/...         # yalnız orchestration (thin controller)
├── Http/Requests/<Modül>/       # FormRequest validasyon
└── Support/                     # ortak yardımcılar (audit, scope, money)
```

- Auth: Laravel Breeze (Inertia) + RBAC tablosal model (roles/permissions/user_roles/role_permissions + department membership) — dış permission paketi yerine az ve denetlenebilir çekirdek.
- Audit: append-only `audit_events` (actor, action, auditable, before/after JSON, IP, UA); model observer tabanlı `Auditable` trait; secret alan adları maskelenir.
- Para: `decimal(18,4)` + `currency` kolonu; iş hesapları bcmath. Saat: UTC.
- Silme: `SoftDeletes` + gerekli yerlerde `archived_at`.
- Yetki: her route `permission + policy` ile; IDOR testleri her modülde.
- Upload: `storage/app/private` (local), allowlist MIME/uzantı, rastgele key, yetkili indirme route'u.
- API: `/api/v1` (token auth, Sanctum), OpenAPI dokümantasyonu; webhook HMAC-SHA256.
- Queue: `database` driver; scheduler girdileri `routes/console.php`.

## 1. Faz 0 — Kurulum (Prompt 00–01)

| Adım | Çıktı |
|---|---|
| 00 | `docs/REPO_ASSESSMENT.md`, `docs/IMPLEMENTATION_PLAN.md` ✔ |
| 01 | Laravel 12 + Inertia v2 (React 19 + TS + Tailwind 4) iskeleti, PostgreSQL bağlantısı, `.env.example`, Pint/Larastan, Pest, CI (GitHub Actions), `/health` endpoint, `docker-compose.yml` (yalnız app+db, portlar lokal), README setup |

## 2. Faz 1 — Çekirdek (Prompt 02–10)

| Prompt | Migration / Model | UI | Test odağı |
|---|---|---|---|
| 02 | — (design tokens, layout bileşenleri) | AppShell: sidebar, topbar, breadcrumbs, state bileşenleri (empty/error/loading), design-system.md | Görsel bileşen smoke, a11y temel |
| 03 | users(zenginleştirme), departments, department_user, roles, permissions, role_permission, role_user, audit_events, password policy | Kullanıcı/rol/departman yönetimi, profil | RBAC policy + audit + IDOR |
| 04 | customers, contacts, customer_types(seeded config), custom_field_definitions, custom_field_values | CRM list/detay (sekmeler), custom field yöneticisi | EAV tipleri, filtre, policy |
| 05 | document_types, documents (+document_requests) | Evrak sekmeleri, yükleme, uyarı badge'leri | private storage, yetkili indirme, geçerlilik uyarısı |
| 06 | service_types, pipelines, pipeline_stages, projects, project_stage_histories | Hizmet kataloğu, pipeline şablon editörü, proje Kanban | stage transition atomik+audit, transition rule |
| 07 | tasks, task_assignments, task_comments, task_histories | Görev panoları/listeleri, detay | atama, tamamlama, geçmiş, hedef bağı stub |
| 08 | visions, strategic_goals (sıra+ad+formül alanları), goal_task_links, goal_progress_snapshots | Vizyon + 12 hedef ekranları, ilerleme (yalnız onaylı formülle) | formül yoksa boş durum; snapshot |
| 09 | finance_categories, financial_entries (planned/actual, income/expense, DECIMAL+currency) | Finans kayıtları, P&L/nakit görünümü (konfigürasyona bağlı) | decimal hesaplar, iptal/düzeltme auditli |
| 10 | — (rapor sorguları) | Dashboard (gerçek veri), rapor ekranları, Faz1 UAT | uçtan uca akış, N+1, boş durumlar |

## 3. Faz 2 — Gelişmiş otomasyon (Prompt 11–17)

| Prompt | Çekirdek | Test odağı |
|---|---|---|
| 11 | checkup_templates(+version), questions, options, declarative rules (JSON koşul ağacı, allowlist operator), sessions, answers, results(snapshot) | determinizm, AND/OR, eşik, sürüm snapshot; eval yok |
| 12 | roadmap_items, opportunities (statü: new/in_review/customer_sync/approved/rejected/deferred) | tespit→fırsat; insan onayı zorunlu |
| 13 | opportunity→project conversion (transactional, idempotency key) | çift dönüşüm engeli, doğru Kanban |
| 14 | partners, partner_referrals, commissions, payouts | matrah/oran hesabı, onay akışı |
| 15 | portal_users (customer'a bağlı), portal rotaları | IDOR: her kaynakta ownership; izole test |
| 16 | api_credentials (Sanctum token+scope), webhook_endpoints, webhook_deliveries; /api/v1 dokümante | scope/rate limit; HMAC imza, retry, delivery log |
| 17 | backup komutları, health derinleştirme, baseline ölçüm; deploy docs | güvenlik regresyonu, performans baseline |

## 4. Migration ve deploy riskleri

- Tüm migration'lar temiz DB'den sıfır uygulanabilir olmalı (CI'da migration smoke).
- Şu an canlı veri yok; destructive migration riski yok. İleride müşteri verisi gelirse `CHANGE_MANAGEMENT.md` akışı işler.
- Deploy: docker-compose (dev) + production önerisi (NGINX + PHP-FPM + PG + worker + scheduler) Prompt 17'de dokümante edilir; gerçek host kararı P0-11'e bağlı.

## 5. Kabul ölçütleri (faz kapanışı)

`docs/DEFINITION_OF_DONE.md` listesi her fazi kapatır: izlenebilirlik, UI durumları, server-side auth testleri, audit, migration güvenliği, lint/type/build/test geçişi, güvenlik-performans-UIUX-a11y review, dokümantasyon/ADR güncelliği, repoda secret yok, rapor.
