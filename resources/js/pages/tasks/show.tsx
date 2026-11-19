import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { toast } from '@/components/ui/toast';

interface HistoryRow {
    id: number;
    field: string;
    from: string | null;
    to: string | null;
    actor: string;
    at: string;
}

/** Gorev detay: durum degistirme, yeniden atama, gecmis. */
export default function TaskShow({
    task,
    history,
    users,
    can,
}: {
    task: {
        id: number;
        title: string;
        description: string | null;
        status: string;
        priority: string;
        start_date: string | null;
        due_date: string | null;
        overdue: boolean;
        assignees: { id: number; name: string }[];
        customer: { id: number; name: string } | null;
        project: { id: number; name: string } | null;
        department: { id: number; name: string } | null;
    };
    history: HistoryRow[];
    users: { id: number; name: string }[];
    can: { update: boolean; assign: boolean; archive: boolean };
}) {
    const statusForm = useForm({ status: task.status });
    const assignForm = useForm({ user_id: '' });

    const changeStatus = (status: string) => {
        statusForm.setData('status', status);
        statusForm.patch(route('tasks.status', task.id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Durum güncellendi.'),
            onError: () => toast.error('Durum değişikliği reddedildi.'),
        });
    };

    const reassign = (e: React.FormEvent) => {
        e.preventDefault();
        assignForm.patch(route('tasks.assign', task.id), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Atama güncellendi.'); assignForm.reset(); },
            onError: () => toast.error('Atama başarısız.'),
        });
    };

    const archive = () => {
        router.patch(route('tasks.archive', task.id), {}, {
            onSuccess: () => router.get(route('tasks.index')),
        });
    };

    return (
        <>
            <Head title={task.title} />
            <AppShell title={task.title}>
                <PageHeader
                    title={task.title}
                    description={task.description ?? undefined}
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Görevler', href: route('tasks.index') },
                        { label: task.title },
                    ]}
                    actions={
                        <>
                            <Badge variant={task.overdue ? 'danger' : 'info'}>{task.overdue ? 'Gecikmiş' : task.status}</Badge>
                            {can.update && (
                                <Button size="sm" variant="secondary" onClick={() => router.get(route('tasks.edit', task.id))}>
                                    Düzenle
                                </Button>
                            )}
                            {can.archive && (
                                <Button size="sm" variant="secondary" onClick={archive}>
                                    Arşivle
                                </Button>
                            )}
                        </>
                    }
                />

                <dl className="mb-6 grid gap-x-8 gap-y-3 rounded-xl border border-border bg-surface p-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Sorumlu</dt>
                        <dd className="mt-0.5 text-sm">{task.assignees.map((a) => a.name).join(', ') || 'Atanmadı'}</dd>
                    </div>
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Tarihler</dt>
                        <dd className="mt-0.5 text-sm">{task.start_date ?? '—'} → {task.due_date ?? '—'}</dd>
                    </div>
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Müşteri / Proje</dt>
                        <dd className="mt-0.5 text-sm">
                            {task.customer
                                ? <Link href={route('customers.show', task.customer.id)} className="text-primary hover:underline">{task.customer.name}</Link>
                                : '—'}
                            {task.project && <> · <Link href={route('projects.show', task.project.id)} className="text-primary hover:underline">{task.project.name}</Link></>}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Departman</dt>
                        <dd className="mt-0.5 text-sm">{task.department?.name ?? '—'}</dd>
                    </div>
                </dl>

                {can.update && (
                    <section aria-label="Durum" className="mb-6">
                        <h2 className="mb-3 text-base font-semibold">Durum</h2>
                        <div className="flex flex-wrap gap-2">
                            {['open', 'in_progress', 'done', 'cancelled'].map((status) => (
                                <button
                                    key={status}
                                    type="button"
                                    onClick={() => changeStatus(status)}
                                    disabled={task.status === status || statusForm.processing}
                                    className={`rounded-lg border px-3 py-1.5 text-sm ${
                                        task.status === status
                                            ? 'border-primary bg-primary/10 font-semibold text-primary'
                                            : 'border-border bg-surface hover:border-primary'
                                    } disabled:cursor-not-allowed`}
                                    aria-current={task.status === status ? 'step' : undefined}
                                >
                                    {status}
                                </button>
                            ))}
                        </div>
                    </section>
                )}

                {can.assign && (
                    <section aria-label="Atama" className="mb-6">
                        <h2 className="mb-3 text-base font-semibold">Yeniden atama</h2>
                        <form className="flex flex-wrap gap-2" onSubmit={reassign}>
                            <select
                                className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                                value={assignForm.data.user_id}
                                onChange={(e) => assignForm.setData('user_id', e.target.value)}
                                aria-label="Yeni sorumlu"
                                required
                            >
                                <option value="">Kullanıcı seçin…</option>
                                {users.map((user) => (
                                    <option key={user.id} value={user.id}>{user.name}</option>
                                ))}
                            </select>
                            <Button type="submit" size="sm" loading={assignForm.processing}>Ata</Button>
                        </form>
                    </section>
                )}

                <section aria-label="Değişiklik geçmişi">
                    <h2 className="mb-3 text-base font-semibold">Değişiklik geçmişi</h2>
                    {history.length === 0 ? (
                        <EmptyState title="Kayıt yok" description="Durum, atama ve tarih değişiklikleri burada listelenir." />
                    ) : (
                        <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                            {history.map((entry) => (
                                <li key={entry.id} className="flex items-center justify-between gap-3 px-4 py-2.5 text-sm">
                                    <span>
                                        <Badge variant="info">{entry.field}</Badge>{' '}
                                        <span className="text-xs text-muted-foreground">
                                            {entry.from ?? '—'} → {entry.to ?? '—'}
                                        </span>
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
