import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router } from '@inertiajs/react';

interface SnapshotRow {
    id: number;
    as_of_date: string | null;
    cash_balance: string | null;
    monthly_burn: string | null;
    runway_months: string | null;
    currency_code: string;
    assumptions: Record<string, unknown> | null;
    status: string;
    formula_version: string | null;
    calculated_at: string | null;
}

/** Nakit akisi + runway raporu — P&L'den AYRI. Varsayimlar acikca listelenir. */
export default function CashReport({
    snapshots,
    can,
}: {
    snapshots: SnapshotRow[];
    can: { snapshot: boolean };
}) {
    const takeSnapshot = () => {
        router.patch(route('finance.cash.snapshot'), {}, { preserveScroll: true });
    };

    return (
        <>
            <Head title="Nakit Akışı / Runway" />
            <AppShell title="Finans">
                <PageHeader
                    title="Nakit Akışı ve Runway"
                    description="Tahmini nakit dengesi ve aylık burn ile runway. Bu tahminler yönetimsel kararlara yardımcıdır; resmi finansal rapor değildir."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Finans', href: route('finance.index') },
                        { label: 'Nakit / Runway' },
                    ]}
                    actions={can.snapshot && (
                        <Button size="sm" variant="secondary" onClick={takeSnapshot}>
                            Snapshot al
                        </Button>
                    )}
                />

                {snapshots.length === 0 ? (
                    <EmptyState title="Snapshot yok" description="Snapshot alarak nakit dengesi tahminini kaydedin." />
                ) : (
                    <ul className="space-y-3">
                        {snapshots.map((snapshot) => (
                            <li key={snapshot.id} className="rounded-xl border border-border bg-surface p-4">
                                <header className="mb-2 flex items-center gap-2">
                                    <Badge variant={snapshot.status === 'calculated' ? 'success' : 'warning'}>
                                        {snapshot.status === 'calculated' ? `Hesaplandı (${snapshot.formula_version ?? '—'})` : 'Konfigürasyon bekliyor'}
                                    </Badge>
                                    <span className="text-xs text-muted-foreground">
                                        {snapshot.as_of_date} · {snapshot.calculated_at}
                                    </span>
                                </header>
                                {snapshot.status === 'calculated' ? (
                                    <dl className="grid gap-x-8 gap-y-2 sm:grid-cols-3">
                                        <div>
                                            <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Nakit dengesi</dt>
                                            <dd className="mt-0.5 text-base font-semibold">
                                                {Number(snapshot.cash_balance).toLocaleString('tr-TR', { minimumFractionDigits: 2 })} {snapshot.currency_code}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Aylık burn</dt>
                                            <dd className="mt-0.5 text-base">{Number(snapshot.monthly_burn).toLocaleString('tr-TR', { minimumFractionDigits: 2 })} {snapshot.currency_code}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Runway</dt>
                                            <dd className="mt-0.5 text-base">{snapshot.runway_months ?? '—'} ay</dd>
                                        </div>
                                    </dl>
                                ) : (
                                    <p className="text-sm text-muted-foreground" role="note">
                                        {typeof snapshot.assumptions?.reason === 'string'
                                            ? snapshot.assumptions.reason
                                            : 'Nakit formülü müşteri onayı bekliyor; tahmini değerler üretilmez.'}
                                    </p>
                                )}
                                {snapshot.assumptions && (
                                    <details className="mt-2">
                                        <summary className="cursor-pointer text-xs text-muted-foreground">Varsayımları göster</summary>
                                        <pre className="mt-1 overflow-x-auto rounded-md bg-background p-2 text-xs text-muted-foreground">
                                            {JSON.stringify(snapshot.assumptions, null, 2)}
                                        </pre>
                                    </details>
                                )}
                            </li>
                        ))}
                    </ul>
                )}
            </AppShell>
        </>
    );
}
