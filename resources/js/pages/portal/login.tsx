import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/inputs';
import { PortalLayout } from '@/pages/portal/layout';
import { route } from '@/lib/route';
import { Head, useForm } from '@inertiajs/react';

export default function PortalLogin() {
    const form = useForm({ email: '', password: '' });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(route('portal.login.submit'), {
            onSuccess: () => {},
        });
    };

    return (
        <>
            <Head title="Müşteri Portalı — Giriş" />
            <PortalLayout title="Giriş">
                <div className="mx-auto max-w-sm rounded-xl border border-border bg-surface p-5">
                    <h2 className="mb-4 text-lg font-semibold">Giriş</h2>
                    <form className="space-y-4" onSubmit={submit}>
                        <FormField name="email" label="E-posta" error={form.errors.email} required>
                            {(props) => (
                                <Input {...props} type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="password" label="Parola" error={form.errors.password} required>
                            {(props) => (
                                <Input {...props} type="password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} />
                            )}
                        </FormField>
                        <Button type="submit" loading={form.processing} className="w-full">Giriş yap</Button>
                    </form>
                </div>
            </PortalLayout>
        </>
    );
}
