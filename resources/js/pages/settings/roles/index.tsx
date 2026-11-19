import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from '@/components/ui/toast';

interface RoleRow {
    id: number;
    key: string;
    label: string;
    is_system: boolean;
    permissions: { id: number; key: string; label: string }[];
}

interface PermissionRow {
    id: number;
    key: string;
    module: string;
    action: string;
    label: string;
}

/**
 * Rol-izin matrisi. Kaydetme sunucuda roles.update izniyle doğrulanır;
 * sistem rolleri arşivlenemez. Değişiklik audit'e yazılır.
 */
export default function RolesIndex({
    roles,
    permissions,
    userCounts,
}: { roles: RoleRow[]; permissions: PermissionRow[]; userCounts: Record<string, number> }) {
    const [selected, setSelected] = useState<RoleRow | null>(null);
    const form = useForm<{ permission_ids: number[] }>({ permission_ids: [] });

    const openRole = (role: RoleRow) => {
        setSelected(role);
        form.setData('permission_ids', role.permissions.map((p) => p.id));
    };

    const save = () => {
        if (!selected) return;
        form.put(route('settings.roles.update-permissions', selected.id), {
            onSuccess: () => toast.success('Rol izinleri güncellendi.'),
            onError: () => toast.error('Rol izinleri güncellenemedi.'),
        });
    };

    const modules = [...new Set(permissions.map((p) => p.module))];

    return (
        <>
            <Head title="Roller ve İzinler" />
            <AppShell title="Roller ve İzinler">
                <PageHeader
                    title="Roller ve İzinler"
                    description="Rol matrisi öneridir; müşteri onayıyla kalıcılaştırılır (D-04). İzinler modül/aksiyon bazlıdır."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Ayarlar', href: route('settings.index') },
                        { label: 'Roller ve İzinler' },
                    ]}
                />

                <div className="grid gap-4 lg:grid-cols-3">
                    <div className="space-y-2 lg:col-span-1">
                        {roles.map((role) => (
                            <button
                                key={role.id}
                                type="button"
                                onClick={() => openRole(role)}
                                className={
                                    'w-full rounded-lg border p-3 text-left text-sm transition-colors ' +
                                    (selected?.id === role.id
                                        ? 'border-primary bg-primary/5'
                                        : 'border-border bg-surface hover:bg-surface-muted')
                                }
                                aria-pressed={selected?.id === role.id}
                            >
                                <div className="flex items-center justify-between gap-2">
                                    <span className="font-medium text-foreground">{role.label}</span>
                                    <Badge variant={role.is_system ? 'neutral' : 'info'}>
                                        {role.is_system ? 'sistem' : 'özel'}
                                    </Badge>
                                </div>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {role.permissions.length} izin · {userCounts[role.id] ?? 0} kullanıcı
                                </p>
                            </button>
                        ))}
                    </div>

                    <div className="lg:col-span-2">
                        {selected ? (
                            <div className="rounded-xl border border-border bg-surface p-5 shadow-[var(--shadow-card)]">
                                <h2 className="mb-1 text-sm font-semibold text-foreground">
                                    {selected.label} — izin matrisi
                                </h2>
                                <p className="mb-4 text-xs text-muted-foreground">
                                    Rolün sahip olacağı izinleri seçin; değişiklikler audit log'a yazılır.
                                </p>
                                <div className="space-y-4">
                                    {modules.map((module) => (
                                        <fieldset key={module} className="rounded-lg border border-border p-3">
                                            <legend className="px-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                                {module}
                                            </legend>
                                            <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                                {permissions
                                                    .filter((p) => p.module === module)
                                                    .map((permission) => (
                                                        <label
                                                            key={permission.id}
                                                            className="flex items-center gap-2 text-sm"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                className="h-4 w-4 accent-[var(--color-primary)]"
                                                                checked={form.data.permission_ids.includes(permission.id)}
                                                                onChange={(e) =>
                                                                    form.setData(
                                                                        'permission_ids',
                                                                        e.target.checked
                                                                            ? [...form.data.permission_ids, permission.id]
                                                                            : form.data.permission_ids.filter(
                                                                                    (id) => id !== permission.id,
                                                                                ),
                                                                    )
                                                                }
                                                            />
                                                            <span>{permission.action}</span>
                                                        </label>
                                                    ))}
                                            </div>
                                        </fieldset>
                                    ))}
                                </div>
                                <div className="mt-4 flex gap-2">
                                    <Button size="sm" loading={form.processing} onClick={save}>
                                        Kaydet
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="secondary"
                                        onClick={() => setSelected(null)}
                                    >
                                        Kapat
                                    </Button>
                                </div>
                            </div>
                        ) : (
                            <div className="rounded-xl border border-dashed border-border bg-surface p-8 text-center text-sm text-muted-foreground">
                                İzin matrisini düzenlemek için soldaki listeden bir rol seçin.
                            </div>
                        )}
                    </div>
                </div>
            </AppShell>
        </>
    );
}
