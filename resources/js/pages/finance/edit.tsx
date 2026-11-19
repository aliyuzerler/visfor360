import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input, Textarea } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';

interface CategoryOption { id: number; label: string; direction: string }
interface Option { id: number; name: string }

export default function FinanceEdit({
    entry,
    categories,
    customers,
    projects,
}: {
    entry: {
        id: number;
        direction: string;
        entry_type: string;
        category_id: number | null;
        amount: string;
        currency_code: string;
        entry_date: string | null;
        status: string;
        customer_id: number | null;
        project_id: number | null;
        description: string | null;
    };
    categories: CategoryOption[];
    customers: Option[];
    projects: Option[];
}) {
    const form = useForm({
        direction: entry.direction,
        entry_type: entry.entry_type,
        category_id: entry.category_id ? String(entry.category_id) : '',
        amount: entry.amount,
        currency_code: entry.currency_code,
        entry_date: entry.entry_date ?? '',
        customer_id: entry.customer_id ? String(entry.customer_id) : '',
        project_id: entry.project_id ? String(entry.project_id) : '',
        description: entry.description ?? '',
    });

    const filteredCategories = categories.filter((category) => category.direction === form.data.direction);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.put(route('finance.update', entry.id), {
            onSuccess: () => router.get(route('finance.index')),
        });
    };

    return (
        <>
            <Head title="Düzenle" />
            <AppShell title="Finans">
                <PageHeader
                    title="Finans kaydını düzenle"
                    description="Onaylı kayıtlarda tutar/yön/tarih değişimi eski kaydı iptal edip düzeltme kaydı açar; geçmiş korunur."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Finans', href: route('finance.index') },
                        { label: 'Düzenle' },
                    ]}
                />
                <form className="max-w-2xl space-y-5" onSubmit={submit}>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField name="direction" label="Yön" required>
                            {() => (
                                <select
                                    className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                    value={form.data.direction}
                                    onChange={(e) => { form.setData('direction', e.target.value); form.setData('category_id', ''); }}
                                >
                                    <option value="income">Gelir</option>
                                    <option value="expense">Gider</option>
                                </select>
                            )}
                        </FormField>
                        <FormField name="entry_type" label="Kayıt tipi" required>
                            {() => (
                                <select
                                    className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                    value={form.data.entry_type}
                                    onChange={(e) => form.setData('entry_type', e.target.value as never)}
                                >
                                    <option value="estimated">Tahmini</option>
                                    <option value="actual">Gerçekleşen</option>
                                </select>
                            )}
                        </FormField>
                        <FormField name="category_id" label="Kategori" error={form.errors.category_id}>
                            {() => (
                                <select
                                    className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                    value={form.data.category_id}
                                    onChange={(e) => form.setData('category_id', e.target.value)}
                                >
                                    <option value="">Seçilmedi</option>
                                    {filteredCategories.map((category) => (
                                        <option key={category.id} value={category.id}>{category.label}</option>
                                    ))}
                                </select>
                            )}
                        </FormField>
                        <FormField name="amount" label="Tutar" error={form.errors.amount} required>
                            {(props) => (
                                <Input {...props} type="number" step="0.0001" min="0" value={form.data.amount} onChange={(e) => form.setData('amount', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="currency_code" label="Para birimi (ISO)" error={form.errors.currency_code} required>
                            {(props) => (
                                <Input {...props} maxLength={3} value={form.data.currency_code} onChange={(e) => form.setData('currency_code', e.target.value.toUpperCase())} />
                            )}
                        </FormField>
                        <FormField name="entry_date" label="Tarih" error={form.errors.entry_date} required>
                            {(props) => (
                                <Input {...props} type="date" value={form.data.entry_date} onChange={(e) => form.setData('entry_date', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="customer_id" label="Müşteri">
                            {() => (
                                <select
                                    className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                    value={form.data.customer_id}
                                    onChange={(e) => form.setData('customer_id', e.target.value)}
                                >
                                    <option value="">Seçilmedi</option>
                                    {customers.map((customer) => (
                                        <option key={customer.id} value={customer.id}>{customer.name}</option>
                                    ))}
                                </select>
                            )}
                        </FormField>
                        <FormField name="project_id" label="Proje">
                            {() => (
                                <select
                                    className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                    value={form.data.project_id}
                                    onChange={(e) => form.setData('project_id', e.target.value)}
                                >
                                    <option value="">Seçilmedi</option>
                                    {projects.map((project) => (
                                        <option key={project.id} value={project.id}>{project.name}</option>
                                    ))}
                                </select>
                            )}
                        </FormField>
                    </div>
                    <FormField name="description" label="Açıklama" error={form.errors.description}>
                        {(props) => (
                            <Textarea {...props} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                        )}
                    </FormField>
                    <div className="flex gap-2">
                        <Button type="submit" loading={form.processing}>Kaydet</Button>
                        <Button type="button" variant="secondary" onClick={() => router.get(route('finance.index'))}>İptal</Button>
                    </div>
                </form>
            </AppShell>
        </>
    );
}
