# Prompt 11 - Check-Up Şablonları ve Deklaratif Kural Motoru

## Ön koşul ve bağlam
Faz 2 başlar. Müşteri Check-Up soru setini, cevap türlerini, puanlama ve cevap-hizmet karar tablosunu teslim etmiş olmalı. Bu girdiler yoksa üretim motorunu tamamlamadan yalnız generic form builder prototipi yap ve blokajı raporla.

## Kullanılacak skill'ler
`$visfor-domain-architecture` (`skills/architecture/SKILL.md`), `$visfor-application-security` (`skills/security/SKILL.md`), `$visfor-test-engineering` (`skills/testing/SKILL.md`), `$visfor-enterprise-ui-ux` (`skills/enterprise-ui-ux/SKILL.md`)

## Geliştirme görevleri
1. CheckupTemplate, Question, Option, Rule, Session, Answer, Result ve RoadmapItem modellerini oluştur.
2. Template ve rule set için versioning uygula; tamamlanmış oturum exact version snapshot saklasın.
3. Soru tipleri ve validation'ı müşteri kararına göre uygula.
4. Condition group AND/OR, izinli operator ve hizmet önerisi mapping'i olan deklaratif rule engine kur.
5. Rule conflict, priority, missing answer ve invalid answer davranışını açıkça uygula.
6. Kural test tablosunu müşteri tarafından sağlanan input-output örneklerinden oluştur.
7. Check-Up form UI, taslak/kaydet/tamamla akışını yap.
8. Sonuçta hangi cevap/kural nedeniyle hangi hizmetin önerildiğini explainability alanıyla göster.
9. Kural çalıştırma sonucu sadece Opportunity önerisi üretmeli; proje/task üretmemeli.

## Beklenen çıktılar
Versiyonlu Check-Up form ve soru bankası, rule engine, result snapshot, rule trace/explanation, test fixtures ve test raporu.

## Sınırlar ve güvenlik kuralları
`eval`, dynamic code execution, user-provided SQL, shell veya executable expressions yasaktır. Müşterinin rule matrix'i olmadan gerçek hizmet öneri kuralı yazma. Aynı cevap ve rule version deterministik olmalı.

## İş bitiminde raporla
- Değişen dosyalar ve migration'lar.
- Çalıştırılan gerçek komutlar ve sonuçları.
- Bilinen sorunlar, açık müşteri kararları ve riskler.
- Güncellenen docs/ADR/test senaryoları.
