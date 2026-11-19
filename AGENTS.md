# AGENTS.md - Visfor Proje Kuralları

## Kaynakların önceliği
1. `references/Visfor_Urun_Analizi_Teknik_Kapsam_ve_Musteri_Kesif_Formu.pdf` ana analiz kaynağıdır.
2. `references/Visfor Danışmanlık Yazılım Şartnamesi.pdf` müşterinin ilk SRS kaynağıdır.
3. Onaylanmış müşteri kararları ve `docs/decisions/` ADR kayıtları, öneri niteliğindeki analiz maddelerinden üstündür.
4. Analiz önerilerini müşteri onayı olmadan kesin gereksinime dönüştürme.
5. `docs/OPEN_QUESTIONS.md` içindeki P0 maddeler cevaplanmadan iş kurallarını sabitleme.

## Genel geliştirme kuralları
- Önce mevcut repo, runtime, package manager, DB, auth, test ve deploy yapısını incele. Çalışan sistemi sebepsiz yeniden yazma.
- Her görevden önce kapsam, bağımlılık, etkilenecek modül, DB değişikliği, güvenlik etkisi ve test planını raporla.
- Küçük ve gözden geçirilebilir değişiklik yap; faz dışı refactor/özellik ekleme.
- Domain iş mantığını controller veya UI bileşenine yığma. Validation, Policy ve application service/use-case sınırlarını koru.
- Sahte KPI, gerçekmiş gibi görünen demo veri, çalışmayan buton veya boş aksiyon bırakma.
- Yetki kontrolünü yalnızca arayüzde yapma; her okuma/yazma sunucuda kontrol edilir.
- Silme yerine varsayılan olarak arşivleme/soft delete kullan; hard delete için açık politika gerekir.
- Migration'lar mevcut veriyi kaybettirmemeli. Destructive migration için önce etki/rollback planı sun ve onay al.
- Tarih/saat UTC saklanır; para `DECIMAL` + currency ile saklanır, float kullanılmaz.
- EAV yalnızca müşteri sicilindeki esnek özel alanlar içindir; çekirdek alanları JSON/EAV'ye taşıma.
- Check-Up kural motorunda `eval`, dinamik PHP/JS kodu, SQL veya shell çalıştırma yasaktır.
- Check-Up tespiti otomatik iş açmaz. Müşteri mutabakatı ve yetkili insan onayı olmadan proje/task oluşturma.
- OKR ilerlemesini onaylanmış ölçüm formülü olmadan hesaplama.

## Güvenlik
- Secret, parola, token veya gerçek müşteri verisi repoya/loga yazılmaz.
- Frameworkün desteklediği güvenli parola hashing ve session/auth yaklaşımını kullan; özel kriptografi yazma.
- Production cookie Secure/HttpOnly/SameSite; CSRF koruması etkin.
- RBAC + kayıt kapsamı Policy/Gate ile server-side uygulanır.
- Portal sorguları kullanıcı -> müşteri üyeliği -> customer scope zinciriyle sınırlandırılır.
- Dosyalar public web root dışında private storage'da saklanır; tür/boyut doğrulanır; indirme yetki kontrolüyle yapılır.
- Audit log'a parola/token/cookie/secret yazma. Kritik olaylarda actor, time, entity, action ve before/after tutulur.
- API rate limit, input validation, output encoding, parametreli sorgular ve güvenli hata yanıtı zorunludur.

## Test ve kalite
- Her iş kuralına unit test; endpoint ve DB davranışına feature/integration test; kritik akışa E2E test ekle.
- Test başarısızsa işi tamamlandı olarak raporlama. Atlanan testleri ve nedenini açıkla.
- Lint, formatter, static analysis, type-check, test, build ve migration smoke çalıştır.
- Her yeni endpoint için valid, invalid, unauthenticated, forbidden, not found ve sınır testlerini düşün.
- UI loading/empty/error/success/disabled/forbidden durumlarını ele al.
- Klavye erişimi, focus, form label ve kontrastı denetle.

## Mimari varsayılanı
- Önerilen başlangıç: Laravel modüler monolit + React/TypeScript/Inertia + PostgreSQL.
- Stack mevcut repo/hosting ile uyuşmuyorsa Prompt 00'da gerekçeli alternatif sun; onaysız değiştirme.
- Modül sınırlarını koru. Mikroservis, Redis, Kubernetes veya event bus yalnızca gerçek ihtiyaç ve ADR ile eklenir.
- API `/api/v1` ile versiyonlanır; dosyalar private storage; ağır işler queue/scheduler üzerinden yürür.
- Sürüm seçerken güncel resmi doküman/uyumluluk kontrolü yap; exact version uydurma, lockfile commit et.

## Görev sonu raporu
1. Tamamlananlar ve kapsam dışı bırakılanlar.
2. Değişen dosyalar ve migration'lar.
3. Gerçek komutlar ve test sonuçları.
4. Güvenlik/performance değerlendirmesi.
5. Açık kararlar, riskler ve sonraki adım.
6. Güncellenen doküman/ADR.
