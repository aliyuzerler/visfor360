import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input, Textarea } from '@/components/ui/inputs';
import { AppShell } from '@/components/app/app-shell';
import { PageHeader, BreadcrumbItem } from '@/components/ui/page';
import { route } from '@/lib/route';
import { Head, router, useForm } from '@inertiajs/react';

export interface FieldDef {
    id: number;
    key: string;
    label: string;
    type: string;
    required: boolean;
    options: string[] | null;
    default_value: string | null;
}

export interface CustomerFormData {
    id: number | null;
    customer_type_id: number | null;
    name: string;
    trade_name: string | null;
    tax_number: string | null;
    tax_office: string | null;
    email: string | null;
    phone: string | null;
    city: string | null;
    address: string | null;
    custom_fields: Record<number, string | number | boolean | string[] | null>;
}

/**
 * Musteri formu (create/edit ortak). Dinamik ozel alanlar tip allowlist'e
 * gore render edilir; required alanlar hem istemci hem sunucuda dogrulanir.
 */
export function CustomerFormPage({
    mode,
    title,
    breadcrumbs,
    customerData,
    customerTypes,
    fieldDefinitions,
}: {
    mode: 'create' | 'edit';
    title: string;
    breadcrumbs: BreadcrumbItem[];
    customerData: CustomerFormData;
    customerTypes: { id: number; key: string; label: string }[];
    fieldDefinitions: FieldDef[];
}) {
    const form = useForm({ ...customerData, custom_fields: { ...customerData.custom_fields } });

    const setCustomField = (id: number, value: string | number | boolean | string[] | null) => {
        form.setData('custom_fields', { ...form.data.custom_fields, [id]: value });
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (mode === 'create') {
            form.post(route('customers.store'), {
                onSuccess: () => router.get(route('customers.index')),
            });
        } else {
            form.put(route('customers.update', customerData.id as number), {
                onSuccess: () => router.get(route('customers.show', customerData.id as number)),
            });
        }
    };

    return (
        <>
            <Head title={title} />
            <AppShell title={title}>
                <PageHeader title={title} description="Firma sicil karti. * zorunlu alanlari gosterir." breadcrumbs={breadcrumbs} />

                <form className="max-w-2xl space-y-5" onSubmit={submit}>
                    <FormField name="name" label="Firma adi" error={form.errors.name} required>
                        {(props) => (
                            <Input {...props} value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                        )}
                    </FormField>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField name="trade_name" label="Ticari unvan" error={form.errors.trade_name}>
                            {(props) => (
                                <Input {...props} value={form.data.trade_name ?? ''} onChange={(e) => form.setData('trade_name', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="customer_type_id" label="Musteri tipi" hint="Tipler musteri karariyla kaliciuasir">
                            {(props) => (
                                <select
                                    {...props}
                                    className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                                    value={form.data.customer_type_id ?? ''}
                                    onChange={(e) => form.setData('customer_type_id', e.target.value === '' ? null : Number(e.target.value))}
                                >
                                    <option value="">Secilmedi</option>
                                    {customerTypes.map((type) => (
                                        <option key={type.id} value={type.id}>{type.label}</option>
                                    ))}
                                </select>
                            )}
                        </FormField>
                        <FormField name="tax_number" label="Vergi numarasi" error={form.errors.tax_number}>
                            {(props) => (
                                <Input {...props} value={form.data.tax_number ?? ''} onChange={(e) => form.setData('tax_number', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="tax_office" label="Vergi dairesi" error={form.errors.tax_office}>
                            {(props) => (
                                <Input {...props} value={form.data.tax_office ?? ''} onChange={(e) => form.setData('tax_office', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="email" label="E-posta" error={form.errors.email}>
                            {(props) => (
                                <Input {...props} type="email" value={form.data.email ?? ''} onChange={(e) => form.setData('email', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="phone" label="Telefon" error={form.errors.phone}>
                            {(props) => (
                                <Input {...props} value={form.data.phone ?? ''} onChange={(e) => form.setData('phone', e.target.value)} />
                            )}
                        </FormField>
                        <FormField name="city" label="Sehir" error={form.errors.city}>
                            {(props) => (
                                <Input {...props} value={form.data.city ?? ''} onChange={(e) => form.setData('city', e.target.value)} />
                            )}
                        </FormField>
                    </div>
                    <FormField name="address" label="Adres" error={form.errors.address}>
                        {(props) => (
                            <Textarea {...props} value={form.data.address ?? ''} onChange={(e) => form.setData('address', e.target.value)} />
                        )}
                    </FormField>

                    {fieldDefinitions.length > 0 && (
                        <fieldset className="space-y-4 rounded-xl border border-border p-4">
                            <legend className="px-1 text-sm font-medium text-foreground">Ozel alanlar</legend>
                            {fieldDefinitions.map((field) => (
                                <CustomFieldInput
                                    key={field.id}
                                    field={field}
                                    value={form.data.custom_fields[field.id] ?? null}
                                    onChange={(value) => setCustomField(field.id, value)}
                                />
                            ))}
                            {form.errors.custom_fields && (
                                <p className="text-xs font-medium text-danger" role="alert">{form.errors.custom_fields}</p>
                            )}
                        </fieldset>
                    )}

                    <div className="flex gap-2">
                        <Button type="submit" loading={form.processing}>
                            {mode === 'create' ? 'Olustur' : 'Kaydet'}
                        </Button>
                        <Button
                            type="button"
                            variant="secondary"
                            onClick={() =>
                                mode === 'create'
                                    ? router.get(route('customers.index'))
                                    : router.get(route('customers.show', customerData.id as number))
                            }
                        >
                            Iptal
                        </Button>
                    </div>
                </form>
            </AppShell>
        </>
    );
}

function CustomFieldInput({
    field,
    value,
    onChange,
}: {
    field: FieldDef;
    value: string | number | boolean | string[] | null;
    onChange: (value: string | number | boolean | string[] | null) => void;
}) {
    const inputId = `custom-field-${field.id}`;

    return (
        <div>
            <label htmlFor={inputId} className="mb-1 block text-sm font-medium text-foreground">
                {field.label}
                {field.required && <span className="ml-1 text-danger" aria-hidden>*</span>}
            </label>
            {field.type === 'textarea' && (
                <Textarea
                    id={inputId}
                    value={String(value ?? '')}
                    onChange={(e) => onChange(e.target.value === '' ? null : e.target.value)}
                />
            )}
            {(field.type === 'text' || field.type === 'integer' || field.type === 'decimal' || field.type === 'date') && (
                <Input
                    id={inputId}
                    type={field.type === 'date' ? 'date' : field.type === 'integer' || field.type === 'decimal' ? 'number' : 'text'}
                    value={value === null || value === undefined ? '' : String(value)}
                    onChange={(e) => {
                        const raw = e.target.value;
                        onChange(
                            raw === ''
                                ? null
                                : field.type === 'integer'
                                    ? parseInt(raw, 10)
                                    : field.type === 'decimal'
                                        ? parseFloat(raw)
                                        : raw,
                        );
                    }}
                />
            )}
            {field.type === 'boolean' && (
                <label className="flex items-center gap-2 text-sm">
                    <input
                        id={inputId}
                        type="checkbox"
                        className="h-4 w-4 accent-[var(--color-primary)]"
                        checked={value === true || value === 1 || value === '1'}
                        onChange={(e) => onChange(e.target.checked)}
                    />
                    Evet
                </label>
            )}
            {field.type === 'select' && (
                <select
                    id={inputId}
                    className="h-9 rounded-md border border-border bg-surface px-3 text-sm"
                    value={String(value ?? '')}
                    onChange={(e) => onChange(e.target.value === '' ? null : e.target.value)}
                >
                    <option value="">Secilmedi</option>
                    {(field.options ?? []).map((option) => (
                        <option key={option} value={option}>{option}</option>
                    ))}
                </select>
            )}
            {field.type === 'multi_select' && (
                <div className="grid gap-1.5">
                    {(field.options ?? []).map((option) => {
                        const selected = Array.isArray(value) ? value.includes(option) : false;
                        return (
                            <label key={option} className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    className="h-4 w-4 accent-[var(--color-primary)]"
                                    checked={selected}
                                    onChange={(e) => {
                                        const current = Array.isArray(value) ? [...value] : [];
                                        onChange(e.target.checked ? [...current, option] : current.filter((v) => v !== option));
                                    }}
                                />
                                {option}
                            </label>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
