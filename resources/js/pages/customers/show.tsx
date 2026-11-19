import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs-tooltip';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { route } from '@/lib/route';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useRef } from 'react';
import { toast } from '@/components/ui/toast';

interface ContactRow {
    id: number;
    name: string;
    title: string | null;
    email: string | null;
    phone: string | null;
    is_primary: boolean;
}

interface DocRow {
    type_id: number;
    type_label: string;
    required: boolean;
    status: 'missing' | 'expired' | 'expiring_soon' | 'valid' | 'optional_missing';
    document: { id: number; original_name: string; size_bytes: number; valid_until: string | null } | null;
    warn_days: number;
}

interface ActivityRow {
    id: number;
    actor: string;
    action: string;
    created_at: string;
}

/** Müsteri detay: sekmeli yapi (Genel/Yetkililer/Ozel Alanlar/Evraklar/Projeler/Aktivite). */
export default function CustomerShow({
    customer,
    contacts,
    activities,
    fieldDefinitions,
    can,
    documentRows,
}: {
    documentRows: DocRow[];
    customer: {
        id: number;
        name: string;
        trade_name: string | null;
        tax_number: string | null;
        tax_office: string | null;
        email: string | null;
        phone: string | null;
        city: string | null;
        address: string | null;
        archived_at: string | null;
        customer_type: { id: number; label: string } | null;
        custom_field_values: { id: number; definition_id: number; typed_value: string | number | boolean | string[] | null }[];
    };
    contacts: ContactRow[];
    activities: ActivityRow[];
    fieldDefinitions: { id: number; key: string; label: string; type: string; required: boolean }[];
    can: { update: boolean; archive: boolean };
}) {
    const values = Object.fromEntries(customer.custom_field_values.map((v) => [v.definition_id, v.typed_value]));
    const uploadForm = useForm({ document_type_id: '', file: null as File | null, valid_until: '' });
    const fileInput = useRef<HTMLInputElement>(null);

    const upload = (e: React.FormEvent) => {
        e.preventDefault();
        uploadForm.post(route('customers.documents.store', customer.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => { uploadForm.reset(); toast.success('Evrak yüklendi.'); },
            onError: () => toast.error('Yükleme başarısız: dosya türü/boyut kontrol edin.'),
        });
    };

    const statusBadge = (status: DocRow['status'], warnDays: number) => {
        switch (status) {
            case 'missing': return <Badge variant="danger">Eksik (zorunlu)</Badge>;
            case 'expired': return <Badge variant="danger">Süresi geçmiş</Badge>;
            case 'expiring_soon': return <Badge variant={`warning`}>Yaklaşıyor</Badge>;
            case 'valid': return <Badge variant="success">Geçerli</Badge>;
            default: return <Badge>Istekli</Badge>;
        }
    };

    const archive = () => {
        router.patch(route('customers.archive', customer.id), {}, {
            onSuccess: () => toast.success('Müsteri arsivlendi.'),
            onError: () => toast.error('Arsivleme basarisiz.'),
        });
    };

    return (
        <>
            <Head title={customer.name} />
            <AppShell title={customer.name}>
                <PageHeader
                    title={customer.name}
                    description={customer.customer_type ? `Tip: ${customer.customer_type.label}` : 'Tip atanmadi'}
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Musteriler', href: route('customers.index') },
                        { label: customer.name },
                    ]}
                    actions={
                        <>
                            {customer.archived_at && <Badge variant="danger">Arsivli</Badge>}
                            {can.update && (
                                <Button size="sm" variant="secondary" onClick={() => router.get(route('customers.edit', customer.id))}>
                                    Duzenle
                                </Button>
                            )}
                            {can.archive && !customer.archived_at && (
                                <Button size="sm" variant="secondary" onClick={archive}>
                                    Arsivle
                                </Button>
                            )}
                        </>
                    }
                />

                <Tabs defaultValue="genel">
                    <TabsList aria-label="Müsteri detay sekmeleri">
                        <TabsTrigger value="genel">Genel</TabsTrigger>
                        <TabsTrigger value="yetkililer">Yetkililer ({contacts.length})</TabsTrigger>
                        <TabsTrigger value="ozel">Ozel Alanlar</TabsTrigger>
                        <TabsTrigger value="evraklar">Evraklar</TabsTrigger>
                        <TabsTrigger value="projeler">Projeler</TabsTrigger>
                        <TabsTrigger value="aktivite">Aktivite</TabsTrigger>
                    </TabsList>

                    <TabsContent value="genel">
                        <dl className="grid gap-x-8 gap-y-3 rounded-xl border border-border bg-surface p-5 sm:grid-cols-2">
                            <Detail label="Ticari unvan" value={customer.trade_name} />
                            <Detail label="Vergi no" value={customer.tax_number} />
                            <Detail label="Vergi dairesi" value={customer.tax_office} />
                            <Detail label="E-posta" value={customer.email} />
                            <Detail label="Telefon" value={customer.phone} />
                            <Detail label="Sehir" value={customer.city} />
                            <div className="sm:col-span-2">
                                <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Adres</dt>
                                <dd className="mt-0.5 whitespace-pre-line text-sm text-foreground">{customer.address ?? '—'}</dd>
                            </div>
                        </dl>
                    </TabsContent>

                    <TabsContent value="yetkililer">
                        {contacts.length === 0 ? (
                            <EmptyState title="Yetkili kaydi yok" description="Firmaya bagli yetkili kisi tanimlanmamis." />
                        ) : (
                            <Table>
                                <THead>
                                    <TR>
                                        <TH scope="col">Ad</TH>
                                        <TH scope="col">Unvan</TH>
                                        <TH scope="col">E-posta</TH>
                                        <TH scope="col">Telefon</TH>
                                        <TH scope="col">Birincil</TH>
                                    </TR>
                                </THead>
                                <TBody>
                                    {contacts.map((contact) => (
                                        <TR key={contact.id}>
                                            <TD className="font-medium">{contact.name}</TD>
                                            <TD>{contact.title ?? '—'}</TD>
                                            <TD>{contact.email ?? '—'}</TD>
                                            <TD>{contact.phone ?? '—'}</TD>
                                            <TD>{contact.is_primary && <Badge variant="success">Birincil</Badge>}</TD>
                                        </TR>
                                    ))}
                                </TBody>
                            </Table>
                        )}
                    </TabsContent>

                    <TabsContent value="ozel">
                        {fieldDefinitions.length === 0 ? (
                            <EmptyState
                                title="Ozel alan tanimi yok"
                                description="Yonetim > Ozel Alanlar ekranindan dinamik sicil alanlari ekleyebilirsiniz."
                            />
                        ) : (
                            <dl className="grid gap-x-8 gap-y-3 rounded-xl border border-border bg-surface p-5 sm:grid-cols-2">
                                {fieldDefinitions.map((field) => {
                                    const value = values[field.id];
                                    const display = Array.isArray(value)
                                        ? value.join(', ')
                                        : value === true
                                            ? 'Evet'
                                            : value === false || value === null || value === undefined
                                                ? '—'
                                                : String(value);
                                    return <Detail key={field.id} label={field.label} value={display} />;
                                })}
                            </dl>
                        )}
                    </TabsContent>

                    <TabsContent value="evraklar">
                        {can.update && (
                            <form className="mb-4 flex flex-wrap items-end gap-2 rounded-xl border border-border bg-surface p-4" onSubmit={upload}>
                                <div>
                                    <label htmlFor="doc-type" className="mb-1 block text-xs font-medium text-muted-foreground">Evrak tipi</label>
                                    <select
                                        id="doc-type"
                                        className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                                        value={uploadForm.data.document_type_id}
                                        onChange={(e) => uploadForm.setData('document_type_id', e.target.value)}
                                        required
                                    >
                                        <option value="">Seçin…</option>
                                        {documentRows.map((row) => (
                                            <option key={row.type_id} value={row.type_id}>{row.type_label}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label htmlFor="doc-valid" className="mb-1 block text-xs font-medium text-muted-foreground">Geçerlilik (opsiyonel)</label>
                                    <input
                                        id="doc-valid"
                                        type="date"
                                        className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                                        value={uploadForm.data.valid_until}
                                        onChange={(e) => uploadForm.setData('valid_until', e.target.value)}
                                    />
                                </div>
                                <div>
                                    <label htmlFor="doc-file" className="mb-1 block text-xs font-medium text-muted-foreground">Dosya (PDF/görsel/Office ≤10MB)</label>
                                    <input
                                        id="doc-file"
                                        ref={fileInput}
                                        type="file"
                                        className="text-sm"
                                        onChange={(e) => uploadForm.setData('file', e.target.files?.[0] ?? null)}
                                        required
                                    />
                                </div>
                                <Button type="submit" size="sm" loading={uploadForm.processing}>
                                    Yükle
                                </Button>
                                {uploadForm.errors.file && <p className="text-xs text-danger" role="alert">{uploadForm.errors.file}</p>}
                            </form>
                        )}
                        <Table>
                            <THead>
                                <TR>
                                    <TH scope="col">Evrak tipi</TH>
                                    <TH scope="col">Durum</TH>
                                    <TH scope="col">Dosya</TH>
                                    <TH scope="col">Geçerlilik</TH>
                                    <TH scope="col">İşlem</TH>
                                </TR>
                            </THead>
                            <TBody>
                                {documentRows.map((row) => {
                                    const activeDocument = row.document;
                                    return (
                                    <TR key={row.type_id}>
                                        <TD className="font-medium">
                                            {row.type_label}
                                            {row.required && <Badge className="ml-2">zorunlu</Badge>}
                                        </TD>
                                        <TD>{statusBadge(row.status, row.warn_days)}</TD>
                                        <TD className="text-sm">
                                            {row.document ? (
                                                <a href={route('documents.download', row.document.id)} className="text-primary hover:underline">
                                                    {row.document.original_name}
                                                </a>
                                            ) : '—'}
                                        </TD>
                                        <TD className="text-sm">{activeDocument?.valid_until ?? '—'}</TD>
                                        <TD>
                                            {activeDocument ? (
                                                can.update && (
                                                    <Button size="sm" variant="secondary" onClick={() => router.patch(route('documents.archive', activeDocument.id))}>
                                                        Arşivle
                                                    </Button>
                                                )
                                            ) : null}
                                        </TD>
                                    </TR>
                                    );
                                })}
                            </TBody>
                        </Table>
                    </TabsContent>

                    <TabsContent value="projeler">
                        <EmptyState
                            title="Proje modulu Faz 1'de eklenecek (Prompt 06)"
                            description="Bu firmaya bagli hizmet projeleri ve Kanban asamalari burada goruntulenecek."
                        />
                    </TabsContent>

                    <TabsContent value="aktivite">
                        {activities.length === 0 ? (
                            <EmptyState title="Kayit yok" description="Bu kayit uzerinde yapilan degisiklikler audit log'a yazilir." />
                        ) : (
                            <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                                {activities.map((activity) => (
                                    <li key={activity.id} className="flex items-center justify-between gap-3 px-4 py-2.5 text-sm">
                                        <span>
                                            <span className="font-medium">{activity.actor}</span>{' '}
                                            <Badge variant={activity.action === 'deleted' ? 'danger' : 'info'}>{activity.action}</Badge>
                                        </span>
                                        <span className="text-xs text-muted-foreground">{activity.created_at}</span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </TabsContent>
                </Tabs>
            </AppShell>
        </>
    );
}

function Detail({ label, value }: { label: string; value: string | null }) {
    return (
        <div>
            <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{label}</dt>
            <dd className="mt-0.5 text-sm text-foreground">{value || '—'}</dd>
        </div>
    );
}
