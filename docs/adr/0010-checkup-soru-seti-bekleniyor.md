# ADR-0010: Check-Up soru seti ve kural matrisi bekleniyor (karar bekleniyor)

## Bağlam
Prompt 11, müşterinin teslim etmesi gereken Check-Up soru setini, cevap türlerini, puanlamayı ve cevap-hizmet karar tablosunu baz alır. Bu girdiler henüz teslim edilmedi.

## Karar
- Üretim hizmet öneri kuralı YAZILMADI. Yalnızca generic form builder prototipi + is_example=true etiketli örnek şablon geliştirildi.
- Deklaratif kural motoru tamamlandı: condition_groups (OR of groups, group = AND of conditions), operator allowlist (eq, neq, gt, gte, lt, lte, in, not_in, is_true, is_false), missing/invalid answer → koşul false, priority sıralaması, deterministik hash.
- eval / dynamic code / user SQL motor içinde HİÇBİR yerde çalıştırılmaz — kurallar yalnız JSON koşul ağacı.
- Tamamlanan oturum exact version snapshot saklar (sorular + kurallar + cevaplar); şablon sonradan değişse sonuç değişmez.
- Sonuç yalnız fırsat (Opportunity) ÖNERİSİ üretir; proje/task otomatik oluşmaz.

## Blokaj
Müşteriden beklenen girdi: (1) soru seti + cevap türleri, (2) puanlama, (3) cevap-hizmet karar tablosu. Girdi geldikten sonra CheckupTemplate kayıtları is_example=false olarak oluşturulur ve kurallar bu tablo ile kalıcılaşır.

## Sonuç
Motor deterministik ve güvenli; üretim kuralları müşteri girdisiyle aktive edilecek.
