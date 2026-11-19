# ADR-0006 - Check-Up Kural Motoru
- Durum: Faz 2 öncesi müşteri veri seti bekleniyor.
- Öneri: Deklaratif condition groups (AND/OR), allowlist karşılaştırma operatörleri, eşikler ve hizmet önerisi çıktısı. Serbest PHP/JS, SQL veya `eval` çalıştırılmaz.
- Gerekli girdi: soru seti/tipleri, seçenek kodları, puanlama/eşik, koşul kombinasyonları, hizmet mapping, öncelik/çakışma, örnek input-output testleri.
- Her oturum template/rule version snapshot saklar. Aynı cevap ve aynı sürüm deterministik sonuç üretir.
