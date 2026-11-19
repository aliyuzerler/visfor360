import { Button } from '@/components/ui/button';
import { Checkbox, FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';

interface RoleOption {
    id: number;
    key: string;
    label: string;
}

/** Yeni kullanıcı oluşturma: validasyon hem istemcide (min alanlar) hem sunucuda. */
export default function UserCreate({
    roles,
    departments,
}: { roles: RoleOption[]; departments: { id: number; name: string }[] }) {
    const form = useForm({
        name: '',
        email: '',
        password: '',
        role_ids: [] as number[],
        department_ids: [] as number[],
    });

    const toggle = (list: number[], id: number): number[] =>
        list.includes(id) ? list.filter((x) => x !== id) : [...list, id];

    return (
        <>
            <Head title="Yeni kullanıcı" />
            <AppShell title="Yeni kullanıcı">
                <PageHeader
                    title="Yeni kullanıcı"
                    description="Hesap oluşturulunca ilk parola belirleyin; kullanıcı ilk girişten sonra değiştirir."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Ayarlar', href: route('settings.index') },
                        { label: 'Kullanıcılar', href: route('settings.users.index') },
                        { label: 'Yeni' },
                    ]}
                />

                <form
                    className="max-w-xl space-y-5"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post(route('settings.users.store'), {
                            onSuccess: () => router.get(route('settings.users.index')),
                        });
                    }}
                >
                    <FormField name="name" label="Ad Soyad" error={form.errors.name} required>
                        {(props) => (
                            <Input
                                {...props}
                                value={form.data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                                autoFocus
                            />
                        )}
                    </FormField>
                    <FormField name="email" label="E-posta" error={form.errors.email} required>
                        {(props) => (
                            <Input
                                {...props}
                                type="email"
                                value={form.data.email}
                                onChange={(e) => form.setData('email', e.target.value)}
                            />
                        )}
                    </FormField>
                    <FormField
                        name="password"
                        label="Parola"
                        hint="En az 12 karakter; harf ve sayı içermeli."
                        error={form.errors.password}
                        required
                    >
                        {(props) => (
                            <Input
                                {...props}
                                type="password"
                                autoComplete="new-password"
                                value={form.data.password}
                                onChange={(e) => form.setData('password', e.target.value)}
                            />
                        )}
                    </FormField>

                    <fieldset className="space-y-2">
                        <legend className="text-sm font-medium text-foreground">Roller</legend>
                        {form.errors.role_ids && (
                            <p className="text-xs text-danger" role="alert">{form.errors.role_ids}</p>
                        )}
                        <div className="grid gap-2 sm:grid-cols-2">
                            {roles.map((role) => (
                                <Checkbox
                                    key={role.id}
                                    label={role.label}
                                    checked={form.data.role_ids.includes(role.id)}
                                    onChange={() => form.setData('role_ids', toggle(form.data.role_ids, role.id))}
                                />
                            ))}
                        </div>
                    </fieldset>

                    <fieldset className="space-y-2">
                        <legend className="text-sm font-medium text-foreground">Departmanlar</legend>
                        <div className="grid gap-2 sm:grid-cols-2">
                            {departments.map((department) => (
                                <Checkbox
                                    key={department.id}
                                    label={department.name}
                                    checked={form.data.department_ids.includes(department.id)}
                                    onChange={() =>
                                        form.setData('department_ids', toggle(form.data.department_ids, department.id))
                                    }
                                />
                            ))}
                        </div>
                    </fieldset>

                    <div className="flex gap-2">
                        <Button type="submit" loading={form.processing}>
                            Oluştur
                        </Button>
                        <Button type="button" variant="secondary" onClick={() => router.get(route('settings.users.index'))}>
                            İptal
                        </Button>
                    </div>
                </form>
            </AppShell>
        </>
    );
}
