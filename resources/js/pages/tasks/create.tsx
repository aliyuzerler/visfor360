import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input, Textarea } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';

interface Option { id: number; name: string }

/** Gorev olusturma — zorunluluklar TaskFieldSettings'ten (konfigüre edilebilir). */
export default function TaskCreate({
    users,
    customers,
    projects,
    departments,
    requirements,
}: {
    users: Option[];
    customers: Option[];
    projects: Option[];
    departments: Option[];
    requirements: Record<string, boolean>;
}) {
    const form = useForm({
        title: '',
        description: '',
        priority: 'medium',
        customer_id: '',
        project_id: '',
        department_id: '',
        start_date: '',
        due_date: '',
        assignee_id: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(route('tasks.store'), {
            onSuccess: () => router.get(route('tasks.index')),
        });
    };

    const select = <K extends keyof typeof form.data>(name: K, options: Option[], placeholder: string, required = false) => (
        <select
            className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
            value={String(form.data[name] ?? '')}
            onChange={(e) => form.setData(name, e.target.value as never)}
            required={required}
        >
            <option value="">{placeholder}</option>
            {options.map((option) => (
                <option key={option.id} value={option.id}>{option.name}</option>
            ))}
        </select>
    );

    return (
        <>
            <Head title="Yeni görev" />
            <AppShell title="Görevler">
                <PageHeader
                    title="Yeni görev"
                    description="Zorunlu alanlar yönetim ayarına göre belirler; * işaretliler zorunludur."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Görevler', href: route('tasks.index') },
                        { label: 'Yeni' },
                    ]}
                />
                <form className="max-w-2xl space-y-5" onSubmit={submit}>
                    <FormField name="title" label="Başlık" error={form.errors.title} required>
                        {(props) => (
                            <Input {...props} value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} />
                        )}
                    </FormField>
                    <FormField name="description" label="Açıklama" error={form.errors.description} required={requirements.description}>
                        {(props) => (
                            <Textarea {...props} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                        )}
                    </FormField>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField name="priority" label="Öncelik">
                            {() => (
                                <select
                                    className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                    value={form.data.priority}
                                    onChange={(e) => form.setData('priority', e.target.value as never)}
                                >
                                    <option value="low">Düşük</option>
                                    <option value="medium">Orta</option>
                                    <option value="high">Yüksek</option>
                                    <option value="urgent">Acil</option>
                                </select>
                            )}
                        </FormField>
                        <FormField name="customer_id" label="Müşteri" required={requirements.customer_id}>
                            {() => select('customer_id', customers, 'Seçilmedi', requirements.customer_id)}
                        </FormField>
                        <FormField name="project_id" label="Proje" required={requirements.project_id}>
                            {() => select('project_id', projects, 'Seçilmedi', requirements.project_id)}
                        </FormField>
                        <FormField name="department_id" label="Departman" required={requirements.department_id}>
                            {() => select('department_id', departments, 'Seçilmedi', requirements.department_id)}
                        </FormField>
                        <FormField name="start_date" label="Başlangıç" error={form.errors.start_date}>
                            {(props) => (
                                <Input {...props} type="date" value={form.data.start_date} onChange={(e) => form.setData('start_date', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="due_date" label="Son tarih" error={form.errors.due_date} required={requirements.due_date}>
                            {(props) => (
                                <Input {...props} type="date" value={form.data.due_date} onChange={(e) => form.setData('due_date', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="assignee_id" label="Sorumlu (atama)">
                            {() => select('assignee_id', users, 'Seçilmedi')}
                        </FormField>
                    </div>
                    <div className="flex gap-2">
                        <Button type="submit" loading={form.processing}>Oluştur</Button>
                        <Button type="button" variant="secondary" onClick={() => router.get(route('tasks.index'))}>İptal</Button>
                    </div>
                </form>
            </AppShell>
        </>
    );
}
