# ADR-0001 - Modüler Monolit
- Durum: Önerilen.
- Karar: İlk sürüm tek deploy edilebilir modüler monolit olacak.
- Bağlam: Çok sayıda iş modülü var ancak SRS mikroservis/ayrı ölçek gereksinimi belirtmiyor.
- Gerekçe: Tek VPS ve küçük ekipte daha az operasyon maliyeti; transaction gerektiren akışlar daha kolay.
- Sonuç: Domain sınırları açık kalır; mikroservis sadece ölçülmüş ihtiyaç ve yeni ADR ile.
