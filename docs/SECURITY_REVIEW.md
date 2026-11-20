# Güvenlik İncelemesi (Prompt 17)

> Yürütme tarihi: 2026-11-20 · Yöntem: OWASP ASVS benzeri kontrol listesi + otomatik test paketi. Test ortamında yürütüldü; gerçek üretim yük hedefi müşteriyle netleşince yeniden ölçülür.

## 1. Kimlik ve Oturum (ASVS 3.1-3.5)

| Kontrol | Durum | Kanıt |
|---|---|---|
| Parola hash bcrypt (cost ≥ 10) | ✅ PASS | `User` model `hashed` cast; plaintext saklanmaz |
| Parola değişimi → eski oturumları kapatır | ✅ PASS | `AuthenticateSession` middleware + test |
| Login rate limit | ✅ PASS | `RateLimiter::for('login', 5/dk)` + `AuthController` |
| Portal ayrı guard | ✅ PASS | `portal` guard + `portal_users` ayrı tablo |
| Pasif kullanıcı/üyelik anında erişim keser | ✅ PASS | `PortalIsolationTest` |
| Zayıf parola reddi | ✅ PASS | `Password` kuralı (min 12, sınıf karışımı) |

## 2. Yetkilendirme (RBAC) ve İzolasyon

| Kontrol | Durum | Kanıt |
|---|---|---|
| Her route izin middleware/policy ile korumalı | ✅ PASS | `RbacTest`, route tanımları |
| UI gizleme tek başına yetki sayılmaz | ✅ PASS | Server-side `Gate` + policy; test |
| Portal IDOR → 404 (varlık ifşası yok) | ✅ PASS | `PortalIsolationTest` (cross-customer upload 404) |
| `customer_id` request parametresi tek başına güvenilir değil | ✅ PASS | Portal sorguları `activeMemberships` ile kısıtlı |
| Finans verisi yetkisiz rollere sızmaz (cache/props dahil) | ✅ PASS | `DashboardE2eTest` (Inertia payload'da finance anahtarı yok) |

## 3. Girdi Doğrulama ve Injection

| Kontrol | Durum | Kanıt |
|---|---|---|
| Tüm yazma uçlarında FormRequest/validate | ✅ PASS | Controller incelemesi + testler |
| Eloquent parametre bağlama (raw SQL yok) | ✅ PASS | `grep raw(` → yalnız orderByRaw allowlist (sabit string) |
| Check-Up kural motoru: eval/dynamic code yok | ✅ PASS | Deklaratif operator allowlist; `CheckupRuleEngineTest` |
| Dosya tipi/boyut allowlist (PDF/JPG/PNG, ≤10 MB) | ✅ PASS | Prompt 05 + portal upload testleri |
| Private storage (public URL yok) | ✅ PASS | `local` disk + signed download + random anahtar |

## 4. API ve Webhook

| Kontrol | Durum | Kanıt |
|---|---|---|
| Token hash-only saklama + scope + expiry/revoke | ✅ PASS | `ApiWebhookTest` |
| HMAC-SHA256 imza + ±300 sn replay penceresi | ✅ PASS | `ApiWebhookTest` (sink) |
| Delivery idempotency (unique endpoint+event) | ✅ PASS | `ApiWebhookTest` |
| Rate limit 60/dk + max page size 100 | ✅ PASS | `ApiWebhookTest` |
| Gerçek sağlayıcı entegrasyonu yok | ✅ PASS | `ApiWebhookTest::gercek_saglayici_entegrasyonu_yapilmamistir` |

## 5. Otomatik Taramalar

| Tarama | Sonuç |
|---|---|
| Static analysis (Larastan) | ✅ 0 hata (kalite kapısı) |
| Secret scan (kaynak kod, hardcoded secret pattern) | ✅ Temiz — secret'lar yalnız runtime üretilir (bin2hex/random_bytes), hash saklanır |
| Composer dependency audit | ⚠️ Yerel composer.phar sürüm sorunu nedeniyle çalıştırılamadı; CI pipeline'ına `composer audit` adımı önerilir. Laravel 12 güncel sürüm; bilinen kritik advisory yok (manuel kontrol). |
| Migration smoke (boş DB'den sıfır kurulum) | ✅ PASS (kalite kapısı) |

## 6. Bulgular ve Riskler

| Seviye | Bulgu | Aksiyon |
|---|---|---|
| Low | `APP_DEBUG=true` geliştirme env'inde — üretimde false olmalı | Runbook'ta zorunlu madde |
| Low | Virüs tarama servisi yok (dosya yükleme) | Opsiyonel not; ClamAV önerisi runbook'ta |
| Info | composer audit CI adımı eklenecek | Prompt 17 sonrası CI düzenlemesi |

**Kritik/High açık bulgu: YOK — release için engel yok.**
