# Visfor - ZCode Geliştirme Paketi

Bu paket, Visfor Ürün Analizi / Teknik Kapsam PDF'si ve kaynak SRS şartnamesine göre hazırlanmış geliştirme rehberidir. Çalışan uygulama kodu değil; ZCode ile aşamalı geliştirme için ürün bağlamı, mimari, agent kuralları, skill'ler, geliştirme promptları ve eşleşen test promptlarını içerir.

## Başlangıç
1. Paketin tamamını yeni Git deposunun köküne kopyalayın.
2. `AGENTS.md` dosyasını depo kökünde bırakın. ZCode bu dosyayı Workspace talimatı olarak görev başında okur.
3. ZCode'da **Settings -> Skills -> Import** yoluyla `skills/` klasöründeki becerileri içe aktarın ve hedef olarak **Current Project** seçin. Skills sayfasını yenileyin; tüm becerilerin listelendiğini ve etkin olduğunu doğrulayın. Prompt içinde ilgili beceriyi `$visfor-...` adıyla çağırın.
4. `references/` altındaki ürün analiz PDF'sini ve kaynak SRS'yi ZCode Agent görevine `@` ile referans verin veya görev bağlamına ekleyin.
5. `docs/OPEN_QUESTIONS.md` içindeki P0 kararları müşteriyle netleştirin.
6. Önce yalnızca Prompt 00'ı çalıştırın. Repo/hosting incelemesi onaylanmadan kodlama fazına geçmeyin.
7. Her geliştirme promptunu eşleşen test promptuyla takip edin. Testler geçmeden sonraki faza geçmeyin.

## Dosya haritası
- `AGENTS.md`: Her oturumda uyulacak proje kuralları.
- `docs/`: Ürün özeti, mimari, domain model, roller, ekranlar, güvenlik, test ve açık kararlar.
- `prompts/phase-0/`: Keşif ve kurulum.
- `prompts/phase-1/`: Çekirdek sistem.
- `prompts/phase-2/`: Gelişmiş otomasyon ve müşteri portalı.
- `prompts/quality/`: Tekrarlanabilir tasarım, güvenlik, performans ve regresyon kontrolleri.
- `skills/`: Tasarım, UI/UX, güvenlik, performans, mimari, test ve erişilebilirlik standartları.
- `templates/`: Karar, test ve API kayıt şablonları.
- `references/`: Kaynak SRS ve analiz PDF/DOCX.

## Önerilen prompt sırası
| No | Geliştirme | Kaynak faz |
|---|---|---|
| 00 | Repo keşfi ve uygulama planı | Hazırlık |
| 01 | Proje iskeleti, ortamlar ve CI | Faz 1 temeli |
| 02 | Tasarım sistemi ve uygulama kabuğu | Faz 1 temeli |
| 03 | Kimlik, RBAC ve Audit | Faz 1 temeli |
| 04 | CRM ve dinamik sicil alanları | Faz 1 |
| 05 | Evrak deposu ve süre uyarıları | Faz 1 |
| 06 | Departman, hizmet, pipeline ve proje | Faz 1 |
| 07 | Görev ve ekip iş yönetimi | Faz 1 |
| 08 | Vizyon ve 12 ana hedef / OKR | Faz 1 |
| 09 | Finans ve nakit akışı | Faz 1 |
| 10 | Dashboard, raporlar ve Faz 1 UAT | Faz 1 |
| 11 | Check-Up şablonları ve kural motoru | Faz 2 |
| 12 | Gelişim yol haritası ve fırsat havuzu | Faz 2 |
| 13 | Onaylı fırsatı projeye dönüştürme | Faz 2 |
| 14 | Çözüm ortağı, komisyon ve hakediş | Faz 2 |
| 15 | Müşteri portalı | Faz 2/3 kararı |
| 16 | REST API ve Webhook | Faz 2 |
| 17 | Güvenlik, performans ve canlıya geçiş | Her iki faz |

## Ürün sınırları
- Başlangıçta tek Visfor kurulumu varsayılır; başka danışmanlık firmalarına SaaS olarak sunulması kararlaştırılmamıştır.
- Müşteri portalında her müşteri yalnızca kendi firma/proje/belge verisine erişir. Bu zorunlu veri izolasyonu, SaaS tenant mimarisiyle aynı şey değildir.
- Resmi muhasebe, e-fatura/fatura kesme, online ödeme, native mobil uygulama, partner portalı, portal mesajlaşması ve gerçek WhatsApp/e-posta entegrasyonları kaynak analizde kesin kapsam değildir; ayrı karar/fiyat gerekir.
- Check-Up soru ve kural seti, 12 hedefin ölçüm formülleri, rol-yetki matrisi, finans formülleri ve hizmet pipeline aşamaları müşteri girdisi olmadan uydurulmayacaktır.

## Çalışma standardı
Her promptta önce etki analizi, sonra küçük adımlarla uygulama, ardından eşleşen test promptu çalıştırılır. Testler, migration, lint, type-check, build, security review ve dokümantasyon tamamlanmadan faz kapatılmaz. Her görev sonunda değişen dosyalar, test komutları/sonuçları, riskler ve açık kararlar raporlanır.
