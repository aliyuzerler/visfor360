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

export interface UserEditData {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    role_ids: number[];
    department_ids: number[];
}

/** Kullanıcı düzenleme. Parola boş bırakılırsa değişmez. */
export default function UserEdit({
    userData,
    roles,
    departments,
}: { userData: UserEditData; roles: RoleOption[]; departments: { id: number; name: string }[] }) {
    const form = useForm({
        name: userData.name,
        email: userData.email,
        password: '',
        is_active: userData.is_active,
        role_ids: userData.role_ids,
        department_ids: userData.department_ids,
    });

    const toggle = (list: number[], id: number): number[] =>
        list.includes(id) ? list.filter((x) => x !== id) : [...list, id];

    return (
        <>
            <Head title="Kullanıcı düzenle" />
            <AppShell title="Kullanıcı düzenle">
                <PageHeader
                    title="Kullanıcı düzenle"
                    description="Rol/departman atamaları ve hesap durumu. Değişiklikler audit log'a yazılır."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Ayarlar', href: route('settings.index') },
                        { label: 'Kullanıcılar', href: route('settings.users.index') },
                        { label: userData.name },
                    ]}
                />

                <form
                    className="max-w-xl space-y-5"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.put(route('settings.users.update', userData.id), {
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
                        label="Yeni parola (isteğe bağlı)"
                        hint="Boş bırakılırsa parola değişmez. Değişirse eski oturumlar kapatılır."
                        error={form.errors.password}
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

                    <Checkbox
                        label="Hesap aktif"
                        checked={form.data.is_active}
                        onChange={(e) => form.setData('is_active', e.target.checked)}
                    />

                    <fieldset className="space-y-2">
                        <legend className="text-sm font-medium text-foreground">Roller</legend>
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
                            Kaydet
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
