import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, Link, router, useForm } from '@inertiajs/react';

interface ProjectRow {
    id: number;
    name: string;
    archived_at: string | null;
    customer: { id: number; name: string } | null;
    service_type: { id: number; label: string; pipeline_id: number } | null;
    current_stage: { id: number; label: string; pipeline_id: number } | null;
    responsible: { id: number; name: string } | null;
}

interface ServiceRow {
    id: number;
    key: string;
    label: string;
    pipeline_id: number;
    pipeline?: { id: number; label: string; is_example: boolean } | null;
}

/**
 * Proje Kanban panosu: seçilen hizmetin pipeline aşamaları kolon,
 * aktif projeler kart olarak yerleşir. Filtre/arama query-level.
 */
export default function ProjectsIndex({
    projects,
    serviceTypes,
    filters,
    can,
}: {
    projects: { data: ProjectRow[]; current_page: number; last_page: number; links: { url: string | null; label: string; active: boolean }[] };
    serviceTypes: ServiceRow[];
    filters: { search: string; service: number | null; status: string };
    can: { create: boolean; update: boolean; archive: boolean };
}) {
    const form = useForm({ search: filters.search ?? '', service: filters.service ?? '', status: filters.status ?? 'active' });

    const selectedService = serviceTypes.find((service) => String(service.id) === String(form.data.service)) ?? serviceTypes[0];
    const activeService = selectedService && String(selectedService.id) === String(form.data.service || selectedService.id) ? selectedService : null;
    const visibleService = form.data.service === '' ? null : serviceTypes.find((service) => String(service.id) === String(form.data.service)) ?? null;

    const apply = () => form.get(route('projects.index'), { preserveState: true });

    const archive = (id: number) => {
        router.patch(route('projects.archive', id), {}, { onSuccess: () => router.reload({ only: ['projects', 'filters'] }) });
    };

    return (
        <>
            <Head title="Projeler" />
            <AppShell title="Projeler">
                <PageHeader
                    title="Projeler"
                    description="Her hizmet kalemi kendi Kanban pipeline'ı ile yürütülür. Aşama geçişleri izin ve kurallarla denetlenir."
                    breadcrumbs={[{ label: 'Ana sayfa', href: route('dashboard') }, { label: 'Projeler' }]}
                    actions={can.create && (
                        <Button size="sm" onClick={() => router.get(route('projects.create'))}>
                            Yeni proje
                        </Button>
                    )}
                />

                <form className="mb-4 flex flex-wrap gap-2" onSubmit={(e) => { e.preventDefault(); apply(); }}>
                    <Input
                        type="search"
                        placeholder="Proje adı ara…"
                        value={form.data.search}
                        onChange={(e) => form.setData('search', e.target.value)}
                        aria-label="Proje ara"
                        className="max-w-xs"
                    />
                    <select
                        className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                        value={form.data.service ?? ''}
                        onChange={(e) => form.setData('service', e.target.value === '' ? '' : Number(e.target.value))}
                        aria-label="Hizmet filtresi"
                    >
                        <option value="">Tüm hizmetler</option>
                        {serviceTypes.map((service) => (
                            <option key={service.id} value={service.id}>{service.label}</option>
                        ))}
                    </select>
                    <select
                        className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                        value={form.data.status}
                        onChange={(e) => form.setData('status', e.target.value)}
                        aria-label="Durum filtresi"
                    >
                        <option value="active">Aktif</option>
                        <option value="archived">Arşivli</option>
                        <option value="">Tümü</option>
                    </select>
                    <Button type="submit" variant="secondary" size="sm">Filtrele</Button>
                </form>

                {visibleService ? (
                    <section aria-label="Kanban panosu" className="mb-6">
                        <header className="mb-3 flex items-center gap-3">
                            <h2 className="text-base font-semibold">{visibleService.label} — Kanban</h2>
                            {visibleService.pipeline?.is_example && (
                                <Badge variant="warning">Örnek şablon — müşteri onayı bekliyor</Badge>
                            )}
                        </header>
                        <KanbanGrid projectId={null} projects={projects.data} serviceId={visibleService.id} canUpdate={can.update} />
                    </section>
                ) : null}

                {projects.data.length === 0 ? (
                    <EmptyState
                        title="Proje bulunamadı"
                        description="Arama ölçütünüze eşleşen proje yok veya henüz proje eklenmemiş."
                        action={can.create && (
                            <Button size="sm" onClick={() => router.get(route('projects.create'))}>
                                İlk projeyi ekle
                            </Button>
                        )}
                    />
                ) : (
                    <ul className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {projects.data.map((project) => (
                            <li key={project.id} className="rounded-xl border border-border bg-surface p-4">
                                <div className="flex items-start justify-between gap-2">
                                    <Link href={route('projects.show', project.id)} className="font-medium text-primary hover:underline">
                                        {project.name}
                                    </Link>
                                    {project.archived_at && <Badge variant="danger">Arşivli</Badge>}
                                </div>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {project.service_type?.label ?? '—'} · {project.current_stage?.label ?? 'Aşama yok'}
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Müşteri: {project.customer?.name ?? '—'} · Sorumlu: {project.responsible?.name ?? '—'}
                                </p>
                                <div className="mt-3 flex gap-2">
                                    <Link href={route('projects.show', project.id)} className="text-sm text-primary hover:underline">
                                        Detay
                                    </Link>
                                    {can.archive && !project.archived_at && (
                                        <Button size="sm" variant="secondary" onClick={() => archive(project.id)}>
                                            Arşivle
                                        </Button>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                )}

                {projects.last_page > 1 && (
                    <nav className="mt-4 flex gap-2" aria-label="Sayfalama">
                        {projects.links.map((link, index) => (
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

function KanbanGrid({
    projects,
    canUpdate,
}: {
    projectId: number | null;
    projects: ProjectRow[];
    serviceId?: number;
    canUpdate: boolean;
}) {
    // Kartlar aşamaya göre gruplanır; sıralama backend'den gelir.
    const byStage = new Map<number, ProjectRow[]>();
    projects.forEach((project) => {
        if (project.current_stage === null) return;
        const list = byStage.get(project.current_stage.id) ?? [];
        list.push(project);
        byStage.set(project.current_stage.id, list);
    });

    return (
        <div className="grid gap-3 overflow-x-auto md:grid-cols-3 xl:grid-cols-5">
            {byStage.size === 0 ? (
                <p className="text-sm text-muted-foreground">Bu filtrede aşama atanmış proje yok.</p>
            ) : (
                [...byStage.entries()].map(([stageId, cards]) => (
                    <div key={stageId} className="rounded-xl border border-border bg-surface p-3">
                        <h3 className="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            {cards[0]?.current_stage?.label ?? 'Aşama'} ({cards.length})
                        </h3>
                        <ul className="space-y-2">
                            {cards.map((card) => (
                                <li key={card.id} className="rounded-lg border border-border bg-background p-2.5">
                                    <Link href={route('projects.show', card.id)} className="text-sm font-medium text-primary hover:underline">
                                        {card.name}
                                    </Link>
                                    <p className="mt-0.5 text-xs text-muted-foreground">{card.customer?.name ?? '—'}</p>
                                </li>
                            ))}
                        </ul>
                    </div>
                ))
            )}
        </div>
    );
}
