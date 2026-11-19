# Müşteri Açık Kararları

Bu maddeler analiz PDF'sindeki keşif soruları ve karar tablosuna dayanır. Yanıtlanmayan maddeyi ZCode varsayımla kesinleştirmemelidir.

## P0 - Eforu ve mimariyi doğrudan etkiler
1. Uygulama yalnızca Visfor için mi, ileride SaaS olarak başka danışmanlık firmalarına da sunulacak mı?
2. Kullanıcı rolleri ve her rolün müşteri/proje/belge/finans/rapor erişim matrisi nedir?
3. Departmanlar ve sunulan tüm hizmetlerin listesi nedir?
4. Her hizmetin Kanban aşamaları, terminal aşaması ve aşama geçiş kuralları nedir?
5. Firma sicilindeki zorunlu alanlar ve şirket/müşteri türleri nelerdir?
6. Evrak türleri, hangi müşteride zorunlu oldukları, geçerlilik ve uyarı zamanları nedir?
7. Custom Field veri tipleri, kimlerin alan ekleyebileceği ve raporlama/filtre ihtiyacı nedir?
8. 12 ana hedefin adları, tanımları, sahipleri, dönemleri, başlangıç/hedef değerleri ve ilerleme formülleri nedir?
9. Bir görev kaç hedefe bağlanabilir; katkı/ağırlık nasıl hesaplanır?
10. Finans kategorileri, tahmini/gerçekleşen ayrımı, P&L ve runway formülleri, para birimleri ve tahsilat/ödeme mantığı nedir?
11. Hosting, DB, dosya depolama, backup ve işletim sorumlusu kimdir?
12. UAT süresi, kabul koşulu, eğitim, kaynak kod ve deploy dokümantasyonu teslimi nedir?

## P1 - İlgili modül öncesinde gerekli
13. Görev tek/çoklu sorumlu mu? Alt görev, yorum, dosya, efor ve tekrar eden görev gerekiyor mu?
14. Mevcut Check-Up soru seti ve örnek rapor var mı?
15. Soru tipleri, puanlama, koşullu görünürlük ve tekrar değerlendirme nasıl çalışır?
16. Hangi cevap/koşul hangi hizmet önerisini üretir; AND/OR, eşik ve öncelik nedir?
17. Yol haritası hangi formatta sunulacak?
18. Fırsat statüleri, ret/erteleme nedenleri, onay rolü ve müşteri mutabakat kanıtı nedir?
19. Aynı firma/hizmette mükerrer fırsat ve tek fırsattan çoklu hizmet üretme kuralı nedir?
20. Partner komisyon matrahı, sabit/oransal oranı, tahakkuk ve ödeme onayı nedir?
21. Müşteri portalı Faz 2 mi Faz 3 mü? Bir firmadan birden fazla portal kullanıcısı olacak mı?
22. Portalda belge yükleme dışında belge onayı, indirme veya mesaj/not özelliği isteniyor mu?
23. API tüketicileri, auth scope ve rate limit beklentisi nedir?
24. Hangi gerçek dış servis entegrasyonları isteniyor? API altyapısı ile entegrasyon geliştirmesi ayrımı nedir?
25. Eski CRM/Excel verisi aktarılacak mı; dosya sayısı ve hacmi nedir?
26. KVKK saklama, silme/anonimleştirme ve müşteri talep süreçleri nasıl yönetilecek?
27. Backup sıklığı, RPO/RTO, saklama süresi ve restore sorumlusu nedir?
28. Tarayıcı, mobil ekran, eşzamanlı kullanıcı ve veri hacmi hedefleri nedir?

## P2 - Görsel ve operasyonel
29. Logo, marka renkleri, font ve tasarım referansı var mı?
30. Dark mode isteniyor mu?
31. Dashboard'da öncelikli KPI ve raporlar hangileri?
32. Bildirim kanalı ve zamanlama nedir?
33. Eğitim, yardım ve onboarding beklentisi nedir?

## Şartname tutarsızlığı
Müşteri portalı Bölüm 2.2'de Faz 2/3 hedefi, Bölüm 7'de Faz 2 olarak yazılmıştır. Yazılı karar gereklidir. API/Webhook altyapısı teslimi ile gerçek dış servis entegrasyonları da ayrı fiyatlandırılmalıdır.

## Geliştirme kuralı
P0 yanıtı yoksa agent etki ve soru raporu verir; üretim iş kuralını varsayımla kodlamaz. UI iskeleti ve sentetik test verisi ilerleyebilir; gerçek iş formülü sabitlenmez.
