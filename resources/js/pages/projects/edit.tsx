import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input, Textarea } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';

interface Option { id: number; name: string }

export default function ProjectEdit({
    project,
    customers,
    serviceTypes,
    departments,
    users,
}: {
    project: {
        id: number;
        name: string;
        description: string | null;
        customer_id: number | null;
        service_type_id: number;
        department_id: number | null;
        responsible_user_id: number | null;
        start_date: string | null;
        due_date: string | null;
    };
    customers: Option[];
    serviceTypes: Option[];
    departments: Option[];
    users: Option[];
}) {
    const form = useForm({
        name: project.name,
        description: project.description ?? '',
        customer_id: project.customer_id ? String(project.customer_id) : '',
        service_type_id: String(project.service_type_id),
        department_id: project.department_id ? String(project.department_id) : '',
        responsible_user_id: project.responsible_user_id ? String(project.responsible_user_id) : '',
        start_date: project.start_date ?? '',
        due_date: project.due_date ?? '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.put(route('projects.update', project.id), {
            onSuccess: () => router.get(route('projects.show', project.id)),
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
            <Head title={`Düzenle: ${project.name}`} />
            <AppShell title="Projeler">
                <PageHeader
                    title={`Düzenle: ${project.name}`}
                    description="Hizmet değişirse proje, yeni pipeline'ın ilk aktif aşamasına alınır; önceki aşama geçmişi korunur."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Projeler', href: route('projects.index') },
                        { label: project.name, href: route('projects.show', project.id) },
                        { label: 'Düzenle' },
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
                            {() => select('service_type_id', serviceTypes, 'Seçin…')}
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
                        <Button type="submit" loading={form.processing}>Kaydet</Button>
                        <Button type="button" variant="secondary" onClick={() => router.get(route('projects.show', project.id))}>İptal</Button>
                    </div>
                </form>
            </AppShell>
        </>
    );
}
