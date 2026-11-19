import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from '@/components/ui/toast';

interface StageRow {
    id: number;
    key: string;
    label: string;
    sort_order: number;
    is_active: boolean;
    is_terminal: boolean;
    required_fields: string[] | null;
}

interface PipelineRow {
    id: number;
    key: string;
    label: string;
    is_example: boolean;
    is_active: boolean;
    stages: StageRow[];
}

interface ServiceRow {
    id: number;
    key: string;
    label: string;
    description: string | null;
    pipeline_id: number;
    is_active: boolean;
    pipeline?: { id: number; label: string; is_example: boolean } | null;
    department?: { id: number; name: string } | null;
}

/**
 * Hizmet ve pipeline yöneticisi. Tanımlar müşteri kararı ile kalıcılaşır;
 * yalnızca örnek şablonlar (is_example) düzenlenebilir — aşamalar "ÖRNEK" etiketlidir.
 */
export default function ServicesIndex({
    serviceTypes,
    pipelines,
    departments,
}: {
    serviceTypes: ServiceRow[];
    pipelines: PipelineRow[];
    departments: { id: number; name: string }[];
}) {
    const serviceForm = useForm({
        key: '',
        label: '',
        description: '',
        pipeline_id: '',
        department_id: '',
    });
    const [editingService, setEditingService] = useState<ServiceRow | null>(null);

    const stageForm = useForm({ pipeline_id: '', key: '', label: '', sort_order: '' });

    const submitService = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingService) {
            serviceForm.put(route('settings.services.update', editingService.id), {
                onSuccess: () => { toast.success('Hizmet güncellendi.'); setEditingService(null); },
            });
        } else {
            serviceForm.post(route('settings.services.store'), {
                onSuccess: () => { toast.success('Hizmet oluşturuldu.'); serviceForm.reset(); },
            });
        }
    };

    const submitStage = (e: React.FormEvent) => {
        e.preventDefault();
        if (! stageForm.data.pipeline_id) return;
        router.post(route('settings.pipelines.stages.store', stageForm.data.pipeline_id), stageForm.data, {
            preserveScroll: true,
            onSuccess: () => { toast.success('Aşama eklendi.'); stageForm.reset('key', 'label', 'sort_order'); },
        });
    };

    return (
        <>
            <Head title="Hizmetler ve Pipeline'lar" />
            <AppShell title="Hizmetler ve Pipeline'lar">
                <PageHeader
                    title="Hizmetler ve Pipeline'lar"
                    description="Her hizmet kendi Kanban şablonuna bağlıdır. Aşama adları ve geçiş kuralları müşteri girdisiyle kalıcılaşır; örnek şablonlar 'ÖRNEK' etiketlidir."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Ayarlar', href: route('settings.index') },
                        { label: 'Hizmetler ve Pipeline\'lar' },
                    ]}
                />

                <h2 className="mb-3 text-base font-semibold">Hizmet kataloğu</h2>
                {serviceTypes.length === 0 ? (
                    <EmptyState title="Hizmet tanımı yok" description="Aşağıdaki formdan ilk hizmeti ekleyin." />
                ) : (
                    <Table>
                        <THead>
                            <TR>
                                <TH scope="col">Hizmet</TH>
                                <TH scope="col">Anahtar</TH>
                                <TH scope="col">Pipeline</TH>
                                <TH scope="col">Departman</TH>
                                <TH scope="col">Durum</TH>
                            </TR>
                        </THead>
                        <TBody>
                            {serviceTypes.map((service) => (
                                <TR key={service.id}>
                                    <TD className="font-medium">{service.label}</TD>
                                    <TD className="text-xs text-muted-foreground">{service.key}</TD>
                                    <TD>
                                        {service.pipeline?.label}
                                        {service.pipeline?.is_example && <Badge variant="warning" className="ml-2">ÖRNEK</Badge>}
                                    </TD>
                                    <TD>{service.department?.name ?? '—'}</TD>
                                    <TD>{service.is_active ? <Badge variant="success">Aktif</Badge> : <Badge variant="danger">Pasif</Badge>}</TD>
                                </TR>
                            ))}
                        </TBody>
                    </Table>
                )}

                <form className="mt-4 max-w-xl space-y-4 rounded-xl border border-border bg-surface p-4" onSubmit={submitService}>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField name="label" label="Hizmet adı" error={serviceForm.errors.label} required>
                            {(props) => (
                                <Input {...props} value={serviceForm.data.label} onChange={(e) => serviceForm.setData('label', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="key" label="Anahtar (sistem)" error={serviceForm.errors.key} required>
                            {(props) => (
                                <Input {...props} value={serviceForm.data.key} onChange={(e) => serviceForm.setData('key', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="pipeline_id" label="Pipeline şablonu" error={serviceForm.errors.pipeline_id} required>
                            {() => (
                                <select
                                    className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                    value={String(serviceForm.data.pipeline_id)}
                                    onChange={(e) => serviceForm.setData('pipeline_id', e.target.value)}
                                    required
                                >
                                    <option value="">Seçin…</option>
                                    {pipelines.map((pipeline) => (
                                        <option key={pipeline.id} value={pipeline.id}>
                                            {pipeline.label}{pipeline.is_example ? ' (ÖRNEK)' : ''}
                                        </option>
                                    ))}
                                </select>
                            )}
                        </FormField>
                        <FormField name="department_id" label="Departman">
                            {() => (
                                <select
                                    className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                    value={String(serviceForm.data.department_id)}
                                    onChange={(e) => serviceForm.setData('department_id', e.target.value)}
                                >
                                    <option value="">Seçilmedi</option>
                                    {departments.map((department) => (
                                        <option key={department.id} value={department.id}>{department.name}</option>
                                    ))}
                                </select>
                            )}
                        </FormField>
                    </div>
                    <Button type="submit" size="sm" loading={serviceForm.processing}>Hizmet oluştur</Button>
                </form>

                <h2 className="mb-3 mt-8 text-base font-semibold">Pipeline şablonları</h2>
                {pipelines.map((pipeline) => (
                    <section key={pipeline.id} className="mb-4 rounded-xl border border-border bg-surface p-4">
                        <header className="mb-2 flex items-center gap-2">
                            <h3 className="font-medium">{pipeline.label}</h3>
                            {pipeline.is_example && <Badge variant="warning">ÖRNEK — müşteri onayı bekliyor</Badge>}
                            {!pipeline.is_active && <Badge variant="danger">Pasif</Badge>}
                        </header>
                        <ol className="mb-3 flex flex-wrap gap-2">
                            {pipeline.stages.map((stage) => (
                                <li key={stage.id} className="rounded-lg border border-border bg-background px-3 py-1.5 text-sm">
                                    {stage.sort_order}. {stage.label}
                                    {stage.is_terminal && ' ⏹'}
                                    {!stage.is_active && ' (pasif)'}
                                </li>
                            ))}
                        </ol>
                        <form className="flex flex-wrap items-end gap-2" onSubmit={submitStage}>
                            <input type="hidden" value={pipeline.id} />
                            <Button
                                type="button"
                                size="sm"
                                variant="secondary"
                                onClick={() => stageForm.setData('pipeline_id', String(pipeline.id))}
                                aria-pressed={stageForm.data.pipeline_id === String(pipeline.id)}
                            >
                                {stageForm.data.pipeline_id === String(pipeline.id) ? 'Bu şablona ekleniyor…' : 'Bu şablona aşama ekle'}
                            </Button>
                            {stageForm.data.pipeline_id === String(pipeline.id) && (
                                <>
                                    <Input
                                        aria-label="Aşama adı"
                                        placeholder="Aşama adı"
                                        value={stageForm.data.label}
                                        onChange={(e) => stageForm.setData('label', e.target.value)}
                                        required
                                    />
                                    <Input
                                        aria-label="Anahtar"
                                        placeholder="anahtar (ornek-asama)"
                                        value={stageForm.data.key}
                                        onChange={(e) => stageForm.setData('key', e.target.value)}
                                        required
                                    />
                                    <Input
                                        aria-label="Sıra"
                                        type="number"
                                        placeholder="Sıra"
                                        className="w-24"
                                        value={stageForm.data.sort_order}
                                        onChange={(e) => stageForm.setData('sort_order', e.target.value)}
                                    />
                                    <Button type="submit" size="sm">Ekle</Button>
                                </>
                            )}
                        </form>
                    </section>
                ))}
            </AppShell>
        </>
    );
}
