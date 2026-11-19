# Prompt 3 - Kimlik Rol Yetki ve Audit Altyapısı

## Ön koşul ve bağlam
Bu modül tüm ürünün güvenlik temelidir. Müşteri kesin rol matrisini henüz vermediyse önerilen roller yapılandırılabilir seed olarak işaretlenir; permission sınırları sonradan değiştirilebilir tasarlanır.

## Kullanılacak skill'ler
`$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`)

## Geliştirme görevleri
1. Frameworkün güncel resmi auth/session yaklaşımını kullan; login, logout, password reset ve gerekiyorsa email verification kur.
2. User, Role, Permission ve gerekirse UserDepartment ilişkilerini tanımla.
3. Permission'ları modül/aksiyon bazında ayır: view, create, update, archive, approve, export, download, manage settings.
4. Laravel Policy/Gate ile sunucu taraflı authorization uygula. UI görünürlüğü yalnızca yardımcı katmandır.
5. Kayıt kapsamını (kurum/departman/atanmış müşteri/atanmış proje/kendi görevi) permission'dan ayrı modelle.
6. Kritik işlemler için audit event altyapısı kur: actor, timestamp, entity, action, old/new value, request/correlation ID. Hassas alanları maskele.
7. Audit kaydı append-only olacak şekilde tasarla; normal kullanıcı CRUD ekranından değiştirilemesin.
8. İlk admin oluşturmayı güvenli komut/one-time bootstrap ile yap; sabit parola seed etme.
9. Login throttling, session invalidation, password reset token expiry ve admin MFA için karar notu oluştur.

## Beklenen çıktılar
Auth akışları, RBAC tabloları, policy örnekleri, audit service/table, role/permission seed, güvenlik dokümantasyonu ve testler.

## Sınırlar ve güvenlik kuralları
Kullanıcı rollerini nihai kabul etme; müşteri onayı gerekir. Admin parolasını kod içine koyma. Audit log'a parola, reset token veya cookie yazma. Yetki kontrollerini UI ile sınırlama.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
