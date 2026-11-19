import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { route } from '@/lib/route';
import { PageProps } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';

interface AuditRow {
    id: number;
    actor_name: string | null;
    action: string;
    auditable_type: string | null;
    auditable_id: number | null;
    before: Record<string, unknown> | null;
    after: Record<string, unknown> | null;
    ip_address: string | null;
    correlation_id: string | null;
    created_at: string;
    actor: { id: number; email: string } | null;
}

/**
 * Audit log: salt-okunur. Kayıt değiştirilemez (append-only) — ekranda da
 * hiçbir yazma aksiyonu sunulmaz.
 */
export default function AuditLogsIndex({
    events,
    filters,
}: {
    events: {
        data: AuditRow[];
        last_page: number;
        current_page: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { action: string; actor: string };
}) {
    const form = useForm({ action: filters.action ?? '', actor: filters.actor ?? '' });

    return (
        <>
            <Head title="Audit Log" />
            <AppShell title="Audit Log">
                <PageHeader
                    title="Audit Log"
                    description="Kritik değişikliklerin izlenebilir kaydı. Append-only: kim, ne zaman, eski/yeni değer."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Ayarlar', href: route('settings.index') },
                        { label: 'Audit Log' },
                    ]}
                />

                <form
                    className="mb-4 flex flex-wrap gap-2"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.get(route('settings.audit-logs.index'), { preserveState: true });
                    }}
                >
                    <Input
                        type="search"
                        placeholder="Aksiyon (örn. updated)"
                        value={form.data.action}
                        onChange={(e) => form.setData('action', e.target.value)}
                        aria-label="Aksiyona göre filtrele"
                        className="max-w-48"
                    />
                    <Input
                        type="search"
                        placeholder="Kullanıcı e-postası"
                        value={form.data.actor}
                        onChange={(e) => form.setData('actor', e.target.value)}
                        aria-label="Kullanıcıya göre filtrele"
                        className="max-w-48"
                    />
                    <Button type="submit" variant="secondary" size="sm">
                        Filtrele
                    </Button>
                </form>

                {events.data.length === 0 ? (
                    <EmptyState
                        title="Kayıt yok"
                        description="Filtreyle eşleşen audit kaydı bulunamadı veya sistem henüz olay kaydetmedi."
                    />
                ) : (
                    <Table>
                        <THead>
                            <TR>
                                <TH scope="col">Zaman</TH>
                                <TH scope="col">Actor</TH>
                                <TH scope="col">Aksiyon</TH>
                                <TH scope="col">Kayıt</TH>
                                <TH scope="col">Değişiklik</TH>
                            </TR>
                        </THead>
                        <TBody>
                            {events.data.map((event) => (
                                <TR key={event.id}>
                                    <TD className="whitespace-nowrap text-xs">
                                        {new Date(event.created_at).toLocaleString('tr-TR')}
                                    </TD>
                                    <TD>
                                        <p className="text-sm">{event.actor_name ?? 'sistem'}</p>
                                        <p className="text-xs text-muted-foreground">{event.ip_address}</p>
                                    </TD>
                                    <TD>
                                        <Badge variant={event.action === 'deleted' ? 'danger' : 'info'}>
                                            {event.action}
                                        </Badge>
                                    </TD>
                                    <TD className="text-xs">
                                        {event.auditable_type ? (
                                            <>
                                                {event.auditable_type.split('\\').pop()} #{event.auditable_id}
                                            </>
                                        ) : (
                                            '—'
                                        )}
                                    </TD>
                                    <TD className="max-w-md">
                                        <details className="text-xs">
                                            <summary className="cursor-pointer text-primary">
                                                before/after incele
                                            </summary>
                                            <pre className="mt-1 overflow-x-auto rounded-md bg-surface-muted p-2">
{JSON.stringify({ before: event.before, after: event.after }, null, 2)}
                                            </pre>
                                        </details>
                                    </TD>
                                </TR>
                            ))}
                        </TBody>
                    </Table>
                )}

                {events.last_page > 1 && (
                    <nav className="mt-4 flex gap-2" aria-label="Sayfalama">
                        {events.links.map((link, index) => (
                            <button
                                key={index}
                                type="button"
                                disabled={!(link as { url: string | null }).url}
                                onClick={() =>
                                    router.get((link as { url: string }).url, {}, { preserveState: true })
                                }
                                className="rounded-md border border-border px-3 py-1 text-sm disabled:opacity-40"
                                aria-current={(link as { active: boolean }).active ? 'page' : undefined}
                                dangerouslySetInnerHTML={{ __html: (link as { label: string }).label }}
                            />
                        ))}
                    </nav>
                )}
            </AppShell>
        </>
    );
}
