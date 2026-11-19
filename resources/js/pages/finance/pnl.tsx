import { Badge } from '@/components/ui/indicators';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head } from '@inertiajs/react';

/** P&L raporu — nakit akisindan AYRI. Onaysiz formulde sayi uretilmez. */
export default function PnlReport({
    summary,
}: {
    summary: {
        status: string;
        total_income: number | null;
        total_expense: number | null;
        net: number | null;
        currency_code: string;
        formula_version: string | null;
    };
}) {
    const fmt = (value: number | null) =>
        value === null ? '—' : value.toLocaleString('tr-TR', { minimumFractionDigits: 2 });

    return (
        <>
            <Head title="P&L Raporu" />
            <AppShell title="Finans">
                <PageHeader
                    title="Kâr / Zarar (P&L)"
                    description="Gerçekleşen onaylı gelir ve gider kayıtlarının özeti. Nakit akışı ayrı rapordadır; bu iki metrik birbirinin ikamesi değildir."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Finans', href: route('finance.index') },
                        { label: 'P&L' },
                    ]}
                />

                <div className="rounded-xl border border-border bg-surface p-5">
                    <header className="mb-4 flex items-center gap-2">
                        <Badge variant={summary.status === 'calculated' ? 'success' : 'warning'}>
                            {summary.status === 'calculated' ? `Hesaplandı (${summary.formula_version})` : 'Konfigürasyon bekliyor'}
                        </Badge>
                        <span className="text-xs text-muted-foreground">Para birimi: {summary.currency_code}</span>
                    </header>
                    {summary.status === 'needs_configuration' ? (
                        <p className="text-sm text-muted-foreground" role="note">
                            P&L formülü müşteri örnek hesaplarıyla doğrulanmadı. Formül onaylanmadan gerçek finansal
                            toplamlar üretilmez; bu bilinçli bir güvenlik sınırıdır (ADR-0005).
                        </p>
                    ) : (
                        <dl className="grid gap-x-8 gap-y-3 sm:grid-cols-3">
                            <div>
                                <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Gelir (gerçekleşen, onaylı)</dt>
                                <dd className="mt-0.5 text-lg font-semibold text-success">{fmt(summary.total_income)} {summary.currency_code}</dd>
                            </div>
                            <div>
                                <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Gider (gerçekleşen, onaylı)</dt>
                                <dd className="mt-0.5 text-lg font-semibold text-danger">{fmt(summary.total_expense)} {summary.currency_code}</dd>
                            </div>
                            <div>
                                <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Net</dt>
                                <dd className="mt-0.5 text-lg font-semibold">{fmt(summary.net)} {summary.currency_code}</dd>
                            </div>
                        </dl>
                    )}
                </div>
            </AppShell>
        </>
    );
}
