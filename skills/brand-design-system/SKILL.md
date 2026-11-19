---
name: visfor-brand-design-system
description: Create and maintain a consistent, brand-safe enterprise design system with tokens, components, typography, spacing, status colors and visual quality checks.
---

# Skill: Brand Design System

## Amaç
Visfor için marka varlıklarına saygılı, tutarlı, kurumsal ve sürdürülebilir tasarım sistemi kur.

## Başlangıç
- Önce logo/marka kılavuzunu `references/` içinde ara. Yoksa marka rengi, font veya logo varmış gibi davranma.
- Marka varlığı gelene kadar nötr ve profesyonel geçici tema kullan; tüm tokenlar merkezi olarak değiştirilebilir olsun.
- Rastgele gradient, neon, glow, aşırı gölge ve keyfi renk kullanımından kaçın.
- İç operasyon ürünü olduğundan tablo okunabilirliği, bilgi hiyerarşisi ve status renklerinin tutarlılığı önceliklidir.

## Token grupları
- Colors: background, surface, text, muted text, border, primary/accent, success, warning, danger, info.
- Typography: font family, size scale, weight, line height.
- Spacing: tutarlı 4/8 tabanlı ölçek.
- Radius, shadow/elevation, focus ring, z-index, motion/reduced-motion.

Raw hex değerlerini bileşenlere dağıtma. CSS variables/theme config kullan.

## Bileşen standardı
Button (primary/secondary/tertiary/destructive/loading/disabled); input/select/date/number; form field; table; badge; card; dialog/drawer; tabs; breadcrumbs; toast; tooltip; skeleton; empty state; Kanban card/stage.

## Zorunlu çıktılar
1. `docs/design-system.md` token sözlüğü.
2. Theme token uygulaması.
3. Component inventory ve varyant örnekleri.
4. Do/don't kullanım rehberi.
5. Geçici tema kullanılıyorsa açıkça not.

## Kontrol
- Gerçek veri uzunluklarıyla taşma/tablo denemesi.
- Focus görünür; status sadece renkle anlatılmaz.
- Marka asset'i yoksa yeni logo üretme veya markayı taklit etme.
