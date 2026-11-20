import { Badge } from '@/components/ui/indicators';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, Link } from '@inertiajs/react';

interface GoalRow {
    id: number;
    slot_key: string;
    label: string | null;
    owner: string | null;
    period: string | null;
    unit: string | null;
    is_placeholder: boolean;
    progress_status: 'calculated' | 'needs_configuration';
    task_count: number;
    can_update: boolean;
}

/** 5 yıllık vizyon + 12 hedef slotu. İçerik müşteri kararı; placeholder'lar etiketli. */
export default function GoalsIndex({ vision, goals }: { vision: { content: string; horizon_years: number; is_approved: boolean } | null; goals: GoalRow[] }) {
    return (
        <>
            <Head title="Stratejik Hedefler" />
            <AppShell title="Stratejik Hedefler">
                <PageHeader
                    title="Vizyon ve 12 Ana Hedef"
                    description="5 yıllık vizyon ve 12 ana hedef bağlantılı OKR takibi. İlerleme yalnızca onaylı ölçüm formülü ile hesaplanır; formül yoksa yüzde gösterilmez."
                    breadcrumbs={[{ label: 'Ana sayfa', href: route('dashboard') }, { label: 'Stratejik Hedefler' }]}
                />

                <section aria-label="Vizyon" className="mb-6 rounded-xl border border-border bg-surface p-5">
                    <header className="mb-2 flex items-center gap-2">
                        <h2 className="text-base font-semibold">5 Yıllık Vizyon</h2>
                        {vision && !vision.is_approved && <Badge variant="warning">Onay bekliyor</Badge>}
                    </header>
                    {vision ? (
                        <>
                            <p className="whitespace-pre-line text-sm text-foreground">{vision.content}</p>
                            {!vision.is_approved && (
                                <p className="mt-2 text-xs text-muted-foreground">
                                    Vizyon metni müşteri onayına gönderilmedi; içerik taslak görünümündedir.
                                </p>
                            )}
                        </>
                    ) : (
                        <EmptyState title="Vizyon girilmedi" description="5 yıllık vizyon metni müşteri kararıyla girilecek." />
                    )}
                </section>

                <section aria-label="12 ana hedef">
                    <header className="mb-3 flex items-center gap-2">
                        <h2 className="text-base font-semibold">12 Ana Hedef</h2>
                        <Badge variant="info">SRS: 12 slot sabit</Badge>
                    </header>
                    {goals.every((goal) => goal.is_placeholder) && (
                        <p className="mb-3 text-xs text-muted-foreground" role="note">
                            Aşağıdaki 12 hedef slotu yönetim panelinden düzenlenir; formüller müşteri kararı bekliyor (örnek veri üretim verisi değildir).
                        </p>
                    )}
                    <ul className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {goals.map((goal) => (
                            <li key={goal.id} className="rounded-xl border border-border bg-surface p-4">
                                <div className="flex items-start justify-between gap-2">
                                    <Link href={route('goals.show', goal.id)} className="font-medium text-primary hover:underline">
                                        {goal.slot_key}
                                    </Link>
                                    {goal.is_placeholder && <Badge variant="warning">Örnek/Boş</Badge>}
                                    {!goal.is_placeholder && goal.progress_status === 'needs_configuration' && (
                                        <Badge variant="info">Konfigürasyon bekliyor</Badge>
                                    )}
                                </div>
                                <p className="mt-1 text-sm">{goal.label ?? 'Hedef adı müşteri kararı bekliyor'}</p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {goal.owner ? `Sorumlu: ${goal.owner}` : 'Sorumlu atanmadı'} · {goal.task_count} görev
                                </p>
                                {goal.can_update && (
                                    <p className="mt-2 text-xs text-muted-foreground">Düzenleme yetkiniz var.</p>
                                )}
                            </li>
                        ))}
                    </ul>
                </section>
            </AppShell>
        </>
    );
}
