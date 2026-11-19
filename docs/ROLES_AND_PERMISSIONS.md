# Rol ve Yetki Matrisi - Başlangıç Taslağı

Kaynak SRS rol listesini ve izin matrisini tanımlamıyor. Aşağıdakiler öneridir.

| Rol | Müşteri | Proje/Görev | Finans | Check-Up/Fırsat | Ayarlar |
|---|---|---|---|---|---|
| Sistem yöneticisi | Teyit | Teyit | Teyit | Teyit | Yönetir |
| İşletme yöneticisi | Kurum geneli? | Tüm departman? | Tüm finans? | Onay/inceleme? | Sınırlı |
| Satış/müşteri sorumlusu | Atanmış müşteri? | Görüntüleme? | Sınırlı? | Oluşturur/mutabakat | Yok |
| Departman yöneticisi | İş kapsamı? | Kendi departmanı | Yok/özet? | İnceleme? | Kendi pipeline? |
| Danışman/personel | Minimum gerekli | Atanmış işler | Yok | Yok/cevaplama? | Yok |
| Finans kullanıcısı | Finans için gerekli | Sınırlı bağlam | Yönetir | Yok | Finans ayarı? |
| Müşteri portalı | Sadece kendi firması | Sadece kendi projeleri | Yok | Yok | Yok |
| Çözüm ortağı | Varsayılan erişim yok | Varsayılan yok | Varsayılan yok | Yok | Yok |

Her modülde `view`, `create`, `update`, `archive/delete`, `approve`, `export`, `download`, `manage_settings` izinleri ayrı değerlendirilir. Kayıt kapsamı: kurum geneli, departman, atanmış müşteri, atanmış proje veya kendi görevi olabilir.

Kabul: UI gizleme yeterli değildir. Her endpoint/use-case server-side policy çalıştırır. ID/URL değişikliği başka müşteriye erişim sağlamaz. Pasif kullanıcı geçmiş kayıtlardaki actor olarak korunur.
