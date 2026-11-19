import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input, Textarea } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';

interface Option { id: number; name: string }
interface ServiceOption { id: number; label: string; pipeline_id: number }

export default function ProjectCreate({
    customers,
    serviceTypes,
    departments,
    users,
}: {
    customers: Option[];
    serviceTypes: ServiceOption[];
    departments: Option[];
    users: Option[];
}) {
    const form = useForm({
        name: '',
        description: '',
        customer_id: '' as string,
        service_type_id: '' as string,
        department_id: '' as string,
        responsible_user_id: '' as string,
        start_date: '',
        due_date: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(route('projects.store'), {
            onSuccess: () => router.get(route('projects.index')),
        });
    };

    const select = <K extends keyof typeof form.data>(name: K, options: Option[], placeholder: string) => (
        <select
            className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
            value={String(form.data[name] ?? '')}
            onChange={(e) => form.setData(name, e.target.value as never)}
        >
            <option value="">{placeholder}</option>
            {options.map((option) => (
                <option key={option.id} value={option.id}>{option.name}</option>
            ))}
        </select>
    );

    return (
        <>
            <Head title="Yeni proje" />
            <AppShell title="Projeler">
                <PageHeader
                    title="Yeni proje"
                    description="Proje, seçilen hizmetin pipeline'ına bağlanır; ilk aktif aşamadan başlar."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Projeler', href: route('projects.index') },
                        { label: 'Yeni' },
                    ]}
                />
                <form className="max-w-2xl space-y-5" onSubmit={submit}>
                    <FormField name="name" label="Proje adı" error={form.errors.name} required>
                        {(props) => (
                            <Input {...props} value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                        )}
                    </FormField>
                    <FormField name="description" label="Açıklama" error={form.errors.description}>
                        {(props) => (
                            <Textarea {...props} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                        )}
                    </FormField>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField name="customer_id" label="Müşteri">
                            {() => select('customer_id', customers, 'Seçilmedi')}
                        </FormField>
                        <FormField name="service_type_id" label="Hizmet" error={form.errors.service_type_id} required>
                            {() => (
                                <select
                                    className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                    value={String(form.data.service_type_id)}
                                    onChange={(e) => form.setData('service_type_id', e.target.value)}
                                    required
                                >
                                    <option value="">Seçin…</option>
                                    {serviceTypes.map((service) => (
                                        <option key={service.id} value={service.id}>{service.label}</option>
                                    ))}
                                </select>
                            )}
                        </FormField>
                        <FormField name="department_id" label="Departman">
                            {() => select('department_id', departments, 'Seçilmedi')}
                        </FormField>
                        <FormField name="responsible_user_id" label="Sorumlu">
                            {() => select('responsible_user_id', users, 'Seçilmedi')}
                        </FormField>
                        <FormField name="start_date" label="Başlangıç" error={form.errors.start_date}>
                            {(props) => (
                                <Input {...props} type="date" value={form.data.start_date} onChange={(e) => form.setData('start_date', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="due_date" label="Hedef bitiş" error={form.errors.due_date}>
                            {(props) => (
                                <Input {...props} type="date" value={form.data.due_date} onChange={(e) => form.setData('due_date', e.target.value)} />
                            )}
                        </FormField>
                    </div>
                    <div className="flex gap-2">
                        <Button type="submit" loading={form.processing}>Oluştur</Button>
                        <Button type="button" variant="secondary" onClick={() => router.get(route('projects.index'))}>İptal</Button>
                    </div>
                </form>
            </AppShell>
        </>
    );
}
