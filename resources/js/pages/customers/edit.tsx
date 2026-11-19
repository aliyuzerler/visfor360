import { CustomerFormPage, CustomerFormData } from '@/components/customers/customer-form';
import { route } from '@/lib/route';

export default function CustomerEdit({
    customerData,
    customerTypes,
    fieldDefinitions,
}: {
    customerData: CustomerFormData;
    customerTypes: { id: number; key: string; label: string }[];
    fieldDefinitions: { id: number; key: string; label: string; type: string; required: boolean; options: string[] | null; default_value: string | null }[];
}) {
    return (
        <CustomerFormPage
            mode="edit"
            title={`Duzenle: ${customerData.name}`}
            breadcrumbs={[
                { label: 'Ana sayfa', href: route('dashboard') },
                { label: 'Musteriler', href: route('customers.index') },
                { label: customerData.name, href: route('customers.show', customerData.id as number) },
                { label: 'Duzenle' },
            ]}
            customerData={customerData}
            customerTypes={customerTypes}
            fieldDefinitions={fieldDefinitions}
        />
    );
}
