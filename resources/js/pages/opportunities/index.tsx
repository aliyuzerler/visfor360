import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, Link, router, useForm } from '@inertiajs/react';

interface OpportunityRow {
    id: number;
    title: string;
    status: string;
    is_duplicate: boolean;
    service_key: string;
    customer: { id: number; name: string };
    responsible: { id: number; name: string } | null;
    created_at: string | null;
}

const statusVariant: Record<string, 'info' | 'success' | 'danger' | 'warning'> = {
    review_pending: 'info',
    customer_discussion: 'warning',
    approved: 'success',
    rejected: 'danger',
    deferred: 'warning',
};

/** Fırsat Havuzu: Check-Up tespitlerinin insan incelemesi bekleyen havuzu. */
export default function OpportunitiesIndex({
    opportunities,
    filters,
    can,
}: {
    opportunities: { data: OpportunityRow[]; last_page: number; links: { url: string | null; label: string; active: boolean }[] };
    filters: Record<string, string>;
    can: { decide: boolean };
}) {
    const form = useForm({ status: filters.status ?? '', search: filters.search ?? '', duplicates: filters.duplicates ?? '' });

    const apply = () => router.get(route('opportunities.index'), form.data, { preserveState: true });

    return (
        <>
            <Head title="Fırsat Havuzu" />
            <AppShell title="Fırsat Havuzu">
                <PageHeader
                    title="Fırsat Havuzu"
                    description="Check-Up tespitleri insan incelemesi ve müşteri mutabakatından sonra işe dönüşür."
                    breadcrumbs={[{ label: 'Ana sayfa', href: route('dashboard') }, { label: 'Fırsat Havuzu' }]}
                />

                <form className="mb-4 flex flex-wrap gap-2" onSubmit={(e) => { e.preventDefault(); apply(); }}>
                    <Input
                        type="search"
                        placeholder="Fırsat ara…"
                        value={form.data.search}
                        onChange={(e) => form.setData('search', e.target.value)}
                        aria-label="Fırsat ara"
                        className="max-w-xs"
                    />
                    <select
                        className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                        value={form.data.status}
                        onChange={(e) => form.setData('status', e.target.value)}
                        aria-label="Durum filtresi"
                    >
                        <option value="">Tüm durumlar</option>
                        {['review_pending', 'customer_discussion', 'approved', 'rejected', 'deferred'].map((status) => (
                            <option key={status} value={status}>{status}</option>
                        ))}
                    </select>
                    <Button type="submit" variant="secondary" size="sm">Filtrele</Button>
                </form>

                {opportunities.data.length === 0 ? (
                    <EmptyState
                        title="Fırsat yok"
                        description="Havuzda fırsat bulunmuyor. Check-Up sonuçlarını yol haritasına aktardığınızda öneriler buraya düşer."
                    />
                ) : (
                    <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                        {opportunities.data.map((opportunity) => (
                            <li key={opportunity.id} className="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                <div>
                                    <Link href={route('opportunities.show', opportunity.id)} className="font-medium text-primary hover:underline">
                                        {opportunity.title}
                                    </Link>
                                    <p className="text-xs text-muted-foreground">
                                        {opportunity.customer.name} · {opportunity.service_key}
                                        {opportunity.responsible ? ` · ${opportunity.responsible.name}` : ''}
                                    </p>
                                </div>
                                <div className="flex items-center gap-2">
                                    {opportunity.is_duplicate && <Badge variant="warning">Mükerrer</Badge>}
                                    <Badge variant={statusVariant[opportunity.status] ?? 'info'}>{opportunity.status}</Badge>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}

                {opportunities.last_page > 1 && (
                    <nav className="mt-4 flex gap-2" aria-label="Sayfalama">
                        {opportunities.links.map((link, index) => (
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
