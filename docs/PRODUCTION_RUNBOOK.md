# Üretim Runbook ve Canlıya Geçiş (Prompt 17)

> **Canlı deploy için açık müşteri onayı gerekir** (SRS sınırı). Bu runbook staging provasıyla kullanılır; gerçek üretim verisi staging'e kopyalanmaz.

## 1. Önerilen Mimari

- PHP 8.3 + PostgreSQL 16 (veya yönetilen PostgreSQL), Nginx/Apache, Redis (opsiyonel — cache/queue için önerilir; zorunlu değil, database driver çalışır).
- Süreçler: `php artisan queue:work --tries=3` (supervisor), `php artisan schedule:run` (cron 1 dk).
- HTTPS zorunlu; `APP_DEBUG=false`; `APP_ENV=production`.

## 2. İlk Kurulum

```bash
git clone <repo> && cd visfor360
composer install --no-dev --optimize-autoloader
cp .env.example .env   # değerleri girin (aşağıdaki kontrol listesi)
php artisan key:generate
php artisan migrate --force
php artisan app:init-admin   # ilk yönetici parolası bir kez gösterilir (gizli)
npm ci && npm run build
php artisan storage:link
php artisan optimize
```

## 3. Pre-Deploy Kontrol Listesi

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` https
- [ ] `APP_KEY` üretim ortamında üretildi ve kasada
- [ ] DB kimlik bilgileri kasada; `DB_PASSWORD` boş değil
- [ ] `php artisan migrate --status` beklemede migration yok
- [ ] `php artisan db:seed --force` ÇALIŞTIRILMAZ (örnek/placeholder veri üretim DB'sine girmez)
- [ ] Smoke: `/up` 200; giriş; dashboard KPI'ları gerçek sorgulardan
- [ ] Audit log yazıyor: yeni kayıt → `audit_events` satırı

## 4. Deploy Adımları (sıfıra yakın kesinti)

1. `git pull origin main` (tag'li release commit)
2. `composer install --no-dev --optimize-autoloader`
3. `php artisan migrate --force`
4. `npm run build && php artisan optimize`
5. Smoke (yukarıdaki liste) — başarısızsa **rollback**

## 5. Rollback (staging'de prova edilmeli)

```bash
git checkout <önceki_release_tag>
composer install --no-dev --optimize-autoloader
php artisan migrate:rollback --step=1   # yalnız son migration geriye uyumluysa
php artisan optimize
# Smoke → başarısızsa DB noktasal geri yükleme: docs/BACKUP_RESTORE.md §3
```

Not: Migration'lar additive yazıldı; geri alınabilirlik migration başına değerlendirilir. Rollback provası staging'de bir kez yapıldıktan sonra üretimde tekrarlanır.

## 6. İzleme ve Alarm

- Health: `/up` (HTTP 200) — harici uptime izleme (1 dk aralık) → alarm kanalı müşteri onayıyla.
- Log: `storage/logs/laravel.log` — log rotasyonu (günlük, 14 gün saklama); `failed` job tablosu izleme.
- Audit: `audit_events` satır artışı (anomali alarmı opsiyonel).
- Hata raporlama: Sentry/Flare entegrasyonu ayrı müşteri kararı (kapsam dışı).

## 7. Bilinen Sınırlar (canlıya geçiş öncesi müşteriye bildirilir)

1. Check-Up üretim soru seti + hizmet eşleme kararı bekliyor (ADR-0010) — motor hazır.
2. OKR ilerleme formülleri müşteri örnek hesaplarıyla kalıcılaşacak (ADR-0009) — onaysız yüzden üretilmez.
3. Finans P&L/runway formülleri onay bekliyor (ADR-0005).
4. Portal canlıya alma müşteri kararı (ADR-0014) — feature flag ile açılır.
5. Partner portal, banka entegrasyonu, virüs tarama, gerçek mesajlaşma sağlayıcıları kapsam dışı (ADR-0013/0015).
6. Fırsat statü adları örnek set (ADR-0011).

## 8. Handover Checklist

- [ ] UAT senaryoları müşteriyle yürütüldü (docs/TEST_STRATEGY.md) ve imza alındı
- [ ] Eğitim oturumları: yönetici/satış/ekip/yönetim (docs/RELEASE_NOTES_FAZ1.md §Eğitim)
- [ ] P0 müşteri kararları listesi yanıtlandı (docs/RELEASE_NOTES_FAZ1.md §P0)
- [ ] Bu runbook + backup plan müşteriye teslim edildi
- [ ] CI yeşil: 159+ test, kalite kapısı PASS
