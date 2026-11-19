import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { route } from '@/lib/route';
import { Head, Link, router, useForm } from '@inertiajs/react';

interface TaskRow {
    id: number;
    title: string;
    status: string;
    priority: string;
    due_date: string | null;
    overdue: boolean;
    assignees: string[];
    customer: { id: number; name: string } | null;
    project: { id: number; name: string } | null;
    department: { id: number; name: string } | null;
}

const statusVariant: Record<string, 'info' | 'success' | 'danger' | 'warning'> = {
    open: 'info',
    in_progress: 'warning',
    done: 'success',
    cancelled: 'danger',
};

/**
 * Gorevler: Benim gorevlerim / Ekip gorevleri sekmeleri + gecikmis filtresi.
 * Sayilar gercek query'lerden gelir; sayfalama query-level.
 */
export default function TasksIndex({
    tasks,
    view,
    filters,
    counts,
    can,
}: {
    tasks: { data: TaskRow[]; current_page: number; last_page: number; links: { url: string | null; label: string; active: boolean }[]; total: number };
    view: 'my' | 'team';
    filters: { search: string; status: string };
    counts: { my: number; team: number; overdue: number };
    can: { create: boolean; update: boolean; archive: boolean };
}) {
    const form = useForm({ search: filters.search ?? '', status: filters.status ?? '' });

    const apply = (extra: Record<string, string> = {}) => {
        router.get(route('tasks.index'), { view, ...form.data, ...extra }, { preserveState: true });
    };

    const switchView = (next: 'my' | 'team') => {
        router.get(route('tasks.index'), { view: next }, { preserveState: true });
    };

    return (
        <>
            <Head title="Görevler" />
            <AppShell title="Görevler">
                <PageHeader
                    title="Görevler"
                    description="Ekip iş yönetimi: görevler atanır, ilerletilir, geçmişi saklanır; stratejik hedeflerle ilişkilendirilir."
                    breadcrumbs={[{ label: 'Ana sayfa', href: route('dashboard') }, { label: 'Görevler' }]}
                    actions={can.create && (
                        <Button size="sm" onClick={() => router.get(route('tasks.create'))}>
                            Yeni görev
                        </Button>
                    )}
                />

                <div className="mb-4 flex flex-wrap gap-2" role="tablist" aria-label="Görev görünümleri">
                    <Button size="sm" variant={view === 'my' ? 'primary' : 'secondary'} onClick={() => switchView('my')} aria-selected={view === 'my'}>
                        Benim görevlerim ({counts.my})
                    </Button>
                    <Button size="sm" variant={view === 'team' ? 'primary' : 'secondary'} onClick={() => switchView('team')} aria-selected={view === 'team'}>
                        Ekip görevleri ({counts.team})
                    </Button>
                    <Button
                        size="sm"
                        variant="secondary"
                        onClick={() => apply({ status: filters.status === 'overdue' ? '' : 'overdue' })}
                        aria-pressed={filters.status === 'overdue'}
                    >
                        Gecikmiş ({counts.overdue})
                    </Button>
                </div>

                <form className="mb-4 flex flex-wrap gap-2" onSubmit={(e) => { e.preventDefault(); apply(); }}>
                    <Input
                        type="search"
                        placeholder="Görev ara…"
                        value={form.data.search}
                        onChange={(e) => form.setData('search', e.target.value)}
                        aria-label="Görev ara"
                        className="max-w-xs"
                    />
                    <select
                        className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                        value={form.data.status}
                        onChange={(e) => form.setData('status', e.target.value)}
                        aria-label="Durum filtresi"
                    >
                        <option value="">Tüm durumlar</option>
                        <option value="open">Açık</option>
                        <option value="in_progress">Devam ediyor</option>
                        <option value="done">Tamamlandı</option>
                        <option value="cancelled">İptal</option>
                    </select>
                    <Button type="submit" variant="secondary" size="sm">Filtrele</Button>
                </form>

                {tasks.data.length === 0 ? (
                    <EmptyState
                        title="Görev bulunamadı"
                        description={view === 'my'
                            ? 'Size atanmış görev yok. Ekip görevlerine göz atabilir veya yeni görev oluşturabilirsiniz.'
                            : 'Bu filtreyle eşleşen görev yok.'}
                        action={can.create && (
                            <Button size="sm" onClick={() => router.get(route('tasks.create'))}>
                                İlk görevi ekle
                            </Button>
                        )}
                    />
                ) : (
                    <Table>
                        <THead>
                            <TR>
                                <TH scope="col">Görev</TH>
                                <TH scope="col">Durum</TH>
                                <TH scope="col">Öncelik</TH>
                                <TH scope="col">Son tarih</TH>
                                <TH scope="col">Sorumlu</TH>
                                <TH scope="col">Bağlam</TH>
                            </TR>
                        </THead>
                        <TBody>
                            {tasks.data.map((task) => (
                                <TR key={task.id}>
                                    <TD>
                                        <Link href={route('tasks.show', task.id)} className="font-medium text-primary hover:underline">
                                            {task.title}
                                        </Link>
                                    </TD>
                                    <TD><Badge variant={statusVariant[task.status] ?? 'info'}>{task.status}</Badge></TD>
                                    <TD><Badge variant={task.priority === 'urgent' ? 'danger' : 'info'}>{task.priority}</Badge></TD>
                                    <TD className="text-sm">
                                        {task.overdue
                                            ? <span className="font-medium text-danger">{task.due_date} (gecikmiş)</span>
                                            : task.due_date ?? '—'}
                                    </TD>
                                    <TD className="text-sm text-muted-foreground">{task.assignees.join(', ') || '—'}</TD>
                                    <TD className="text-xs text-muted-foreground">
                                        {task.customer?.name ?? '—'}{task.project ? ` · ${task.project.name}` : ''}
                                    </TD>
                                </TR>
                            ))}
                        </TBody>
                    </Table>
                )}

                {tasks.last_page > 1 && (
                    <nav className="mt-4 flex gap-2" aria-label="Sayfalama">
                        {tasks.links.map((link, index) => (
                            <button
                                key={index}
                                type="button"
                                disabled={!link.url}
                                onClick={() => router.get(link.url ?? '', {}, { preserveState: true })}
                                className="rounded-md border border-border px-3 py-1 text-sm disabled:opacity-40"
                                aria-current={link.active ? 'page' : undefined}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </nav>
                )}
            </AppShell>
        </>
    );
}
