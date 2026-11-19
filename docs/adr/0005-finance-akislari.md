# ADR-0005: Finans onay/ödem akışları (karar bekleniyor)

## Bağlam
Tahsilat/ödeme, kısmi ödeme, iptal/düzeltme ve onay akışı müşteri cevabı bekliyor.

## Karar
- Durum makinesi: draft → pending → approved → cancelled (approved kaydında tutar/ön/tarih değişimi → eski kayıt cancelled, yeni kayıt parent_entry_id zincirli).
- Formüller (P&L, runway) finance_settings'te onay bayrağıyla etkinleşir; onaysızsa hesap yerine needs_configuration.
- Bu kararlar müşteri cevabıyla güncellenir.

## Sonuç
- Veri kaybı yok; geçmiş korunur; uydurma finansal sayı üretilmez.
