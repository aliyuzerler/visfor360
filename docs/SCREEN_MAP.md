# Ekran Haritası

| Menü | Alt ekranlar | Faz |
|---|---|---|
| Genel Bakış | KPI, bekleyen onay, geciken işler, evrak uyarısı, nakit özeti | 1 |
| Müşteriler | Liste, oluştur/düzenle, firma detayı, yetkililer, özel alan, evrak, projeler, aktivite | 1 |
| Projeler/Hizmetler | Proje listesi, hizmet kataloğu, proje detay, hizmete özel Kanban | 1 |
| Görevler | Benim görevlerim, ekip görevleri, görev detayı, atama, geçmiş | 1 |
| Stratejik Hedefler | 5 yıllık vizyon, 12 ana hedef listesi/detayı, ilerleme | 1 |
| Finans | Gelir, gider, tahsilat/ödeme, P&L, nakit akışı, runway | 1 |
| Check-Up | Şablon, soru bankası, oturum, sonuç, yol haritası | 2 |
| Fırsat Havuzu | Liste, detay, inceleme, müşteri mutabakatı, onay/red/dönüşüm | 2 |
| Çözüm Ortakları | Partner, iş/referral, komisyon, hakediş | 2 |
| Müşteri Portalı | Giriş, kendi proje aşamaları, eksik evrak, yükleme | 2/3 teyit |
| Raporlar | Müşteri, proje, departman, görev, hedef, finans | 1 temel / 2 gelişmiş |
| Yönetim/Ayarlar | Kullanıcı/rol, departman, hizmet, özel alan, pipeline, log, entegrasyon | 1/2 |

Her ekranda loading, empty, error, success, disabled ve permission-denied durumu bulunur. Liste ekranlarında server-side pagination, arama, filtreleme ve sıralama olmalıdır. Dashboard KPI'ları gerçek backend verisinden gelmelidir; veri yoksa empty state gösterilir.
