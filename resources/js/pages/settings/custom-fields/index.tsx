import { Badge } from '@/components/ui/indicators';
import { Button } from '@/components/ui/button';
import { Checkbox, FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/inputs';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';
import { AppShell } from '@/components/app/app-shell';
import { EmptyState, PageHeader } from '@/components/ui/page';
import { TBody, TD, TH, THead, TR, Table } from '@/components/ui/table';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from '@/components/ui/toast';

interface DefinitionRow {
    id: number;
    key: string;
    label: string;
    type: string;
    required: boolean;
    options: string[] | null;
    searchable: boolean;
    filterable: boolean;
    reportable: boolean;
    display_order: number;
    is_active: boolean;
    values_count: number;
}

/** Custom field builder: tanim CRUD + deactivate; tum islemler auditli. */
export default function CustomFieldsIndex({
    definitions,
    types,
}: {
    definitions: DefinitionRow[];
    types: string[];
}) {
    const [editing, setEditing] = useState<DefinitionRow | null>(null);
    const [dialogOpen, setDialogOpen] = useState(false);
    const form = useForm({
        key: '',
        label: '',
        type: 'text',
        required: false,
        default_value: '',
        options: [] as string[],
        searchable: false,
        filterable: false,
        reportable: false,
        display_order: 0,
        is_active: true,
    });
    const [optionText, setOptionText] = useState('');

    const openNew = () => {
        setEditing(null);
        form.reset();
        form.setData('type', 'text');
        setOptionText('');
        setDialogOpen(true);
    };

    const openEdit = (definition: DefinitionRow) => {
        setEditing(definition);
        form.setData({
            key: definition.key,
            label: definition.label,
            type: definition.type,
            required: definition.required,
            default_value: '',
            options: definition.options ?? [],
            searchable: definition.searchable,
            filterable: definition.filterable,
            reportable: definition.reportable,
            display_order: definition.display_order,
            is_active: definition.is_active,
        });
        setOptionText((definition.options ?? []).join(', '));
        setDialogOpen(true);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        const options = form.data.type === 'select' || form.data.type === 'multi_select'
            ? optionText.split(',').map((option) => option.trim()).filter(Boolean)
            : [];

        form.setData('options', options);

        if (editing) {
            form.put(route('settings.custom-fields.update', editing.id), {
                onSuccess: () => { toast.success('Tanım güncellendi.'); setDialogOpen(false); },
                onError: () => toast.error('Form hatalarını düzeltin.'),
            });
        } else {
            form.post(route('settings.custom-fields.store'), {
                onSuccess: () => { toast.success('Tanım oluşturuldu.'); setDialogOpen(false); },
                onError: () => toast.error('Form hatalarını düzeltin.'),
            });
        }
    };

    const deactivate = (id: number) => {
        router.patch(route('settings.custom-fields.deactivate', id), {}, {
            onSuccess: () => toast.success('Alan devre dışı bırakıldı.'),
        });
    };

    return (
        <>
            <Head title="Özel Alanlar" />
            <AppShell title="Özel Alanlar">
                <PageHeader
                    title="Özel Alanlar (Dinamik Sicil)"
                    description="Kod yazmadan müşteri siciline alan ekleyin. Tipler allowlist ile sınırlıdır; tip değişikliği mevcut değer varsa engellenir (ADR-0004)."
                    breadcrumbs={[
                        { label: 'Ana sayfa', href: route('dashboard') },
                        { label: 'Ayarlar', href: route('settings.index') },
                        { label: 'Özel Alanlar' },
                    ]}
                    actions={
                        <Button size="sm" onClick={openNew}>
                            Yeni alan
                        </Button>
                    }
                />

                {definitions.length === 0 ? (
                    <EmptyState
                        title="Özel alan tanımı yok"
                        description="Örn. 'Faaliyet Alanı' (text), 'Çalışan Sayısı' (integer), 'Sektör' (select) gibi alanlar ekleyebilirsiniz."
                        action={<Button size="sm" onClick={openNew}>İlk alanı oluştur</Button>}
                    />
                ) : (
                    <Table>
                        <THead>
                            <TR>
                                <TH scope="col">Alan</TH>
                                <TH scope="col">Anahtar</TH>
                                <TH scope="col">Tip</TH>
                                <TH scope="col">Metadata</TH>
                                <TH scope="col">Değer</TH>
                                <TH scope="col">Durum</TH>
                                <TH scope="col">İşlemler</TH>
                            </TR>
                        </THead>
                        <TBody>
                            {definitions.map((definition) => (
                                <TR key={definition.id}>
                                    <TD className="font-medium">{definition.label}</TD>
                                    <TD className="text-xs text-muted-foreground">{definition.key}</TD>
                                    <TD><Badge variant="info">{definition.type}</Badge></TD>
                                    <TD className="text-xs text-muted-foreground">
                                        {[
                                            definition.required && 'zorunlu',
                                            definition.searchable && 'arama',
                                            definition.filterable && 'filtre',
                                            definition.reportable && 'rapor',
                                        ].filter(Boolean).join(', ') || '—'}
                                    </TD>
                                    <TD>{definition.values_count}</TD>
                                    <TD>
                                        {definition.is_active
                                            ? <Badge variant="success">Aktif</Badge>
                                            : <Badge variant="danger">Devre dışı</Badge>}
                                    </TD>
                                    <TD>
                                        <div className="flex gap-2">
                                            <Button size="sm" variant="secondary" onClick={() => openEdit(definition)}>
                                                Düzenle
                                            </Button>
                                            {definition.is_active && (
                                                <Button size="sm" variant="secondary" onClick={() => deactivate(definition.id)}>
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

                <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                    <DialogContent
                        title={editing ? 'Alanı düzenle' : 'Yeni özel alan'}
                        description="Anahtar, form verilerinde ve raporlarda sistem adı olarak kullanılır."
                    >
                        <form className="space-y-4" onSubmit={submit}>
                            <FormField name="label" label="Görünen ad" error={form.errors.label} required>
                                {(props) => (
                                    <Input {...props} value={form.data.label} onChange={(e) => form.setData('label', e.target.value)} />
                                )}
                            </FormField>
                            <FormField name="key" label="Anahtar (sistem)" hint="küçük harf, tire ile: ornek-alan" error={form.errors.key} required>
                                {(props) => (
                                    <Input {...props} value={form.data.key} onChange={(e) => form.setData('key', e.target.value)} />
                                )}
                            </FormField>
                            <FormField name="type" label="Tip" error={form.errors.type} required>
                                {(props) => (
                                    <select
                                        {...props}
                                        className="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm"
                                        value={form.data.type}
                                        onChange={(e) => form.setData('type', e.target.value)}
                                    >
                                        {types.map((type) => (
                                            <option key={type} value={type}>{type}</option>
                                        ))}
                                    </select>
                                )}
                            </FormField>
                            {(form.data.type === 'select' || form.data.type === 'multi_select') && (
                                <FormField
                                    name="options"
                                    label="Seçenekler"
                                    hint="Virgülle ayırın: Hibe, Ruhsat, İhracat"
                                    error={form.errors.options}
                                >
                                    {(props) => (
                                        <Input
                                            {...props}
                                            value={optionText}
                                            onChange={(e) => setOptionText(e.target.value)}
                                        />
                                    )}
                                </FormField>
                            )}
                            <div className="flex flex-wrap gap-4">
                                <Checkbox label="Zorunlu" checked={form.data.required} onChange={(e) => form.setData('required', e.target.checked)} />
                                <Checkbox label="Aramada kullan" checked={form.data.searchable} onChange={(e) => form.setData('searchable', e.target.checked)} />
                                <Checkbox label="Filtrede kullan" checked={form.data.filterable} onChange={(e) => form.setData('filterable', e.target.checked)} />
                                <Checkbox label="Raporlarda kullan" checked={form.data.reportable} onChange={(e) => form.setData('reportable', e.target.checked)} />
                            </div>
                            <FormField name="display_order" label="Görüntüleme sırası" error={form.errors.display_order}>
                                {(props) => (
                                    <Input
                                        {...props}
                                        type="number"
                                        value={form.data.display_order}
                                        onChange={(e) => form.setData('display_order', parseInt(e.target.value || '0', 10))}
                                    />
                                )}
                            </FormField>
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="secondary" size="sm" onClick={() => setDialogOpen(false)}>
                                    İptal
                                </Button>
                                <Button type="submit" size="sm" loading={form.processing}>
                                    Kaydet
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </AppShell>
        </>
    );
}
