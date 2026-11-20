import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { toast } from '@/components/ui/toast';

interface StageRow {
    id: number;
    key: string;
    label: string;
    sort_order: number;
    is_active: boolean;
    is_terminal: boolean;
}

interface HistoryRow {
    id: number;
    from: string | null;
    to: string;
    actor: string;
    at: string;
}

/**
 * Proje detay: aşama geçiş butonları (klavye erişilebilir form submit),
 * aşama geçmişi (actor/old/new/time), arşivleme.
 */
export default function ProjectShow({
    project,
    history,
    can,
}: {
    project: {
        id: number;
        name: string;
        description: string | null;
        start_date: string | null;
        due_date: string | null;
        archived_at: string | null;
        customer: { id: number; name: string } | null;
        service_type: { id: number; label: string } | null;
        department: { id: number; name: string } | null;
        responsible: { id: number; name: string } | null;
        current_stage_id: number | null;
        stages: StageRow[];
        pipeline_label: string;
        pipeline_is_example: boolean;
        source_opportunity: { id: number; title: string } | null;
    };
    history: HistoryRow[];
    can: { update: boolean; archive: boolean };
}) {
    const form = useForm({ stage_id: '' as string });

    const move = (stageId: number) => {
        form.setData('stage_id', String(stageId));
        form.patch(route('projects.stage', project.id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Aşama güncellendi.'),
            onError: (errors) => toast.error(errors.stage_id ?? 'Geçiş reddedildi.'),
        });
    };

    const archive = () => {
        router.patch(route('projects.archive', project.id), {}, {
            onSuccess: () => toast.success('Proje arşivlendi.'),
        });
    };

    return (
        <>
            <Head title={project.name} />
            <AppShell title={project.name}>
                <PageHeader
                    title={project.name}
                    description={`${project.service_type?.label ?? '—'} · Pipeline: ${project.pipeline_label}${project.pipeline_is_example ? ' (ÖRNEK — müşteri onayı bekliyor)' : ''}`}
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Projeler', href: route('projects.index') },
                        { label: project.name },
                    ]}
                    actions={
                        <>
                            {project.archived_at && <Badge variant="danger">Arşivli</Badge>}
                            {can.update && (
                                <Button size="sm" variant="secondary" onClick={() => router.get(route('projects.edit', project.id))}>
                                    Düzenle
                                </Button>
                            )}
                            {can.archive && !project.archived_at && (
                                <Button size="sm" variant="secondary" onClick={archive}>
                                    Arşivle
                                </Button>
                            )}
                        </>
                    }
                />

                {project.source_opportunity && (
                    <p className="mb-4 text-xs text-muted-foreground" role="note">
                        Kaynak fırsat:{' '}
                        <Link href={route('opportunities.show', project.source_opportunity.id)} className="text-primary hover:underline">
                            {project.source_opportunity.title}
                        </Link>
                    </p>
                )}

                <dl className="mb-6 grid gap-x-8 gap-y-3 rounded-xl border border-border bg-surface p-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Müşteri</dt>
                        <dd className="mt-0.5 text-sm">
                            {project.customer
                                ? <Link href={route('customers.show', project.customer.id)} className="text-primary hover:underline">{project.customer.name}</Link>
                                : '—'}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Departman</dt>
                        <dd className="mt-0.5 text-sm">{project.department?.name ?? '—'}</dd>
                    </div>
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Sorumlu</dt>
                        <dd className="mt-0.5 text-sm">{project.responsible?.name ?? '—'}</dd>
                    </div>
                    <div>
                        <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Tarihler</dt>
                        <dd className="mt-0.5 text-sm">{project.start_date ?? '—'} → {project.due_date ?? '—'}</dd>
                    </div>
                </dl>

                <section aria-label="Aşamalar" className="mb-6">
                    <h2 className="mb-3 text-base font-semibold">Aşama geçişi</h2>
                    <p className="mb-3 text-xs text-muted-foreground">
                        Geçişler backend kurallarıyla denetlenir (pipeline uyumu, terminal, zorunlu alan/evrak). Kart sürükleme yerine
                        her aşama bir form butonudur — klavye ile erişilebilir.
                    </p>
                    <ol className="flex flex-wrap gap-2">
                        {project.stages.filter((stage) => stage.is_active).map((stage) => {
                            const isCurrent = stage.id === project.current_stage_id;
                            return (
                                <li key={stage.id}>
                                    <form onSubmit={(e) => { e.preventDefault(); if (!isCurrent) move(stage.id); }}>
                                        <button
                                            type="submit"
                                            disabled={isCurrent || form.processing || project.archived_at !== null}
                                            className={`rounded-lg border px-3 py-1.5 text-sm transition-colors ${
                                                isCurrent
                                                    ? 'border-primary bg-primary/10 font-semibold text-primary'
                                                    : 'border-border bg-surface hover:border-primary'
                                            } disabled:cursor-not-allowed`}
                                            aria-current={isCurrent ? 'step' : undefined}
                                            title={stage.is_terminal ? 'Terminal aşama' : stage.label}
                                        >
                                            {stage.label}
                                            {stage.is_terminal && ' ⏹'}
                                        </button>
                                    </form>
                                </li>
                            );
                        })}
                    </ol>
                    {form.errors.stage_id && (
                        <p className="mt-2 text-xs font-medium text-danger" role="alert">{form.errors.stage_id}</p>
                    )}
                </section>

                <section aria-label="Aşama geçmişi">
                    <h2 className="mb-3 text-base font-semibold">Aşama geçmişi</h2>
                    {history.length === 0 ? (
                        <EmptyState title="Kayıt yok" description="Aşama geçişleri burada izlenebilir listelenir." />
                    ) : (
                        <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                            {history.map((entry) => (
                                <li key={entry.id} className="flex items-center justify-between gap-3 px-4 py-2.5 text-sm">
                                    <span>
                                        <Badge variant="info">{entry.from ?? 'başlangıç'}</Badge>
                                        <span aria-hidden className="mx-1.5">→</span>
                                        <Badge variant="success">{entry.to}</Badge>
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
