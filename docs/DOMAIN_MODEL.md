# Domain Modeli ve İş Kuralları

| Domain | Temel nesneler | Teyit edilmesi gereken ilişki/kurallar |
|---|---|---|
| Identity | User, Role, Permission, UserDepartment | Kullanıcı çok departmanlı mı? |
| Customer | Customer/Organization, Contact, CustomerType | Çoklu yetkili/portal user olacak mı? |
| Custom Fields | CustomFieldDefinition, CustomFieldValue | Tip, required, doğrulama, filtre, rapor ve izin |
| Documents | DocumentType, Document, DocumentVersion, DocumentRequest | Zorunluluk, geçerlilik, onay, saklama |
| Organization | Department, Membership | Ekip ve görünürlük kapsamı |
| Services | ServiceType, Pipeline, PipelineStage, TransitionRule | Her hizmetin aşaması ve geçişi |
| Projects | Project, ProjectStageHistory | Bir projede birden fazla hizmet olabilir mi? |
| Tasks | Task, TaskAssignment, TaskComment, Attachment, History | Tek/çok atanan, alt görev, efor, tekrar |
| Strategy | Vision, StrategicGoal, KeyResult?, GoalTaskLink, ProgressSnapshot | 12 hedef formülü ve katkı ağırlığı |
| Finance | FinancialEntry, Category, CashForecast, BalanceSnapshot | P&L, nakit, tahsilat, para birimi, runway |
| Check-Up | Template, Question, Option, Rule, Session, Answer, Result, RoadmapItem | Puanlama, AND/OR, sürüm, snapshot |
| Opportunity | Opportunity, Decision, Conversion | İnsan onayı ve tekil dönüşüm |
| Partners | Partner, Agreement, Referral, Commission, Payout | Matrah, oran, onay ve ödeme |
| Cross-cutting | AuditEvent, Notification, ApiCredential, WebhookEndpoint, Delivery | Retention, scope, retry |

## Önemli kurallar
- Firma silinmesi yerine arşivleme varsayılandır; ilişkili kayıt geçmişi korunur.
- Eksik evrak ve süresi geçmiş evrak farklı statüdür.
- Hizmet türü bir pipeline şablonuna bağlanır; aşama değişikliği actor/time/history kaydeder.
- Check-Up template/rule sürümlenir; aynı cevap + aynı sürüm aynı sonucu üretir.
- Kural motoru deklaratif allowlist koşulları kullanır; eval/dinamik kod çalıştırmaz.
- Check-Up fırsat önerir; otomatik iş oluşturmaz.
- Fırsat dönüşümü transaction ve idempotency ile çift kayıt engeller.
- OKR formülü onaylanmadan yüzdelik ilerleme hesaplanmaz.
- Finans tahmini/gerçekleşeni ayırır; resmi muhasebe değildir; düzeltme/iptal auditlidir.
- Portal her kaynak nesnede müşteri sahipliğini doğrular.
