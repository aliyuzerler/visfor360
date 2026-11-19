import { Badge, Card, CardBody, CardHeader, CardTitle } from '@/components/ui/indicators';
import { EmptyState } from '@/components/ui/page';
import { AppShell } from '@/components/app/app-shell';
import { PageProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { route } from '@/lib/route';

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
                <div className="mb-4 flex flex-wrap items-center gap-2">
                    <h2 className="text-lg font-semibold text-foreground">Genel Bakis</h2>
                    <Badge variant="info">Tum sayilar gercek sorgu sonuclaridir</Badge>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {can.tasks && kpi.tasks && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Gorevlerim</CardTitle>
                                <Link href={route('tasks.index')} className="text-xs text-primary hover:underline">Gorevlere git</Link>
                            </CardHeader>
                            <CardBody>
                                <p className="text-3xl font-bold text-foreground">{kpi.tasks.my_open}</p>
                                <p className="mt-1 text-xs text-muted-foreground">acik gorev</p>
                                {kpi.tasks.my_overdue > 0 ? (
                                    <p className="mt-2 text-sm font-medium text-danger" role="status">
                                        {kpi.tasks.my_overdue} gecikmis gorev
                                    </p>
                                ) : (
                                    <p className="mt-2 text-sm text-muted-foreground">Gecikmis gorev yok</p>
                                )}
                                <p className="mt-1 text-xs text-muted-foreground">Ekipte geciken: {kpi.tasks.team_overdue}</p>
                            </CardBody>
                        </Card>
                    )}

                    {can.projects && kpi.projects && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Aktif Projeler</CardTitle>
                                <Link href={route('projects.index')} className="text-xs text-primary hover:underline">Projelere git</Link>
                            </CardHeader>
                            <CardBody>
                                <p className="text-3xl font-bold text-foreground">{kpi.projects.active}</p>
                                <p className="mt-1 text-xs text-muted-foreground">arsivlenmemis proje</p>
                            </CardBody>
                        </Card>
                    )}

                    {can.customers && kpi.customers && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Aktif Musteriler</CardTitle>
                                <Link href={route('customers.index')} className="text-xs text-primary hover:underline">Musterilere git</Link>
                            </CardHeader>
                            <CardBody>
                                <p className="text-3xl font-bold text-foreground">{kpi.customers.active}</p>
                                <p className="mt-1 text-xs text-muted-foreground">arsivlenmemis firma</p>
                            </CardBody>
                        </Card>
                    )}

                    {can.documents && kpi.documents && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Yaklasan Evraklar</CardTitle>
                            </CardHeader>
                            <CardBody>
                                {kpi.documents.expiring_soon > 0 ? (
                                    <>
                                        <p className="text-3xl font-bold text-warning">{kpi.documents.expiring_soon}</p>
                                        <p className="mt-1 text-xs text-muted-foreground">evrakin suresi 30 gun icinde dolacak</p>
                                    </>
                                ) : (
                                    <EmptyState title="Yaklasan evrak yok" description="Son 30 gun icinde suresi dolan evrak bulunmuyor." />
                                )}
                            </CardBody>
                        </Card>
                    )}

                    {can.goals && kpi.goals && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Stratejik Hedefler</CardTitle>
                                <Link href={route('goals.index')} className="text-xs text-primary hover:underline">Hedeflere git</Link>
                            </CardHeader>
                            <CardBody>
                                <p className="text-3xl font-bold text-foreground">{kpi.goals.configured}/{kpi.goals.slots}</p>
                                <p className="mt-1 text-xs text-muted-foreground">tanimli hedef (12 slot)</p>
                                <p className="mt-1 text-xs text-muted-foreground">{kpi.goals.needs_configuration} slot icerik/formul bekliyor</p>
                            </CardBody>
                        </Card>
                    )}

                    {can.finance && kpi.finance && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Finans</CardTitle>
                                <Link href={route('finance.index')} className="text-xs text-primary hover:underline">Finansa git</Link>
                            </CardHeader>
                            <CardBody>
                                <p className="text-3xl font-bold text-foreground">{kpi.finance.pending_approvals}</p>
                                <p className="mt-1 text-xs text-muted-foreground">onay bekleyen kayit</p>
                                {kpi.finance.needs_configuration > 0 && (
                                    <p className="mt-2 text-xs text-warning" role="status">P&L formulu konfigurasyon bekliyor</p>
                                )}
                            </CardBody>
                        </Card>
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
