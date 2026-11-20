import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/page';
import { PortalLayout } from '@/pages/portal/layout';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';
import { toast } from '@/components/ui/toast';

interface PortalProject {
    id: number;
    name: string;
    service: string | null;
    stage: string;
    start_date: string | null;
    due_date: string | null;
}

interface DocumentRequest {
    id: number;
    customer_name: string | null;
    document_type: string | null;
    note: string | null;
    status: string;
}

/** Portal anasayfa: yalniz kendi firmasinin projeleri + belge talepleri. */
export default function PortalDashboard({
    projects,
    documentRequests,
}: {
    projects: PortalProject[];
    documentRequests: DocumentRequest[];
}) {
    const uploadForm = useForm({ request_id: '' as string, file: null as File | null });

    const upload = (e: React.FormEvent<HTMLFormElement>, requestId: number) => {
        e.preventDefault();
        uploadForm.setData('request_id', String(requestId));
        uploadForm.post(route('portal.documents.upload'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => toast.success('Belge alındı.'),
            onError: (errors) => toast.error(errors.file ?? 'Yükleme başarısız.'),
        });
    };

    return (
        <>
            <Head title="Portal — Genel Bakış" />
            <PortalLayout title="Genel Bakış">
                <section aria-label="Projelerim" className="mb-8">
                    <h2 className="mb-3 text-lg font-semibold">Projelerim</h2>
                    {projects.length === 0 ? (
                        <EmptyState title="Proje yok" description="Firmanıza bağlı aktif proje bulunmuyor." />
                    ) : (
                        <ul className="grid gap-3 sm:grid-cols-2">
                            {projects.map((project) => (
                                <li key={project.id} className="rounded-xl border border-border bg-surface p-4">
                                    <p className="font-medium">{project.name}</p>
                                    <p className="mt-1 text-xs text-muted-foreground">{project.service ?? '—'}</p>
                                    <p className="mt-2 text-sm">Aşama: {project.stage}</p>
                                    <p className="text-xs text-muted-foreground">
                                        {project.start_date ?? '—'} → {project.due_date ?? '—'}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section aria-label="İstenen belgeler">
                    <h2 className="mb-3 text-lg font-semibold">İstenen belgeler</h2>
                    {documentRequests.length === 0 ? (
                        <EmptyState title="Belge talebi yok" description="Şu anda sizden istenen bir belge bulunmuyor." />
                    ) : (
                        <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                            {documentRequests.map((request) => (
                                <li key={request.id} className="px-4 py-3">
                                    <div className="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p className="text-sm font-medium">{request.document_type}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {request.customer_name ?? ''}{request.note ? ` · ${request.note}` : ''}
                                            </p>
                                        </div>
                                        <Badge variant={request.status === 'requested' ? 'warning' : 'success'}>
                                            {request.status === 'requested' ? 'Bekleniyor' : 'Yüklendi'}
                                        </Badge>
                                    </div>
                                    {request.status === 'requested' && (
                                        <form className="mt-2 flex gap-2" onSubmit={(e) => upload(e, request.id)}>
                                            <input
                                                type="file"
                                                name="file"
                                                accept=".pdf,image/jpeg,image/png"
                                                required
                                                aria-label={`${request.document_type} dosyası seç`}
                                                onChange={(e) => uploadForm.setData('file', e.target.files?.[0] ?? null)}
                                                className="text-sm"
                                            />
                                            <Button type="submit" size="sm" loading={uploadForm.processing}>
                                                Yükle
                                            </Button>
                                        </form>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </PortalLayout>
        </>
    );
}
