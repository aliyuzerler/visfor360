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
