import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { route } from '@/lib/route';
import { Head, Link, router, useForm } from '@inertiajs/react';

interface CustomerRow {
    id: number;
    name: string;
    trade_name: string | null;
    tax_number: string | null;
    city: string | null;
    archived_at: string | null;
    customer_type: { id: number; label: string } | null;
    contacts: { id: number; name: string }[];
}

export default function CustomersIndex({
    customers,
    filters,
    customerTypes,
    can,
}: {
    customers: {
        data: CustomerRow[];
        current_page: number;
        last_page: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search: string; type: number | null; status: string };
    customerTypes: { id: number; key: string; label: string }[];
    can: { create: boolean; archive: boolean };
}) {
    const form = useForm({ search: filters.search ?? '', type: filters.type ?? '', status: filters.status ?? 'active' });

    const filter = () => form.get(route('customers.index'), { preserveState: true });

    const archive = (id: number) => {
        router.patch(route('customers.archive', id), {}, {
            onSuccess: () => router.reload({ only: ['customers', 'filters'] }),
        });
    };

    return (
        <>
            <Head title="Müşteriler" />
            <AppShell title="Müşteriler">
                <PageHeader
                    title="Müşteriler"
                    description="360° firma sicili: temel bilgiler, yetkililer, özel alanlar ve evraklar."
                    breadcrumbs={[{ label: 'Ana sayfa', href: route('dashboard') }, { label: 'Müşteriler' }]}
                    actions={can.create && (
                        <Button size="sm" onClick={() => router.get(route('customers.create'))}>
                            Yeni müşteri
                        </Button>
                    )}
                />

                <form className="mb-4 flex flex-wrap gap-2" onSubmit={(e) => { e.preventDefault(); filter(); }}>
                    <Input
                        type="search"
                        placeholder="Ad, ticari unvan veya vergi no ara…"
                        value={form.data.search}
                        onChange={(e) => form.setData('search', e.target.value)}
                        aria-label="Müşteri ara"
                        className="max-w-xs"
                    />
                    <select
                        className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                        value={form.data.type ?? ''}
                        onChange={(e) => form.setData('type', e.target.value === '' ? '' : Number(e.target.value))}
                        aria-label="Tip filtresi"
                    >
                        <option value="">Tüm tipler</option>
                        {customerTypes.map((type) => (
                            <option key={type.id} value={type.id}>{type.label}</option>
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

                {customers.data.length === 0 ? (
                    <EmptyState
                        title="Müşteri bulunamadı"
                        description="Arama ölçütünüzle eşleşen kayıt yok veya henüz müşteri eklenmemiş."
                        action={can.create && (
                            <Button size="sm" onClick={() => router.get(route('customers.create'))}>
                                İlk müşteriyi ekle
                            </Button>
                        )}
                    />
                ) : (
                    <Table>
                        <THead>
                            <TR>
                                <TH scope="col">Firma</TH>
                                <TH scope="col">Tip</TH>
                                <TH scope="col">Vergi No</TH>
                                <TH scope="col">Şehir</TH>
                                <TH scope="col">Yetkililer</TH>
                                <TH scope="col">Durum</TH>
                                <TH scope="col">İşlemler</TH>
                            </TR>
                        </THead>
                        <TBody>
                            {customers.data.map((customer) => (
                                <TR key={customer.id}>
                                    <TD>
                                        <Link href={route('customers.show', customer.id)} className="font-medium text-primary hover:underline">
                                            {customer.name}
                                        </Link>
                                        {customer.trade_name && (
                                            <p className="text-xs text-muted-foreground">{customer.trade_name}</p>
                                        )}
                                    </TD>
                                    <TD>{customer.customer_type && <Badge variant="info">{customer.customer_type.label}</Badge>}</TD>
                                    <TD className="text-sm">{customer.tax_number ?? '—'}</TD>
                                    <TD className="text-sm">{customer.city ?? '—'}</TD>
                                    <TD className="text-sm text-muted-foreground">{customer.contacts.length} kişi</TD>
                                    <TD>
                                        {customer.archived_at
                                            ? <Badge variant="danger">Arşivli</Badge>
                                            : <Badge variant="success">Aktif</Badge>}
                                    </TD>
                                    <TD>
                                        <div className="flex gap-2">
                                            <Link href={route('customers.show', customer.id)} className="text-sm text-primary hover:underline">
                                                Detay
                                            </Link>
                                            {can.archive && !customer.archived_at && (
                                                <Button size="sm" variant="secondary" onClick={() => archive(customer.id)}>
                                                    Arşivle
                                                </Button>
                                            )}
                                        </div>
                                    </TD>
                                </TR>
                            ))}
                        </TBody>
                    </Table>
                )}

                {customers.last_page > 1 && (
                    <nav className="mt-4 flex gap-2" aria-label="Sayfalama">
                        {customers.links.map((link, index) => (
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
