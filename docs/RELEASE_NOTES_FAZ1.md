# Visfor360 — Faz 1 Release Notes (Taslak)

> Bu belge Faz 1 kapanışında müşteriye sunulacak taslaktır; müşteri kararları (P0) netleşince güncellenir.

## Kapsamdaki modüller
1. **Kimlik ve Yetki** — e-posta/parola girişi, oturum koruması (parola değişimi eski oturumları kapatır), rol/izin matrisi (12+ modül), departmanlar, append-only audit log.
2. **CRM** — müşteri sicili, yetkililer, arama/filtre/sayfalama, arşivleme (kalıcı silme yok), dinamik sicil alanları (8 tip allowlist, tip değişikliği koruması).
3. **Evrak Deposu** — private storage, rastgele anahtar, MIME/boyut allowlist, sürüm zinciri (supersede), eksik/süresi dolmuş/yaklaşan durumları.
4. **Proje Yönetimi** — hizmet kataloğu, her hizmet için ayrı Kanban şablonu, aşama geçiş kuralları (backend'de zorlanır), geçmiş + audit.
5. **Görevler** — benim/ekip görünümleri, gecikmiş hesabı (tamamlanan hariç), durum/atama/tarih geçmişi, hedef bağlantıları.
6. **Strateji (OKR)** — 5 yıllık vizyon + 12 hedef slotu (içerik müşteri kararı), onaylı formül olmadan ilerleme yüzdesi üretilmez, snapshot geçmişi.
7. **Finans** — gelir/gider (tahmini/gerçekleşen), durum makinesi, düzeltme zinciri, P&L ve nakit/runway ayrı raporlar; formül onayı olmadan hesap yok.
8. **Dashboard** — role-aware KPI'lar, yalnız gerçek aggregate sorgular; izni olmayan modülün verisi üretilmez.

## Bilinen sınırlar
- Resmi muhasebe/fatura aracı değildir (e-fatura, vergi beyanı kapsam dışı — SRS).
- Check-Up, Fırsat Havuzu, Çözüm Ortağı, Müşteri Portalı ve API Faz 2'de.
- Virüs tarama servisi henüz yok — eklenmesi önerilir (opsiyon notu, docs/RELEASE_NOTES_FAZ1.md).
- Bildirim kanalları (e-posta/SMS) müşteri onayı olmadan etkinleştirilmez.

## Eğitim ve kullanıcı kılavuzu taslağı (özet)
- Yönetici: kullanıcı/rol/departman kurulumu, izin matrisi, audit inceleme.
- Satış: müşteri + evrak + proje açılışı.
- Ekip: görev akışı (benim görevlerim), aşama geçişi.
- Yönetim: hedef slotları, finans raporları, dashboard.

## Açık müşteri kararları (P0)
1. Müşteri türleri ve zorunlu sicil alanları (Prompt 04).
2. Evrak türleri, zorunluluklar, uyarı periyotları (Prompt 05).
3. Hizmet kataloğu, aşama adları, geçiş kuralları (Prompt 06).
4. Görev atama modeli: tek/çoklu (Prompt 07 — ADR-0008).
5. 12 hedefin gerçek adları + ilerleme formülleri (Prompt 08 — ADR-0009).
6. Finans onay akışı, runway varsayımları, para birimi davranışı (Prompt 09 — ADR-0005).
7. Çok departmanlı hizmet modeli (Prompt 06 — ADR-0007).
