import { CustomerFormPage } from '@/components/customers/customer-form';
import { route } from '@/lib/route';

export default function CustomerCreate({
    customerTypes,
    fieldDefinitions,
}: {
    customerTypes: { id: number; key: string; label: string }[];
    fieldDefinitions: { id: number; key: string; label: string; type: string; required: boolean; options: string[] | null; default_value: string | null }[];
}) {
    return (
        <CustomerFormPage
            mode="create"
            title="Yeni müsteri"
            breadcrumbs={[
                { label: 'Ana sayfa', href: route('dashboard') },
                { label: 'Musteriler', href: route('customers.index') },
                { label: 'Yeni' },
            ]}
            customerData={{
                id: null,
                customer_type_id: null,
                name: '',
                trade_name: null,
                tax_number: null,
                tax_office: null,
                email: null,
                phone: null,
                city: null,
                address: null,
                custom_fields: {},
            }}
            customerTypes={customerTypes}
            fieldDefinitions={fieldDefinitions}
        />
    );
}
