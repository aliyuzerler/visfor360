# ADR-0008: Görev atama modeli ve ek özellikler (karar bekleniyor)

## Bağlam
Tek atama mı çoklu atama mı? Yorum/ek/alt görev/efor/tekrar eden görev isteniyor mu?

## Karar
- task_assignments tablosu çoklu atamaya hazır; varsayılan tek atama (is_primary=true).
- Policy: sorumlu kendi görevini ilerletebilir; atama için tasks.update izni.
- Yorum/ek/alt görev/efor/tekrar: müşteri onayı olmadan eklenmez.

## Sonuç
Genişletilebilir model; varsayım açıkça işaretli.
