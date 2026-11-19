import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { route } from '@/lib/route';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { toast } from '@/components/ui/toast';

interface UserRow {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    roles: { id: number; key: string; label: string }[];
    departments: { id: number; name: string }[];
    department_memberships?: unknown[];
}

/** Kullanıcı listesi: sunucu tarafı arama/sayfalama; aksiyonlar izne bağlı. */
export default function UsersIndex({
    users,
    filters,
    auth,
}: {
    users: { data: UserRow[]; current_page: number; last_page: number; links: { url: string | null; label: string; active: boolean }[] };
    filters: { search: string };
    auth: { user: { permissions: string[] } };
}) {
    const searchForm = useForm({ search: filters.search ?? '' });
    const can = (permission: string) => auth.user.permissions.includes(permission);

    const archive = (userId: number) => {
        router.patch(route('settings.users.archive', userId), {}, {
            onSuccess: () => toast.success('Kullanıcı devre dışı bırakıldı.'),
            onError: () => toast.error('Kullanıcı devre dışı bırakılamadı.'),
        });
    };

    return (
        <>
            <Head title="Kullanıcılar" />
            <AppShell title="Kullanıcılar">
                <PageHeader
                    title="Kullanıcılar"
                    description="Hesaplar, roller ve departman atamaları."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Ayarlar', href: route('settings.index') },
                        { label: 'Kullanıcılar' },
                    ]}
                    actions={
                        can('users.create') && (
                            <Button size="sm" onClick={() => router.get(route('settings.users.create'))}>
                                Yeni kullanıcı
                            </Button>
                        )
                    }
                />

                <form
                    className="mb-4 flex gap-2"
                    onSubmit={(e) => {
                        e.preventDefault();
                        searchForm.get(route('settings.users.index'), { preserveState: true });
                    }}
                >
                    <Input
                        type="search"
                        placeholder="Ad veya e-posta ara…"
                        value={searchForm.data.search}
                        onChange={(e) => searchForm.setData('search', e.target.value)}
                        aria-label="Kullanıcı ara"
                        className="max-w-xs"
                    />
                    <Button type="submit" variant="secondary" size="sm">
                        Ara
                    </Button>
                </form>

                {users.data.length === 0 ? (
                    <EmptyState
                        title="Kullanıcı bulunamadı"
                        description="Arama ölçütünüzle eşleşen kullanıcı yok veya sistemde henüz kullanıcı tanımlanmamış."
                        action={can('users.create') && (
                            <Button size="sm" onClick={() => router.get(route('settings.users.create'))}>
                                İlk kullanıcıyı oluştur
                            </Button>
                        )}
                    />
                ) : (
                    <Table>
                        <THead>
                            <TR>
                                <TH scope="col">Kullanıcı</TH>
                                <TH scope="col">Roller</TH>
                                <TH scope="col">Departmanlar</TH>
                                <TH scope="col">Durum</TH>
                                <TH scope="col">İşlemler</TH>
                            </TR>
                        </THead>
                        <TBody>
                            {users.data.map((user) => (
                                <TR key={user.id}>
                                    <TD>
                                        <p className="font-medium">{user.name}</p>
                                        <p className="text-xs text-muted-foreground">{user.email}</p>
                                    </TD>
                                    <TD>
                                        <div className="flex flex-wrap gap-1">
                                            {user.roles.map((role) => (
                                                <Badge key={role.id} variant="info">
                                                    {role.label}
                                                </Badge>
                                            ))}
                                        </div>
                                    </TD>
                                    <TD>
                                        <div className="flex flex-wrap gap-1">
                                            {(user.departments ?? []).map((department) => (
                                                <Badge key={department.id}>{department.name}</Badge>
                                            ))}
                                        </div>
                                    </TD>
                                    <TD>
                                        {user.is_active ? (
                                            <Badge variant="success">Aktif</Badge>
                                        ) : (
                                            <Badge variant="danger">Devre dışı</Badge>
                                        )}
                                    </TD>
                                    <TD>
                                        <div className="flex gap-2">
                                            {can('users.update') && (
                                                <Link
                                                    href={route('settings.users.edit', user.id)}
                                                    className="text-sm text-primary hover:underline"
                                                >
                                                    Düzenle
                                                </Link>
                                            )}
                                            {can('users.archive') && user.is_active && (
                                                <Button
                                                    size="sm"
                                                    variant="secondary"
                                                    onClick={() => archive(user.id)}
                                                >
                                                    Devre dışı bırak
                                                </Button>
                                            )}
                                        </div>
                                    </TD>
                                </TR>
                            ))}
                        </TBody>
                    </Table>
                )}

                {users.last_page > 1 && (
                    <nav className="mt-4 flex gap-2" aria-label="Sayfalama">
                        {(users.links as { url: string | null; label: string; active: boolean }[]).map((link, index) => (
                            <button
                                key={index}
                                type="button"
                                disabled={!(link as { url: string | null }).url}
                                onClick={() =>
                                    router.get(
                                        (link as { url: string }).url,
                                        {},
                                        { preserveState: true },
                                    )
                                }
                                className="rounded-md border border-border px-3 py-1 text-sm disabled:opacity-40"
                                aria-current={(link as { active: boolean }).active ? 'page' : undefined}
                                dangerouslySetInnerHTML={{ __html: (link as { label: string }).label }}
                            />
                        ))}
                    </nav>
                )}
            </AppShell>
        </>
    );
}
