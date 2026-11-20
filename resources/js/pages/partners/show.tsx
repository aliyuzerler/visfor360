import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input, Textarea } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { toast } from '@/components/ui/toast';

interface WorkRow {
    id: number;
    title: string;
    referral_type: string;
    value: string | null;
    currency_code: string;
    customer: { id: number; name: string } | null;
    project: { id: number; name: string } | null;
    service: { id: number; label: string } | null;
    commission_status: string | null;
}

interface AgreementRow {
    id: number;
    commission_type: string;
    commission_rate: string;
    currency_code: string;
    status: string;
    valid_from: string | null;
    valid_until: string | null;
}

/** Ortak detay: isler, anlasmalar (onaysiz hesap yok), komisyonlar. */
export default function PartnerShow({
    partner,
    works,
    agreements,
    customers,
    serviceTypes,
    can,
}: {
    partner: {
        id: number;
        name: string;
        expertise_type: string | null;
        contact_name: string | null;
        contact_email: string | null;
        contact_phone: string | null;
        notes: string | null;
        is_active: boolean;
    };
    works: WorkRow[];
    agreements: AgreementRow[];
    customers: { id: number; name: string }[];
    serviceTypes: { id: number; label: string; key: string }[];
    can: { update: boolean; archive: boolean };
}) {
    const workForm = useForm({
        title: '',
        customer_id: '',
        service_type_id: '',
        referral_type: 'work',
        value: '',
        currency_code: 'TRY',
    });
    const agreementForm = useForm({
        commission_type: 'percent_of_value',
        commission_rate: '',
        currency_code: 'TRY',
        valid_from: '',
        valid_until: '',
    });

    const submitWork = (e: React.FormEvent) => {
        e.preventDefault();
        workForm.post(route('partners.works.store', partner.id), {
            preserveScroll: true,
            onSuccess: () => { toast.success('İş kaydı eklendi.'); workForm.reset(); },
            onError: (errors) => toast.error(Object.values(errors)[0] ?? 'Kayıt başarısız.'),
        });
    };

    const submitAgreement = (e: React.FormEvent) => {
        e.preventDefault();
        agreementForm.post(route('partners.agreements.store', partner.id), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Anlaşma taslak oluşturuldu.'); agreementForm.reset(); },
            onError: (errors) => toast.error(Object.values(errors)[0] ?? 'Kayıt başarısız.'),
        });
    };

    const approveAgreement = (agreementId: number) => {
        router.patch(route('partners.agreements.approve', [partner.id, agreementId]), {}, {
            preserveScroll: true,
            onSuccess: () => toast.success('Anlaşma onaylandı.'),
        });
    };

    const calculateCommission = (workId: number) => {
        router.post(route('partners.commission.calculate', [partner.id, workId]), {}, {
            preserveScroll: true,
            onSuccess: () => toast.success('Komisyon hesaplandı.'),
            onError: (errors) => toast.error(errors.commission ?? 'Onaylı anlaşma olmadan hesap yapılamaz.'),
        });
    };

    return (
        <>
            <Head title={partner.name} />
            <AppShell title="Çözüm Ortakları">
                <PageHeader
                    title={partner.name}
                    description={`${partner.expertise_type ?? '—'}${partner.contact_name ? ` · ${partner.contact_name}` : ''}`}
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Çözüm Ortakları', href: route('partners.index') },
                        { label: partner.name },
                    ]}
                    actions={<Badge variant={partner.is_active ? 'success' : 'danger'}>{partner.is_active ? 'Aktif' : 'Pasif'}</Badge>}
                />

                <section aria-label="Anlaşmalar" className="mb-6">
                    <h2 className="mb-3 text-base font-semibold">Komisyon anlaşmaları</h2>
                    <p className="mb-3 text-xs text-muted-foreground">
                        Komisyon tipi örnek settir (fixed_amount / percent_of_value — ADR-0013); onaylanmadan hesaplama yapılamaz.
                    </p>
                    <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                        {agreements.map((agreement) => (
                            <li key={agreement.id} className="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 text-sm">
                                <span>
                                    {agreement.commission_type} · {agreement.commission_rate} {agreement.currency_code}
                                    {agreement.valid_from ? ` · ${agreement.valid_from}` : ''}
                                </span>
                                <span className="flex items-center gap-2">
                                    <Badge variant={agreement.status === 'approved' ? 'success' : 'warning'}>{agreement.status}</Badge>
                                    {agreement.status !== 'approved' && (
                                        <Button size="sm" variant="secondary" onClick={() => approveAgreement(agreement.id)}>
                                            Onayla
                                        </Button>
                                    )}
                                </span>
                            </li>
                        ))}
                    </ul>
                    {can.update && (
                        <form className="mt-4 max-w-2xl space-y-3 rounded-xl border border-border bg-surface p-4" onSubmit={submitAgreement}>
                            <div className="grid gap-3 sm:grid-cols-3">
                                <FormField name="commission_type" label="Tip">
                                    {() => (
                                        <select
                                            className="h-9 w-full rounded-md border border-border bg-background px-3 text-sm"
                                            value={agreementForm.data.commission_type}
                                            onChange={(e) => agreementForm.setData('commission_type', e.target.value as never)}
                                        >
                                            <option value="fixed_amount">Sabit tutar</option>
                                            <option value="percent_of_value">Yüzde</option>
                                        </select>
                                    )}
                                </FormField>
                                <FormField name="commission_rate" label="Tutar / Oran" error={agreementForm.errors.commission_rate} required>
                                    {(props) => (
                                        <Input {...props} type="number" step="0.0001" min="0" value={agreementForm.data.commission_rate} onChange={(e) => agreementForm.setData('commission_rate', e.target.value)} />
                                    )}
                                </FormField>
                                <FormField name="currency_code" label="Para birimi" error={agreementForm.errors.currency_code} required>
                                    {(props) => (
                                        <Input {...props} maxLength={3} value={agreementForm.data.currency_code} onChange={(e) => agreementForm.setData('currency_code', e.target.value.toUpperCase())} />
                                    )}
                                </FormField>
                            </div>
                            <Button type="submit" size="sm" loading={agreementForm.processing}>Anlaşma ekle</Button>
                        </form>
                    )}
                </section>

                <section aria-label="Ortak işleri" className="mb-6">
                    <h2 className="mb-3 text-base font-semibold">Ortak işler</h2>
                    {works.length === 0 ? (
                        <EmptyState title="İş kaydı yok" description="Ortağın yürüttüğü/yönlendirdiği işler burada listelenir." />
                    ) : (
                        <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
                            {works.map((work) => (
                                <li key={work.id} className="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                    <div>
                                        <p className="text-sm font-medium">{work.title}</p>
                                        <p className="text-xs text-muted-foreground">
                                            {work.customer?.name ?? '—'}
                                            {work.project ? ` · ${work.project.name}` : ''}
                                            {work.value ? ` · ${Number(work.value).toLocaleString('tr-TR')} ${work.currency_code}` : ''}
                                            {work.commission_status ? ` · komisyon: ${work.commission_status}` : ''}
                                        </p>
                                    </div>
                                    {can.update && (
                                        <Button size="sm" variant="secondary" onClick={() => calculateCommission(work.id)}>
                                            Komisyon hesapla
                                        </Button>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                    {can.update && (
                        <form className="mt-4 max-w-2xl space-y-3 rounded-xl border border-border bg-surface p-4" onSubmit={submitWork}>
                            <div className="grid gap-3 sm:grid-cols-2">
                                <FormField name="title" label="İş başlığı" error={workForm.errors.title} required>
                                    {(props) => (
                                        <Input {...props} value={workForm.data.title} onChange={(e) => workForm.setData('title', e.target.value)} />
                                    )}
                                </FormField>
                                <FormField name="customer_id" label="Müşteri" error={workForm.errors.customer_id} required>
                                    {() => (
                                        <select
                                            className="h-9 w-full rounded-md border border-border bg-background px-3 text-sm"
                                            value={workForm.data.customer_id}
                                            onChange={(e) => workForm.setData('customer_id', e.target.value)}
                                            required
                                        >
                                            <option value="">Seçin…</option>
                                            {customers.map((customer) => (
                                                <option key={customer.id} value={customer.id}>{customer.name}</option>
                                            ))}
                                        </select>
                                    )}
                                </FormField>
                                <FormField name="referral_type" label="Tür">
                                    {() => (
                                        <select
                                            className="h-9 w-full rounded-md border border-border bg-background px-3 text-sm"
                                            value={workForm.data.referral_type}
                                            onChange={(e) => workForm.setData('referral_type', e.target.value as never)}
                                        >
                                            <option value="work">Yürütülen iş</option>
                                            <option value="referral">Yönlendirme</option>
                                        </select>
                                    )}
                                </FormField>
                                <FormField name="value" label="İş değeri (matrah)" error={workForm.errors.value}>
                                    {(props) => (
                                        <Input {...props} type="number" step="0.0001" min="0" value={workForm.data.value} onChange={(e) => workForm.setData('value', e.target.value)} />
                                    )}
                                </FormField>
                            </div>
                            <Button type="submit" size="sm" loading={workForm.processing}>İş ekle</Button>
                        </form>
                    )}
                </section>
            </AppShell>
        </>
    );
}
