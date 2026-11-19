# ADR-0004 - Kontrollü EAV / Custom Fields
- Durum: Önerilen.
- Karar: Kod yazmadan alan ekleme yalnız Customer sicilinde desteklenir. Definition ve value ayrılır. Tipler allowlist ile sınırlandırılır (text, textarea, integer/decimal, date, boolean, select, multi-select).
- Metadata: key, label, type, required, default, options, validation, searchable/filterable/reportable, visibility/edit permissions, order, active.
- Değer saklama: Typed columns veya güvenli JSONB seçenekleri test edilip seçilir; her şeyi tek string alanında saklama.
- Sınır: Çekirdek müşteri/proje/görev/finans kolonları EAV'ye taşınmaz. Alan tipi değişikliği veri migrasyonu kararı gerektirir. Silme varsayılanı deactivate/archive.
