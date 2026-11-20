import { Card, CardBody, CardHeader, CardTitle } from '@/components/ui/indicators';
import { EmptyState } from '@/components/ui/page';
import { AppShell } from '@/components/app/app-shell';
import { PageProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { route } from '@/lib/route';
import {
    AlertTriangle,
    Building2,
    ClipboardCheck,
    FolderKanban,
    ListChecks,
    Target,
    Wallet,
    type LucideIcon,
} from 'lucide-react';

/** İkonlu KPI kartı: değer büyük, alt bilgi sade; gölge + hover ile derinlik. */
function KpiCard({
    icon: Icon,
    label,
    value,
    hint,
    href,
    hrefLabel,
    tone = 'default',
}: {
    icon: LucideIcon;
    label: string;
    value: string | number;
    hint: React.ReactNode;
    href?: string;
    hrefLabel?: string;
    tone?: 'default' | 'warning' | 'danger';
}) {
    const valueColor = tone === 'danger' ? 'text-danger' : tone === 'warning' ? 'text-warning' : 'text-foreground';

    return (
        <Card className="transition-shadow hover:shadow-md">
            <CardBody className="p-5">
                <div className="flex items-start justify-between">
                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <Icon className="h-5 w-5" aria-hidden />
                    </div>
                    {href && hrefLabel && (
                        <Link href={href} className="text-xs font-medium text-primary hover:underline">
                            {hrefLabel}
                        </Link>
                    )}
                </div>
                <p className="mt-3 text-xs font-medium uppercase tracking-wide text-muted-foreground">{label}</p>
                <p className={`mt-1 text-3xl font-bold tabular-nums ${valueColor}`}>{value}</p>
                <div className="mt-2 text-xs text-muted-foreground">{hint}</div>
            </CardBody>
        </Card>
    );
}

interface Kpi {
    tasks?: { my_open: number; my_overdue: number; team_overdue: number };
    projects?: { active: number };
    customers?: { active: number };
    documents?: { expiring_soon: number };
    goals?: { slots: number; configured: number; needs_configuration: number };
    finance?: { pending_approvals: number; needs_configuration: number };
}

/**
 * Genel Bakis (Prompt 10): tum KPI'lar gercek aggregate sorgularindan gelir;
 * izni olmayan modulun verisi backend'de uretilmez, kart da gosterilmez.
 */
export default function Dashboard({ kpi, can }: PageProps<{ kpi: Kpi; can: Record<string, boolean> }>) {
    return (
        <>
            <Head title="Genel Bakis" />
            <AppShell title="Genel Bakis">
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {can.tasks && kpi.tasks && (
                        <KpiCard
                            icon={ListChecks}
                            label="Görevlerim"
                            value={kpi.tasks.my_open}
                            hint={kpi.tasks.my_overdue > 0
                                ? <span className="font-medium text-danger" role="status">{kpi.tasks.my_overdue} gecikmiş görev · ekipte {kpi.tasks.team_overdue}</span>
                                : <span>Gecikmiş görev yok · ekipte {kpi.tasks.team_overdue}</span>}
                            href={route('tasks.index')}
                            hrefLabel="Görevlere git"
                        />
                    )}

                    {can.projects && kpi.projects && (
                        <KpiCard
                            icon={FolderKanban}
                            label="Aktif Projeler"
                            value={kpi.projects.active}
                            hint="Arşivlenmemiş projeler"
                            href={route('projects.index')}
                            hrefLabel="Projelere git"
                        />
                    )}

                    {can.customers && kpi.customers && (
                        <KpiCard
                            icon={Building2}
                            label="Aktif Müşteriler"
                            value={kpi.customers.active}
                            hint="Arşivlenmemiş firmalar"
                            href={route('customers.index')}
                            hrefLabel="Müşterilere git"
                        />
                    )}

                    {can.documents && kpi.documents && (
                        kpi.documents.expiring_soon > 0 ? (
                            <KpiCard
                                icon={AlertTriangle}
                                label="Yaklaşan Evraklar"
                                value={kpi.documents.expiring_soon}
                                hint="Süresi 30 gün içinde dolacak"
                                tone="warning"
                            />
                        ) : (
                            <KpiCard
                                icon={ClipboardCheck}
                                label="Evraklar"
                                value="—"
                                hint="Süresi yaklaşan evrak yok"
                            />
                        )
                    )}

                    {can.goals && kpi.goals && (
                        <KpiCard
                            icon={Target}
                            label="Stratejik Hedefler"
                            value={`${kpi.goals.configured}/${kpi.goals.slots}`}
                            hint={`${kpi.goals.needs_configuration} hedef içerik bekliyor`}
                            href={route('goals.index')}
                            hrefLabel="Hedeflere git"
                        />
                    )}

                    {can.finance && kpi.finance && (
                        <KpiCard
                            icon={Wallet}
                            label="Finans"
                            value={kpi.finance.pending_approvals}
                            hint={kpi.finance.needs_configuration > 0
                                ? <span className="font-medium text-warning">P&L formülü konfigürasyon bekliyor</span>
                                : 'Onay bekleyen kayıt'}
                            href={route('finance.index')}
                            hrefLabel="Finansa git"
                        />
                    )}
                </div>

                {!can.tasks && !can.projects && !can.customers && !can.documents && !can.goals && !can.finance && (
                    <EmptyState
                        title="Goruntulenebilir modul yok"
                        description="Hesabiniza hicbir modul izni atanmamis. Sistem yoneticinizle iletisime gecin."
                    />
                )}

                <div className="mt-6 flex flex-wrap gap-2">
                    {can.createTask && (
                        <button
                            type="button"
                            onClick={() => router.get(route('tasks.create'))}
                            className="rounded-md border border-border px-3 py-1.5 text-sm hover:border-primary"
                        >
                            Yeni gorev olustur
                        </button>
                    )}
                    {can.createCustomer && (
                        <button
                            type="button"
                            onClick={() => router.get(route('customers.create'))}
                            className="rounded-md border border-border px-3 py-1.5 text-sm hover:border-primary"
                        >
                            Yeni musteri ekle
                        </button>
                    )}
                </div>
            </AppShell>
        </>
    );
}
