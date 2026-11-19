import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input, Textarea } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';

interface Option { id: number; name: string }

export default function TaskEdit({
    task,
    customers,
    projects,
    departments,
    requirements,
}: {
    task: {
        id: number;
        title: string;
        description: string | null;
        status: string;
        priority: string;
        customer_id: number | null;
        project_id: number | null;
        department_id: number | null;
        start_date: string | null;
        due_date: string | null;
    };
    customers: Option[];
    projects: Option[];
    departments: Option[];
    requirements: Record<string, boolean>;
}) {
    const form = useForm({
        title: task.title,
        description: task.description ?? '',
        priority: task.priority,
        customer_id: task.customer_id ? String(task.customer_id) : '',
        project_id: task.project_id ? String(task.project_id) : '',
        department_id: task.department_id ? String(task.department_id) : '',
        start_date: task.start_date ?? '',
        due_date: task.due_date ?? '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.put(route('tasks.update', task.id), {
            onSuccess: () => router.get(route('tasks.show', task.id)),
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
            <Head title={`Düzenle: ${task.title}`} />
            <AppShell title="Görevler">
                <PageHeader
                    title={`Düzenle: ${task.title}`}
                    description="Tarih değişiklikleri geçmişe yazılır."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Görevler', href: route('tasks.index') },
                        { label: task.title, href: route('tasks.show', task.id) },
                        { label: 'Düzenle' },
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
                    </div>
                    <div className="flex gap-2">
                        <Button type="submit" loading={form.processing}>Kaydet</Button>
                        <Button type="button" variant="secondary" onClick={() => router.get(route('tasks.show', task.id))}>İptal</Button>
                    </div>
                </form>
            </AppShell>
        </>
    );
}
