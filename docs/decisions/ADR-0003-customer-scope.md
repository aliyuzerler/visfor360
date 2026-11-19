# ADR-0003 - Customer Scope ve SaaS Tenant Ayrımı
- Durum: Önerilen; SaaS kararı bekliyor.
- Bağlam: Visfor kendi müşteri şirketlerini yönetecek; portal kullanıcısı yalnız kendi firmasını görmeli. Diğer danışmanlık firmalarına SaaS satılacağı belirtilmemiş.
- Karar: İlk etapta tek Visfor çalışma alanı. Customer kaydı portal veri sınırıdır; her portal sorgusu user membership -> customer scope ile daraltılır.
- Sonuç: Customer ve SaaS Tenant kavramları karıştırılmaz. SaaS kararı gelirse tenant admin, tenant settings ve veri izolasyonu için yeni ADR ve migration planı hazırlanır.
