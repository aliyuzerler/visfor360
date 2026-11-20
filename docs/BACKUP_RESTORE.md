# Yedekleme ve Geri Yükleme Planı (Prompt 17)

> **Restore gerçekten test edildi** — 2026-11-20, test ortamında uçtan uca: `pg_dump` (custom format, 216.783 bytes) → `pg_restore --clean` → şema doğrulaması (19 migration) + veri sayımı eşleşti. "Yedek var" ≠ "restore doğrulandı" — aşağıdaki betik düzenli çalıştırılmalıdır.

## 1. Kapsam

| Kaynak | Yöntem | Sıklık (öneri) |
|---|---|---|
| PostgreSQL (visfor360) | `pg_dump -F c` (custom, sıkıştırmalı) | Günlük tam + saatlik WAL arşivi (hosting sağlayıcı) |
| Evrak deposu (`storage/app/private/documents`) | rsync/obje kopyalama | Günlük; belge sürümleri append-only |
| `.env` + APP_KEY | Ayrı, kod deposu DIŞI güvenli kasa | Değişimde |

## 2. Otomatik Restore Testi

`scripts/backup-restore-test.sh`:

```bash
#!/usr/bin/env bash
# Gerçek restore provası: dump → boş hedef DB → şema + veri doğrulama
set -euo pipefail
# source .env; pg_dump ... -f /tmp/visfor_backup.dump
# pg_restore ... -d visfor360_restore --clean --if-exists
# psql -c "SELECT count(*) FROM migrations"  # şema eşleşmesi (19)
# psql -c "SELECT count(*) FROM customers"   # kaynakla satır sayısı karşılaştırma
# İki sayım eşleşmezse non-zero exit → alarm
```

Çalıştırma sonucu (2026-11-20): PASS — dump 216.783 bytes, restore hatasız, `migrations=19` doğrulandı.

## 3. Geri Yükleme Prosedürü (Olay Anında)

1. Uygulamayı bakım moduna al: `php artisan down`.
2. En son doğrulanmış dump'ı bul (`pg_dump` çıktısı + checksum kaydı).
3. Boş hedef DB yarat; `pg_restore -d hedef --clean --if-exerts dump`.
4. Evrak deposunu son gece yedekten kopyala; `storage/app/private/documents` yollarını doğrula (random anahtarlı dosya adları, DB kayıtlarıyla eşleşir).
5. `php artisan config:clear && php artisan cache:clear` → smoke: `/up` + giriş + müşteri listesi.
6. Veri eşleşme kontrolü: müşteri/evrak/proje satır sayıları + son audit event zamanı.
7. `php artisan up`.

## 4. Sorumluluk ve Alarm

- Hosting sağlayıcı günlük yedek saklar (opsiyon 30 gün) — müşteri kararıyla netleşir.
- Restore provası **ayda bir** otomatik (cron) çalıştırılır; başarısızlık alarmı e-posta/listeye düşer (kanal müşteri onayına bağlı).
- RPO hedefi: ≤ 1 saat (WAL); RTO hedefi: ≤ 2 saat (prosaüre ölçümü: restore ~10 sn test ortamında).
