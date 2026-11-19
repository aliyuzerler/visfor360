import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';
import { toast } from '@/components/ui/toast';

interface Snapshot {
    progress_value: string | null;
    status: string;
    formula_version: string | null;
    source_task_count: number;
    source_done_count: number;
    calculated_at: string | null;
}

/** Hedef detay: ilerleme durumu (needs-configuration görünümü), görev bağları, snapshot geçmişi. */
export default function GoalShow({
    goal,
    linkedTasks,
    availableTasks,
    latestSnapshot,
    snapshotHistory,
    can,
}: {
    goal: {
        id: number;
        slot_key: string;
        label: string | null;
        description: string | null;
        owner: string | null;
        period: string | null;
        baseline: string | null;
        target: string | null;
        unit: string | null;
        is_placeholder: boolean;
        calculation_status: 'calculated' | 'needs_configuration';
    };
    linkedTasks: { id: number; task_id: number; title: string; status: string | null; weight: string | null }[];
    availableTasks: { id: number; title: string }[];
    latestSnapshot: Snapshot | null;
    snapshotHistory: { id: number; status: string; progress_value: string | null; formula_version: string | null; calculated_at: string | null }[];
    can: { update: boolean; linkTask: boolean };
}) {
    const linkForm = useForm({ task_id: '' });

    const link = (e: React.FormEvent) => {
        e.preventDefault();
        linkForm.post(route('goals.tasks.link', goal.id), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Görev bağlandı.'); linkForm.reset(); },
            onError: () => toast.error('Bağlama yetkiniz yok veya görev geçersiz.'),
        });
    };

    const takeSnapshot = () => {
        router.patch(route('goals.snapshot', goal.id), {}, {
            preserveScroll: true,
            onSuccess: () => toast.success('Snapshot alındı.'),
        });
    };

    return (
        <>
            <Head title={goal.slot_key} />
            <AppShell title="Stratejik Hedefler">
                <PageHeader
                    title={`${goal.slot_key}${goal.label ? ` — ${goal.label}` : ''}`}
                    description={goal.description ?? 'Hedef tanımı müşteri kararı bekliyor.'}
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Stratejik Hedefler', href: route('goals.index') },
                        { label: goal.slot_key },
                    ]}
                    actions={
                        <>
                            {goal.is_placeholder && <Badge variant="warning">Örnek/Boş</Badge>}
                            <Button size="sm" variant="secondary" onClick={takeSnapshot}>
                                Snapshot al
                            </Button>
                        </>
                    }
                />

                <dl className="mb-6 grid gap-x-8 gap-y-3 rounded-xl border border-border bg-surface p-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div><dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Sorumlu</dt><dd className="mt-0.5 text-sm">{goal.owner ?? '—'}</dd></div>
                    <div><dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Dönem</dt><dd className="mt-0.5 text-sm">{goal.period ?? '—'}</dd></div>
                    <div><dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Baz / Hedef</dt><dd className="mt-0.5 text-sm">{goal.baseline ?? '—'} → {goal.target ?? '—'} {goal.unit ?? ''}</dd></div>
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">İlerleme</dt>
                        <dd className="mt-0.5 text-sm">
                            {goal.calculation_status === 'calculated' && latestSnapshot !== null && latestSnapshot.progress_value !== null
                                ? `%${latestSnapshot.progress_value}`
                                : <Badge variant="warning">Konfigürasyon bekliyor — formül onayı olmadan yüzde üretilmez</Badge>}
                        </dd>
                    </div>
                </dl>

                {can.linkTask && (
                    <section aria-label="Görev bağla" className="mb-6 rounded-xl border border-border bg-surface p-4">
                        <h2 className="mb-3 text-base font-semibold">Görev bağla</h2>
                        <form className="flex flex-wrap gap-2" onSubmit={link}>
                            <select
                                className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                                value={linkForm.data.task_id}
                                onChange={(e) => linkForm.setData('task_id', e.target.value)}
                                aria-label="Bağlanacak görev"
                                required
                            >
                                <option value="">Görev seçin…</option>
                                {availableTasks.map((task) => (
                                    <option key={task.id} value={task.id}>{task.title}</option>
                                ))}
                            </select>
                            <Button type="submit" size="sm" loading={linkForm.processing}>Bağla</Button>
                        </form>
                    </section>
                )}

                <section aria-label="Bağlı görevler" className="mb-6">
                    <h2 className="mb-3 text-base font-semibold">Bağlı görevler ({linkedTasks.length})</h2>
                    {linkedTasks.length === 0 ? (
                        <EmptyState title="Bağlı görev yok" description="Görev-hedef bağlantıları bu listede izlenir." />
                    ) : (
                        <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                            {linkedTasks.map((task) => (
                                <li key={task.id} className="flex items-center justify-between px-4 py-2.5 text-sm">
                                    <span className="font-medium">{task.title}</span>
                                    <span className="text-xs text-muted-foreground">{task.status ?? '—'}</span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section aria-label="Snapshot geçmişi">
                    <h2 className="mb-3 text-base font-semibold">İlerleme snapshot geçmişi</h2>
                    {snapshotHistory.length === 0 ? (
                        <EmptyState title="Snapshot yok" description="Hesaplar snapshot ile izlenebilir; formül sürümü kaydedilir." />
                    ) : (
                        <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                            {snapshotHistory.map((snapshot) => (
                                <li key={snapshot.id} className="flex items-center justify-between px-4 py-2.5 text-sm">
                                    <span>
                                        <Badge variant={snapshot.status === 'calculated' ? 'success' : 'warning'}>{snapshot.status}</Badge>
                                        {snapshot.status === 'calculated' && snapshot.progress_value !== null && (
                                            <span className="ml-2 font-medium">%{snapshot.progress_value}</span>
                                        )}
                                        {snapshot.formula_version && (
                                            <span className="ml-2 text-xs text-muted-foreground">formül: {snapshot.formula_version}</span>
                                        )}
                                    </span>
                                    <span className="text-xs text-muted-foreground">{snapshot.calculated_at}</span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </AppShell>
        </>
    );
}
