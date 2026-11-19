# Canlıya Geçiş ve İşletim

## Ortamlar
Local: app + PostgreSQL + sentetik seed data. Test: izole DB, fake mail/storage. Staging: prod benzeri ve maskelenmiş/sentetik veri. Production: HTTPS, secret injection, private storage, worker/scheduler, backup ve monitoring.

## Deploy kontrol listesi
- [ ] Domain, DNS ve HTTPS hazır.
- [ ] Runtime ve extension sürümleri uyumlu.
- [ ] DB ayrı kullanıcı ve minimum yetkiyle çalışıyor; public internet erişimi yok.
- [ ] Secret'lar güvenli şekilde yüklendi; `.env` repoda değil.
- [ ] Debug kapalı, güvenli error handling açık.
- [ ] Backup alındıktan sonra kontrollü migration çalıştırıldı.
- [ ] Queue worker ve scheduler servisleri aktif.
- [ ] Private file storage erişimi doğrulandı.
- [ ] DB ve dosyalar yedekleniyor; restore testi yapıldı.
- [ ] Health check, log ve alarm çalışıyor.
- [ ] Admin hesapları güçlü auth/MFA ile korunuyor.
- [ ] Rollback planı ve bakım penceresi belirlendi.

## VPS keşfi
Mevcut sunucu kullanılacaksa mevcut siteler, CyberPanel/web server, portlar, DB sürümleri, RAM/disk, backup, PHP runtime ve reverse proxy önce incelenir. Yeni servisler mevcut sitelerle port/DB kullanıcı çakışmasına girmemelidir. PostgreSQL'i internete açma. Docker/Reverse proxy yaklaşımının mevcut hosting paneliyle uyumu staging'de kanıtlanmalıdır.

## İşletim/sözleşme
Hosting, SSL, domain, SMTP, object storage, yedekleme, patch/güncelleme, güvenlik olayı, destek saati, SLA, RPO/RTO ve bakım sorumlusu yazılı kararlaştırılır.
