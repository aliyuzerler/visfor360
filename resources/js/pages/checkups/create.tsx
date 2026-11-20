import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';

interface Option { id: number; name: string }
interface TemplateOption { id: number; title: string; version: number; is_example: boolean }

export default function CheckupCreate({ templates, customers }: { templates: TemplateOption[]; customers: Option[] }) {
    const form = useForm({ template_id: '', customer_id: '' });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(route('checkups.store'), {
            onSuccess: () => router.get(route('checkups.index')),
        });
    };

    return (
        <>
            <Head title="Yeni Check-Up oturumu" />
            <AppShell title="Check-Up">
                <PageHeader
                    title="Yeni Check-Up oturumu"
                    description="Şablon ve müşteri seçilir; oturum başlarken şablon sürümü kilitlenir."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Check-Up', href: route('checkups.index') },
                        { label: 'Yeni' },
                    ]}
                />
                <form className="max-w-xl space-y-5" onSubmit={submit}>
                    <FormField name="template_id" label="Şablon" error={form.errors.template_id} required>
                        {() => (
                            <select
                                className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                value={form.data.template_id}
                                onChange={(e) => form.setData('template_id', e.target.value)}
                                required
                            >
                                <option value="">Seçin…</option>
                                {templates.map((template) => (
                                    <option key={template.id} value={template.id}>
                                        {template.title} v{template.version}{template.is_example ? ' (ÖRNEK)' : ''}
                                    </option>
                                ))}
                            </select>
                        )}
                    </FormField>
                    <FormField name="customer_id" label="Müşteri" error={form.errors.customer_id} required>
                        {() => (
                            <select
                                className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                value={form.data.customer_id}
                                onChange={(e) => form.setData('customer_id', e.target.value)}
                                required
                            >
                                <option value="">Seçin…</option>
                                {customers.map((customer) => (
                                    <option key={customer.id} value={customer.id}>{customer.name}</option>
                                ))}
                            </select>
                        )}
                    </FormField>
                    <div className="flex gap-2">
                        <Button type="submit" loading={form.processing}>Oturum başlat</Button>
                        <Button type="button" variant="secondary" onClick={() => router.get(route('checkups.index'))}>İptal</Button>
                    </div>
                </form>
            </AppShell>
        </>
    );
}
