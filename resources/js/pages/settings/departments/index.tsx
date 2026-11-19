import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/inputs';
import { FormField } from '@/components/ui/form-field';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { route } from '@/lib/route';
import { PageProps } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from '@/components/ui/toast';

interface DepartmentRow {
    id: number;
    name: string;
    code: string;
    description: string | null;
    is_active: boolean;
    memberships_count: number;
}

/** Departman yönetimi: oluşturma dialog'u, arşivleme (silme yok). */
export default function DepartmentsIndex({
    departments,
    can,
}: { departments: { data: DepartmentRow[]; current_page: number; last_page: number }; can: Record<string, boolean> }) {
    const [editing, setEditing] = useState<DepartmentRow | null>(null);
    const form = useForm({ name: '', code: '', description: '' });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editing) {
            form.put(route('settings.departments.update', editing.id), {
                onSuccess: () => {
                    toast.success('Departman güncellendi.');
                    setEditing(null);
                    form.reset();
                },
                onError: () => toast.error('Lütfen form hatalarını düzeltin.'),
            });
        } else {
            form.post(route('settings.departments.store'), {
                onSuccess: () => {
                    toast.success('Departman oluşturuldu.');
                    form.reset();
                },
                onError: () => toast.error('Lütfen form hatalarını düzeltin.'),
            });
        }
    };

    const archive = (id: number) => {
        router.patch(route('settings.departments.archive', id), {}, {
            onSuccess: () => toast.success('Departman arşivlendi.'),
            onError: () => toast.error('Departman arşivlenemedi.'),
        });
    };

    return (
        <>
            <Head title="Departmanlar" />
            <AppShell title="Departmanlar">
                <PageHeader
                    title="Departmanlar"
                    description="Ekip yapısı ve görev kapsamı için temel referans. Silme yerine arşivleme yapılır."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Ayarlar', href: route('settings.index') },
                        { label: 'Departmanlar' },
                    ]}
                    actions={
                        can.create && (
                            <Dialog>
                                <DialogTrigger asChild>
                                    <Button size="sm">Yeni departman</Button>
                                </DialogTrigger>
                                <DialogContent
                                    title={editing ? 'Departmanı düzenle' : 'Yeni departman'}
                                    description="Kod alanı tekil olmalıdır; sistem referanslarında kullanılır."
                                >
                                    <form className="space-y-4" onSubmit={submit}>
                                        <FormField name="name" label="Ad" error={form.errors.name} required>
                                            {(props) => (
                                                <Input
                                                    {...props}
                                                    value={form.data.name}
                                                    onChange={(e) => form.setData('name', e.target.value)}
                                                />
                                            )}
                                        </FormField>
                                        <FormField name="code" label="Kod" hint="Örn. hibe, mimari, ihracat" error={form.errors.code} required>
                                            {(props) => (
                                                <Input
                                                    {...props}
                                                    value={form.data.code}
                                                    onChange={(e) => form.setData('code', e.target.value)}
                                                />
                                            )}
                                        </FormField>
                                        <FormField name="description" label="Açıklama" error={form.errors.description}>
                                            {(props) => (
                                                <Input
                                                    {...props}
                                                    value={form.data.description ?? ''}
                                                    onChange={(e) => form.setData('description', e.target.value)}
                                                />
                                            )}
                                        </FormField>
                                        <div className="flex justify-end gap-2">
                                            <Button type="button" variant="secondary" size="sm" onClick={() => setEditing(null)}>
                                                İptal
                                            </Button>
                                            <Button type="submit" size="sm" loading={form.processing}>
                                                Kaydet
                                            </Button>
                                        </div>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        )
                    }
                />

                {departments.data.length === 0 ? (
                    <EmptyState
                        title="Departman yok"
                        description="Ekip yapınızı modellemek için ilk departmanı oluşturun."
                    />
                ) : (
                    <Table>
                        <THead>
                            <TR>
                                <TH scope="col">Departman</TH>
                                <TH scope="col">Kod</TH>
                                <TH scope="col">Üye</TH>
                                <TH scope="col">Durum</TH>
                                <TH scope="col">İşlemler</TH>
                            </TR>
                        </THead>
                        <TBody>
                            {departments.data.map((department) => (
                                <TR key={department.id}>
                                    <TD>
                                        <p className="font-medium">{department.name}</p>
                                        {department.description && (
                                            <p className="text-xs text-muted-foreground">{department.description}</p>
                                        )}
                                    </TD>
                                    <TD>
                                        <Badge>{department.code}</Badge>
                                    </TD>
                                    <TD>{department.memberships_count}</TD>
                                    <TD>
                                        {department.is_active ? (
                                            <Badge variant="success">Aktif</Badge>
                                        ) : (
                                            <Badge variant="danger">Arşivli</Badge>
                                        )}
                                    </TD>
                                    <TD>
                                        <div className="flex gap-2">
                                            {can.update && (
                                                <Button
                                                    size="sm"
                                                    variant="secondary"
                                                    onClick={() => {
                                                        setEditing(department);
                                                        form.setData({
                                                            name: department.name,
                                                            code: department.code,
                                                            description: department.description ?? '',
                                                        });
                                                    }}
                                                >
                                                    Düzenle
                                                </Button>
                                            )}
                                            {can.archive && department.is_active && (
                                                <Button size="sm" variant="secondary" onClick={() => archive(department.id)}>
                                                    Arşivle
                                                </Button>
                                            )}
                                        </div>
                                    </TD>
                                </TR>
                            ))}
                        </TBody>
                    </Table>
                )}
            </AppShell>
        </>
    );
}
