# Performans Baseline (Prompt 17)

> Test ortamı ölçümleri (PHP 8.3.28 + PostgreSQL 16, tek vCPU sandbox). Üretim yük hedefleri müşteri kararıyla netleşince yeniden ölçülür; hedef değer UYDURULMAZ.

## 1. Yanıt Süreleri (yerel test ortamı, curl)

| Endpoint | HTTP | Süre |
|---|---|---|
| `GET /up` (health) | 200 | 100 ms |
| `GET /login` (Inertia sayfa) | 200 | 122 ms |
| `GET /api/v1/openapi.json` | 200 | 100 ms |

## 2. Veri Tabanı (500 müşteri seed'i üzerinde)

| Sorgu | Süre |
|---|---|
| Müşteri listesi (20 kayıt, isim sıralı) | 15.6 ms |
| Filtreli count (`city = Ankara` → 250) | 1.1 ms |
| Toplam index sayısı (customers/tasks/projects/financial_entries) | 19 |

Index stratejisi: arama (name ilike), durum (status), tarih (entry_date, due_date, valid_until) ve birleşik filtre kolonları (direction+entry_type+entry_date; customer_id+service_key+status; customer_id+status) indeksli.

## 3. N+1 Koruması

| Ekran | Sorgu sayısı sınırı | Test |
|---|---|---|
| Müşteri listesi | < 20 | `CustomersTest` |
| Proje Kanban | < 20 | `ProjectPipelineTest` |
| Görev listesi | < 20 | `TaskManagementTest` |
| Dashboard (aggregate KPI) | < 30 | `DashboardE2eTest` |

Tüm modüllerde pagination; kayıt sayısı büyüse bile belleğe sınırlı yükleme (query-level filtre).

## 4. Frontend Bundle (production build)

| Varlık | Boyut |
|---|---|
| `app.js` (minified) | ~688 KB |
| Gzip tahmini | ~220 KB |
| `app.css` | 51 KB (gzip ~8 KB) |

Not: Tek chunk >500 KB uyarısı — sonraki sürümde route-level code splitting planlandı (iyileştirme fırsatı, blocker değil).

## 5. Kuyruk ve Zamanlama

- Queue: `database` sürücüsü; webhook teslimatları iskelet aşamasında çağrı bazlı (`processPending`) — üretimde `queue:work` supervisor altında çalıştırılır (runbook).
- Scheduler: `schedule:run` cron; gecikmiş görev/evrak hatırlatıcıları istenen bildirim kanalı müşteri onayına kadar pasif (SRS sınırı).

## 6. Cache

- Dashboard KPI'ları: doğrudan aggregate sorgular (güncellik öncelikli); uygulama cache'i yalnız izin/permission çözümlemesinde. Sahte KPI/cache sızıntısı riski yok — finans verisi izin yoksa üretilmez.
