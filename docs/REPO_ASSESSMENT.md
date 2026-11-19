# REPO_ASSESSMENT.md — Repo Keşif ve Uygunluk Değerlendirmesi (Prompt 00)

> Tarih: 2026-11-19 · Yürüten: ZCode agent · Kaynak: `Visfor_ZCode_Gelistirme_Paketi.zip`, `references/Visfor_Urun_Analizi_Teknik_Kapsam_ve_Musteri_Kesif_Formu.pdf`, `references/Visfor Danışmanlık Yazılım Şartnamesi.pdf`

## 1. Repo durumu

| Konu | Bulgu |
|---|---|
| Mevcut kod | **Yok.** GitHub deposu `aliyuzerler/visfor360` keşif anında boştu (ilk commit geliştirmeye aittir). |
| Framework / runtime | Yok — sıfırdan kurulacak. |
| Package manager / DB / auth / test / CI / deploy | Yok — Prompt 01'de oluşturulacak. |
| Korunacak mevcut parça | Yok (sıfır repo). Destructive risk: yok. |

Depo **public** olduğundan müşteri kaynak belgeleri (`references/*.pdf|docx`) ve `.env` gibi gizli dosyalar repoya **girmez**; `.gitignore` ile dışlanır. Repoya yalnızca türetilmiş geliştirme rehberi (docs/prompts/skills/templates) ve uygulama kodu girer.

## 2. Sandbox/hosting uyum değerlendirmesi (ADR-0002 doğrulaması)

Geliştirme ortamı (bu sandbox) kısıtları ve alınan uyum kararı:

| Katman | ADR-0002 önerisi | Sandbox gerçeği | Karar |
|---|---|---|---|
| Backend | Laravel (desteklenen kararlı sürüm) | PHP 8.3.28 statik CLI + Composer 2.11 kurularak sağlandı | **Laravel 12 (PHP 8.3 uyumlu)** ✔ uyumlu |
| Frontend | React + TypeScript + Inertia | Node v24.22.3 + npm mevcut | **Inertia v2 + React 19 + TS** ✔ uyumlu |
| DB | PostgreSQL (kararlı sürüm) | Root'suz ortamda portable PostgreSQL 17.2 (localhost, dışa kapalı) | **PostgreSQL 17** ✔ uyumlu; production'da yönetilen PG/uyumlu sürüm |
| Queue | database queue | Uygun | Database queue ✔ |
| Storage | private local disk | Uygun | Private local disk ✔ |
| Build | Vite | Uygun | Vite ✔ |
| Test | Pest + Playwright | Uygun | Pest ✔ (E2E Playwright, makul alt küme) |

**Sonuç:** Önerilen stack ile sandbox/uygulama arasında uyuşmazlık yok; alternatif stack gerekmiyor. Sürüm numaraları `composer.json`/`package.json` lockfile ile sabitlenir ve CI'da doğrulanır. Production hosting (VPS) bilgisi müşteriden gelmediği için deploy mimarisi "Docker Compose / reverse proxy ile tek VPS" varsayımıyla dokümante edilir (ADR-0002'de zaten öneri olarak var); bu bir varsayımdır ve `OPEN_QUESTIONS.md` P0-11'e bağlıdır.

## 3. Modül sınıflandırması (Faz 1 / Faz 2)

Analizdeki tüm modüller, paket `docs/PHASES_AND_DEPENDENCIES.md` ile tutarlı biçimde:

| # | Modül | Faz | Öncül |
|---|---|---|---|
| 1 | Identity (User, Role, Permission, UserDepartment) | 1 | — |
| 2 | Organization (Department, Membership) | 1 | Identity |
| 3 | Audit (AuditEvent, append-only) | 1 | Identity |
| 4 | Customers (Customer, Contact, CustomerType) | 1 | Identity |
| 5 | CustomFields (Definition/Value — EAV, ADR-0004) | 1 | Customers |
| 6 | Documents (Type, Document, Version, Request + uyarılar) | 1 | Customers |
| 7 | Services (ServiceType, Pipeline, PipelineStage, TransitionRule) | 1 | Organization |
| 8 | Projects (Project, StageHistory) | 1 | Customers+Services |
| 9 | Tasks (Task, Assignment, Comment, History) | 1 | Projects+Organization |
| 10 | Strategy (Vision, StrategicGoal, GoalTaskLink, Snapshot) | 1 | Tasks (formül müşteriden) |
| 11 | Finance (FinancialEntry, Category, Snapshot) | 1 | Customers (formül müşteriden) |
| 12 | Dashboard/Reports (gerçek aggregation) | 1 | 3–11 |
| 13 | Checkups (Template, Question, Rule, Session, Result) | 2 | Customers (kural seti müşteriden) |
| 14 | Opportunities (Opportunity, Decision, Conversion) | 2 | Checkups+Projects |
| 15 | Partners (Partner, Referral, Commission, Payout) | 2 | Projects+Finance |
| 16 | Portal (portal kullanıcısı, customer scope) | 2/3 (belirsizlik korunur) | 4–8 |
| 17 | Integrations (API /api/v1, webhook, ApiCredential) | 2 | Audit+domain events |
| 18 | Release hardening (güvenlik/perf/backup) | 2 | tümü |

## 4. P0 açık kararlar ve etkilenen modüller

`docs/OPEN_QUESTIONS.md` (1–12. maddeler P0) **yanıtlanmadı** → AGENTS.md kuralı gereği iş kuralları varsayımla **sabitlenmez**. Uygulama yaklaşımı (geliştirme ilerleyebilsin diye):

- Bu alanlar **yönetilebilir veri/model** olarak kodlanır: pipeline aşamaları, evrak türü matrisi, rol-yetki matrisi, 12 ana hedef tanımları, finans kategorileri, Check-Up soru/kural seti admin UI'sinden tanımlanabilir.
- Seed verileri **açıkça "sentetik örnek"** etiketli olur; gerçek müşteri formülü/rule seti sanılmaz.
- Formül gerektiren hesap (OKR ilerlemesi, P&L/runway) ancak kullanıcı tanımlı konfigürasyon varsa çalışır; yoksa boş/uyarı durumu gösterilir (sahte KPI yasak).

## 5. Test stratejisi ve faz planı

- Her faz: Pest unit/feature testleri (policy, validation, transaction, audit yan etkileri), critical-path E2E (Playwright), lint/type-check/build/migration smoke.
- Faz→test promptu eşlemesi `PROMPT_INDEX.md`'de zaten tam; ek bir faz atanmadı.
- Ayrıntılı dosya düzeyi plan: `docs/IMPLEMENTATION_PLAN.md`.

## 6. Riskler

1. **P0 boşluğu**: iş kuralları müşteri onaysız sabitlenemez; modüller konfigürasyonla teslim edilir (yukarıda).
2. **Public repo**: müşteri belgeleri ve secret'lar asla commit edilmez; CI'da da yalnızca sentetik test verisi kullanılır.
3. **Sandbox sürekliliği**: süreçler (PostgreSQL vb.) oturum arası ölür; portable kurulum + bootstrap betiği ile yeniden ayağa kaldırılır (uygulama kodunu etkilemez).
4. **Portal fazı belirsizliği** (Şartname 2.2 "Faz 2/3" ↔ Bölüm 7 "Faz 2"): korunur; Faz 2 sonunda yazılı müşteri kararı beklenir.
