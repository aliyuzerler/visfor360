import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, Link, router, useForm } from '@inertiajs/react';

interface PartnerRow {
    id: number;
    name: string;
    expertise_type: string | null;
    is_active: boolean;
    contact_name: string | null;
}

/** Cozum ortaklari — iceri kayit; partner portal/login yok (SRS siniri). */
export default function PartnersIndex({ partners, filters, can }: { partners: { data: PartnerRow[]; last_page: number; links: { url: string | null; label: string; active: boolean }[] }; filters: Record<string, string>; can: { create: boolean } }) {
    const form = useForm({ search: filters.search ?? '', status: filters.status ?? '' });

    const apply = () => router.get(route('partners.index'), form.data, { preserveState: true });

    return (
        <>
            <Head title="Çözüm Ortakları" />
            <AppShell title="Çözüm Ortakları">
                <PageHeader
                    title="Çözüm Ortakları"
                    description="Dış paydaş ortakları, yönlendirme ve komisyon/hakediş takibi."
                    breadcrumbs={[{ label: 'Ana sayfa', href: route('dashboard') }, { label: 'Çözüm Ortakları' }]}
                    actions={can.create && (
                        <Button size="sm" onClick={() => router.get(route('partners.create'))}>
                            Yeni ortak
                        </Button>
                    )}
                />

                <form className="mb-4 flex flex-wrap gap-2" onSubmit={(e) => { e.preventDefault(); apply(); }}>
                    <Input
                        type="search"
                        placeholder="Ortak ara…"
                        value={form.data.search}
                        onChange={(e) => form.setData('search', e.target.value)}
                        aria-label="Ortak ara"
                        className="max-w-xs"
                    />
                    <select
                        className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                        value={form.data.status}
                        onChange={(e) => form.setData('status', e.target.value)}
                        aria-label="Durum"
                    >
                        <option value="">Tümü</option>
                        <option value="active">Aktif</option>
                        <option value="passive">Pasif</option>
                    </select>
                    <Button type="submit" variant="secondary" size="sm">Filtrele</Button>
                </form>

                {partners.data.length === 0 ? (
                    <EmptyState
                        title="Ortak bulunamadı"
                        description="Henüz çözüm ortağı kaydı yok."
                        action={can.create && (
                            <Button size="sm" onClick={() => router.get(route('partners.create'))}>
                                İlk ortağı ekle
                            </Button>
                        )}
                    />
                ) : (
                    <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                        {partners.data.map((partner) => (
                            <li key={partner.id} className="flex items-center justify-between gap-3 px-4 py-3">
                                <div>
                                    <Link href={route('partners.show', partner.id)} className="font-medium text-primary hover:underline">
                                        {partner.name}
                                    </Link>
                                    <p className="text-xs text-muted-foreground">{partner.expertise_type ?? '—'}{partner.contact_name ? ` · ${partner.contact_name}` : ''}</p>
                                </div>
                                <Badge variant={partner.is_active ? 'success' : 'danger'}>{partner.is_active ? 'Aktif' : 'Pasif'}</Badge>
                            </li>
                        ))}
                    </ul>
                )}

                {partners.last_page > 1 && (
                    <nav className="mt-4 flex gap-2" aria-label="Sayfalama">
                        {partners.links.map((link, index) => (
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
