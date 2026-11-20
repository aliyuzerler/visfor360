import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input, Textarea } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';

export default function PartnerCreate() {
    const form = useForm({
        name: '',
        expertise_type: '',
        contact_name: '',
        contact_email: '',
        contact_phone: '',
        notes: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(route('partners.store'), {
            onSuccess: () => router.get(route('partners.index')),
        });
    };

    return (
        <>
            <Head title="Yeni çözüm ortağı" />
            <AppShell title="Çözüm Ortakları">
                <PageHeader
                    title="Yeni çözüm ortağı"
                    description="Ortak iç kayıttır; portal erişimi açılmaz. Uzmanlık türü müşteri kararıyla kalıcılaşır."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Çözüm Ortakları', href: route('partners.index') },
                        { label: 'Yeni' },
                    ]}
                />
                <form className="max-w-2xl space-y-5" onSubmit={submit}>
                    <FormField name="name" label="Ortak adı" error={form.errors.name} required>
                        {(props) => (
                            <Input {...props} value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                        )}
                    </FormField>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField name="expertise_type" label="Uzmanlık türü">
                            {(props) => (
                                <Input {...props} value={form.data.expertise_type} onChange={(e) => form.setData('expertise_type', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="contact_name" label="İlgili kişi">
                            {(props) => (
                                <Input {...props} value={form.data.contact_name} onChange={(e) => form.setData('contact_name', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="contact_email" label="E-posta" error={form.errors.contact_email}>
                            {(props) => (
                                <Input {...props} type="email" value={form.data.contact_email} onChange={(e) => form.setData('contact_email', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="contact_phone" label="Telefon">
                            {(props) => (
                                <Input {...props} value={form.data.contact_phone} onChange={(e) => form.setData('contact_phone', e.target.value)} />
                            )}
                        </FormField>
                    </div>
                    <FormField name="notes" label="Notlar">
                        {(props) => (
                            <Textarea {...props} value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} />
                        )}
                    </FormField>
                    <div className="flex gap-2">
                        <Button type="submit" loading={form.processing}>Kaydet</Button>
                        <Button type="button" variant="secondary" onClick={() => router.get(route('partners.index'))}>İptal</Button>
                    </div>
                </form>
            </AppShell>
        </>
    );
}
