# ADR-0009: OKR formülleri, ağırlık ve varsayılan hedef eşleştirmesi (karar bekleniyor)

## Bağlam
12 hedefin gerçek adları ve ilerleme formülleri kaynak PDF'de yok; görev-hedef ağırlıklandırma belirsiz.

## Karar
- 12 slot sabit (goal-01..goal-12); içerik müşteri girdisi.
- İlerleme: GoalCalculationStrategy; yalnız calculation_config.approved=true + fixture doğrulaması ile hesap. DEMO formül (linked_tasks_ratio.v1) yalnız test fixture'ı.
- Formül yok → progress null + needs-configuration (uydurma yüzde yasak).
- goal_task_links.weight null → ağırlık kararı gelene kadar hesaba katılmaz.
- Departman/hizmet → varsayılan hedef mapping yönetilebilir; kullanıcı düzeltme yetkisi müşteri kararı.

## Sonuç
Müşteri formülü gelene kadar hiçbir hedef skoru raporlanmaz.
