# Test Stratejisi

## Katmanlar
1. Unit: domain kuralları, finans hesapları, Check-Up koşulları, validator, policy.
2. Feature/integration: HTTP, DB transaction, file storage, queue, notifications.
3. Frontend component: form, table, Kanban, loading/empty/error/forbidden state.
4. E2E: gerçek rol bazlı kullanıcı akışları.
5. Security: IDOR, privilege escalation, CSRF, upload bypass, rate limit, webhook signature.
6. Performance: pagination, N+1, dashboard, search, file ve queue.
7. UAT: müşterinin gerçek örnek süreçleri ve beklenen sonuçları.

## Kritik uçtan uca senaryolar
- Yönetici kullanıcı/departman oluşturur ve yetki verir.
- Müşteri oluşturulur, özel alan atanır ve kaydedilir.
- Belge yüklenir, geçerlilik tarihi uyarısı oluşur.
- Hizmet türü seçilerek proje açılır ve doğru Kanban kullanılır.
- Görev atanır/tamamlanır; geçmişi saklanır.
- Görev-hedef ilerlemesi yalnızca onaylı formülle hesaplanır.
- Finans kaydı P&L/nakit raporuna doğru yansır.
- Check-Up test cevabı fırsat üretir ama otomatik proje oluşturmaz.
- Müşteri mutabakatı ve insan onayı sonrası fırsat tek projeye dönüşür; tekrar çağrı duplicate üretmez.
- Portal kullanıcısı başka müşteri ID'siyle veri alamaz.
- Yetkisiz dosya indirilemez.
- Audit actor, zaman, action ve before/after içerir.

## Geçiş şartı
Test komutları, build, lint, type-check ve temiz DB migration geçer. Critical/High güvenlik açığı kalmaz. Test edilmeyen alanlar ve bilinen sorunlar raporlanır.

## Faz 1 UAT Senaryoları (Prompt 10)

> Her senaryo `templates/test-case.md` formatında yürütülür; sonuçlar UAT oturumunda kayda alınır.

| # | Senaryo | Adımlar | Beklenen |
|---|---|---|---|
| UAT-1 | Güvenli bootstrap | `php artisan app:init-admin` (parola gizli) | Kullanıcı + sistem-yöneticisi rolü oluşur, parola bir kez gösterilir |
| UAT-2 | Müşteri sicili | Müşteri ekle → yetkili ekle → özel alan tanımla → değer gir → arşivle | Kayıt arşivlenir, audit geçmişi korunur, tip değişikliği koruması çalışır |
| UAT-3 | Evrak yükleme | Vergi levhası PDF yükle + geçerlilik tarihi | Status badge (geçerli/yaklaşıyor), public URL üretilmez |
| UAT-4 | Proje + Kanban | Hizmet seç → proje oluştur → aşama geçir | Geçiş kuralları backend'de zorlanır; geçmiş actor/old/new/time kayıtlı |
| UAT-5 | Görev akışı | Görev oluştur → ata → tamamla | History + audit; gecikmiş filtre done/cancelled hariç |
| UAT-6 | Hedef slotları | goal-01 içerik gir → görev bağla → snapshot | Placeholder etiketi kalkar; formül onaylıysa hesap, değilse needs-configuration |
| UAT-7 | Finans kaydı | Gelir/gider gir → onayla → P&L ve nakit raporu gör | P&L ve cash ayrı; onaysız formülde sayı üretilmez |
| UAT-8 | Rol/izin | Satış sorumlusu rolü ata → dashboard'ı incele | Yalnız izinli modül kartları görünür; finans verisi sızmaz |
| UAT-9 | Audit inceleme | Ayarlar → Audit Log | before/after JSON, aktör, maskeleme (parola görünmez) |

### Faz 1 kapanış kriterleri (Definition of Done)
- [x] Tüm modüllerin eşleşen TEST promptları PASS
- [x] Kalite kapısı: pint + larastan + phpunit + tsc + build + migration smoke PASS
- [x] CI (GitHub Actions) success
- [x] Sahte KPI/statik grafik yok; KPI'lar gerçek aggregate sorgular
- [x] Append-only audit + trigger + model katmanı
- [ ] Müşteri kararları (P0): zorunlu sicil alanları, evrak türleri, aşama adları, OKR formülleri, finans formülleri, atama modeli
