import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { route } from '@/lib/route';
import { Head, Link, router, useForm } from '@inertiajs/react';

interface EntryRow {
    id: number;
    direction: string;
    entry_type: string;
    status: string;
    amount: string;
    currency_code: string;
    entry_date: string | null;
    category: string | null;
    customer: string | null;
    project: string | null;
    parent_entry_id: number | null;
}

/**
 * Finans kayitlari: gelir/gider x tahmini/gerceklesen ayrik filtreler.
 * Bu modul resmi muhasebe araci degildir (SRS acik notu).
 */
export default function FinanceIndex({
    entries,
    filters,
    can,
}: {
    entries: { data: EntryRow[]; current_page: number; last_page: number; links: { url: string | null; label: string; active: boolean }[] };
    filters: Record<string, string>;
    can: { create: boolean; export: boolean };
}) {
    const form = useForm({
        direction: filters.direction ?? '',
        type: filters.type ?? '',
        status: filters.status ?? '',
        from: filters.from ?? '',
        to: filters.to ?? '',
    });

    const apply = () => router.get(route('finance.index'), form.data, { preserveState: true });

    return (
        <>
            <Head title="Finans" />
            <AppShell title="Finans">
                <PageHeader
                    title="Finans"
                    description="Gelir/gider ve tahmini/gerçekleşen takip. Bu modül resmi muhasebe aracı değildir; P&L ve nakit akışı ayrı raporlardadır."
                    breadcrumbs={[{ label: 'Ana sayfa', href: route('dashboard') }, { label: 'Finans' }]}
                    actions={can.create && (
                        <Button size="sm" onClick={() => router.get(route('finance.create'))}>
                            Yeni kayıt
                        </Button>
                    )}
                />

                <nav className="mb-4 flex gap-2" aria-label="Finans raporları">
                    <Button size="sm" variant="secondary" onClick={() => router.get(route('finance.pnl'))}>
                        P&L raporu
                    </Button>
                    <Button size="sm" variant="secondary" onClick={() => router.get(route('finance.cash'))}>
                        Nakit akışı / runway
                    </Button>
                </nav>

                <form className="mb-4 flex flex-wrap gap-2" onSubmit={(e) => { e.preventDefault(); apply(); }}>
                    <select
                        className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                        value={form.data.direction}
                        onChange={(e) => form.setData('direction', e.target.value)}
                        aria-label="Yön"
                    >
                        <option value="">Tüm yönler</option>
                        <option value="income">Gelir</option>
                        <option value="expense">Gider</option>
                    </select>
                    <select
                        className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                        value={form.data.type}
                        onChange={(e) => form.setData('type', e.target.value)}
                        aria-label="Kayıt tipi"
                    >
                        <option value="">Tahmini + gerçekleşen</option>
                        <option value="estimated">Tahmini</option>
                        <option value="actual">Gerçekleşen</option>
                    </select>
                    <select
                        className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                        value={form.data.status}
                        onChange={(e) => form.setData('status', e.target.value)}
                        aria-label="Durum"
                    >
                        <option value="">Tüm durumlar</option>
                        <option value="draft">Taslak</option>
                        <option value="pending">Beklemede</option>
                        <option value="approved">Onaylı</option>
                        <option value="cancelled">İptal</option>
                    </select>
                    <Input type="date" aria-label="Başlangıç" value={form.data.from} onChange={(e) => form.setData('from', e.target.value)} />
                    <Input type="date" aria-label="Bitiş" value={form.data.to} onChange={(e) => form.setData('to', e.target.value)} />
                    <Button type="submit" variant="secondary" size="sm">Filtrele</Button>
                </form>

                {entries.data.length === 0 ? (
                    <EmptyState
                        title="Kayıt bulunamadı"
                        description="Filtre ölçütlerinizle eşleşen finans kaydı yok."
                        action={can.create && (
                            <Button size="sm" onClick={() => router.get(route('finance.create'))}>
                                İlk kaydı ekle
                            </Button>
                        )}
                    />
                ) : (
                    <Table>
                        <THead>
                            <TR>
                                <TH scope="col">Yön</TH>
                                <TH scope="col">Tip</TH>
                                <TH scope="col">Tutar</TH>
                                <TH scope="col">Tarih</TH>
                                <TH scope="col">Kategori</TH>
                                <TH scope="col">Müşteri / Proje</TH>
                                <TH scope="col">Durum</TH>
                                <TH scope="col">İşlem</TH>
                            </TR>
                        </THead>
                        <TBody>
                            {entries.data.map((entry) => (
                                <TR key={entry.id}>
                                    <TD><Badge variant={entry.direction === 'income' ? 'success' : 'danger'}>{entry.direction === 'income' ? 'Gelir' : 'Gider'}</Badge></TD>
                                    <TD><Badge variant="info">{entry.entry_type === 'estimated' ? 'Tahmini' : 'Gerçekleşen'}</Badge></TD>
                                    <TD className="text-sm font-medium">
                                        {Number(entry.amount).toLocaleString('tr-TR', { minimumFractionDigits: 2 })} {entry.currency_code}
                                    </TD>
                                    <TD className="text-sm">{entry.entry_date ?? '—'}</TD>
                                    <TD className="text-sm">{entry.category ?? '—'}</TD>
                                    <TD className="text-xs text-muted-foreground">{entry.customer ?? '—'}{entry.project ? ` · ${entry.project}` : ''}</TD>
                                    <TD>
                                        <Badge variant={entry.status === 'approved' ? 'success' : entry.status === 'cancelled' ? 'danger' : 'info'}>
                                            {entry.status}
                                        </Badge>
                                        {entry.parent_entry_id && <span className="ml-1 text-xs text-muted-foreground">(düzeltme)</span>}
                                    </TD>
                                    <TD>
                                        <Link href={route('finance.edit', entry.id)} className="text-sm text-primary hover:underline">
                                            Düzenle
                                        </Link>
                                    </TD>
                                </TR>
                            ))}
                        </TBody>
                    </Table>
                )}

                {entries.last_page > 1 && (
                    <nav className="mt-4 flex gap-2" aria-label="Sayfalama">
                        {entries.links.map((link, index) => (
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
