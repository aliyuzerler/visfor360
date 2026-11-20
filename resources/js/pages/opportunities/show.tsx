import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Textarea } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, Link, useForm } from '@inertiajs/react';
import { toast } from '@/components/ui/toast';

interface HistoryRow {
    id: number;
    field: string;
    from: string | null;
    to: string | null;
    note: string | null;
    actor: string;
    at: string;
}

/** Fırsat detay + karar akışı: mutabakat notu olmadan onay geçmez. */
export default function OpportunityShow({
    opportunity,
    history,
    can,
}: {
    opportunity: {
        id: number;
        title: string;
        status: string;
        service_key: string;
        detection_reason: string | null;
        discussion_note: string | null;
        decision_note: string | null;
        decided_at: string | null;
        decided_by: string | null;
        is_duplicate: boolean;
        duplicate_of_id: number | null;
        customer: { id: number; name: string };
        responsible: { id: number; name: string } | null;
        source_session_id: number | null;
        status_set: string[];
        status_set_is_example: boolean;
    };
    history: HistoryRow[];
    can: { decide: boolean };
}) {
    const form = useForm({ status: 'approved', discussion_note: '', decision_note: '' });

    const decide = (status: 'approved' | 'rejected' | 'deferred') => {
        form.setData('status', status);
        form.patch(route('opportunities.decide', opportunity.id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Karar kaydedildi.'),
            onError: (errors) => toast.error(errors.discussion_note ?? errors.decision_note ?? errors.status ?? 'Karar reddedildi.'),
        });
    };

    return (
        <>
            <Head title={opportunity.title} />
            <AppShell title="Fırsat Havuzu">
                <PageHeader
                    title={opportunity.title}
                    description={`Tespit gerekçesi: ${opportunity.detection_reason ?? '—'}`}
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Fırsat Havuzu', href: route('opportunities.index') },
                        { label: opportunity.title },
                    ]}
                    actions={
                        <>
                            <Badge variant={opportunity.status === 'approved' ? 'success' : opportunity.status === 'rejected' ? 'danger' : 'info'}>
                                {opportunity.status}
                            </Badge>
                            {opportunity.is_duplicate && <Badge variant="warning">Mükerrer</Badge>}
                            {opportunity.status_set_is_example && <Badge variant="info">Örnek statü seti</Badge>}
                        </>
                    }
                />

                <dl className="mb-6 grid gap-x-8 gap-y-3 rounded-xl border border-border bg-surface p-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Müşteri</dt>
                        <dd className="mt-0.5 text-sm">
                            <Link href={route('customers.show', opportunity.customer.id)} className="text-primary hover:underline">
                                {opportunity.customer.name}
                            </Link>
                        </dd>
                    </div>
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Önerilen hizmet</dt>
                        <dd className="mt-0.5 text-sm">{opportunity.service_key}</dd>
                    </div>
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Kaynak Check-Up</dt>
                        <dd className="mt-0.5 text-sm">
                            {opportunity.source_session_id
                                ? <Link href={route('checkups.show', opportunity.source_session_id)} className="text-primary hover:underline">Oturum #{opportunity.source_session_id}</Link>
                                : '—'}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Sorumlu</dt>
                        <dd className="mt-0.5 text-sm">{opportunity.responsible?.name ?? 'Atanmadı'}</dd>
                    </div>
                </dl>

                {opportunity.is_duplicate && (
                    <p className="mb-4 rounded-xl border border-warning/40 bg-warning/10 p-3 text-sm" role="note">
                        Aynı müşteri + hizmet için aktif fırsat mevcut. Mükerrer kararı müşteriye aittir (ADR-0011).
                    </p>
                )}

                {can.decide && (
                    <section aria-label="Karar" className="mb-6 rounded-xl border border-border bg-surface p-4">
                        <h2 className="mb-3 text-base font-semibold">Karar</h2>
                        <p className="mb-3 text-xs text-muted-foreground">
                            Müşteri mutabakat notu zorunludur — boş onay kabul edilmez. Ret/ertelemede gerekçe zorunludur.
                        </p>
                        <form className="max-w-2xl space-y-4" onSubmit={(e) => { e.preventDefault(); decide('approved'); }}>
                            <FormField name="discussion_note" label="Müşteri mutabakat notu (kanıt/görüşme özeti)" error={form.errors.discussion_note} required>
                                {(props) => (
                                    <Textarea {...props} value={form.data.discussion_note} onChange={(e) => form.setData('discussion_note', e.target.value)} />
                                )}
                            </FormField>
                            <FormField name="decision_note" label="Ret/erteleme gerekçesi (onay için opsiyonel)" error={form.errors.decision_note}>
                                {(props) => (
                                    <Textarea {...props} value={form.data.decision_note} onChange={(e) => form.setData('decision_note', e.target.value)} />
                                )}
                            </FormField>
                            <div className="flex flex-wrap gap-2">
                                <Button type="submit" loading={form.processing}>Onayla</Button>
                                <Button type="button" variant="secondary" onClick={() => decide('deferred')} loading={form.processing}>Ertele</Button>
                                <Button type="button" variant="secondary" onClick={() => decide('rejected')} loading={form.processing}>Ret</Button>
                            </div>
                        </form>
                    </section>
                )}

                {opportunity.decided_at && (
                    <p className="mb-4 text-xs text-muted-foreground">
                        Karar: {opportunity.status} · {opportunity.decided_by} · {opportunity.decided_at}
                    </p>
                )}

                <section aria-label="Karar geçmişi">
                    <h2 className="mb-3 text-base font-semibold">Karar geçmişi</h2>
                    {history.length === 0 ? (
                        <EmptyState title="Kayıt yok" description="Statü ve karar değişiklikleri burada izlenir." />
                    ) : (
                        <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                            {history.map((entry) => (
                                <li key={entry.id} className="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 text-sm">
                                    <span>
                                        <Badge variant="info">{entry.field}</Badge>{' '}
                                        <span className="text-xs text-muted-foreground">{entry.from ?? '—'} → {entry.to ?? '—'}</span>
                                        {entry.note && <span className="ml-2 text-xs text-muted-foreground">· {entry.note}</span>}
                                    </span>
                                    <span className="text-xs text-muted-foreground">{entry.actor} · {entry.at}</span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </AppShell>
        </>
    );
}
