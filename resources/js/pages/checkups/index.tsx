import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, Link, router } from '@inertiajs/react';

interface SessionRow {
    id: number;
    status: string;
    template_version: number;
    completed_at: string | null;
    customer: { id: number; name: string } | null;
    template: { id: number; title: string; version: number } | null;
    recommendation_count: number | null;
}

export default function CheckupsIndex({ sessions, can }: { sessions: { data: SessionRow[]; last_page: number; links: { url: string | null; label: string; active: boolean }[] }; can: { create: boolean } }) {
    return (
        <>
            <Head title="Check-Up" />
            <AppShell title="Check-Up">
                <PageHeader
                    title="Check-Up Oturumları"
                    description="Müşteri dijital olgunluk değerlendirmesi. Tamamlanan oturumlar exact version snapshot saklar; sonuçlar deterministiktir."
                    breadcrumbs={[{ label: 'Ana sayfa', href: route('dashboard') }, { label: 'Check-Up' }]}
                    actions={can.create && (
                        <Button size="sm" onClick={() => router.get(route('checkups.create'))}>
                            Yeni oturum
                        </Button>
                    )}
                />

                {sessions.data.length === 0 ? (
                    <EmptyState
                        title="Oturum yok"
                        description="Henüz Check-Up oturumu başlatılmadı."
                        action={can.create && (
                            <Button size="sm" onClick={() => router.get(route('checkups.create'))}>
                                İlk oturumu başlat
                            </Button>
                        )}
                    />
                ) : (
                    <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                        {sessions.data.map((session) => (
                            <li key={session.id} className="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                <div>
                                    <Link href={route('checkups.show', session.id)} className="font-medium text-primary hover:underline">
                                        {session.customer?.name ?? '—'}
                                    </Link>
                                    <p className="text-xs text-muted-foreground">
                                        {session.template?.title} v{session.template_version}
                                        {session.completed_at ? ` · ${session.completed_at}` : ''}
                                        {session.recommendation_count !== null ? ` · ${session.recommendation_count} öneri` : ''}
                                    </p>
                                </div>
                                <Badge variant={session.status === 'completed' ? 'success' : 'info'}>
                                    {session.status === 'completed' ? 'Tamamlandı' : 'Taslak'}
                                </Badge>
                            </li>
                        ))}
                    </ul>
                )}

                {sessions.last_page > 1 && (
                    <nav className="mt-4 flex gap-2" aria-label="Sayfalama">
                        {sessions.links.map((link, index) => (
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
