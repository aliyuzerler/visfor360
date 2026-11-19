<?php

namespace App\Http\Controllers\Customers;

use App\Domain\Audit\AuditLogger;
use App\Domain\Customers\Actions\CustomFieldService;
use App\Domain\Customers\Models\Customer;
use App\Domain\Customers\Models\CustomerType;
use App\Domain\Customers\Models\CustomFieldDefinition;
use App\Domain\Customers\Models\CustomFieldValue;
use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Customer::class);

        // Query bazlı arama/filtre/sayfalama; tüm kayıtlar belleğe alınmaz.
        $customers = Customer::query()
            ->with(['customerType:id,key,label', 'contacts'])
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('trade_name', 'ilike', "%{$search}%")
                        ->orWhere('tax_number', 'ilike', "%{$search}%");
                });
            })
            ->when($request->filled('type'), fn ($q) => $q->where('customer_type_id', $request->integer('type')))
            ->when($request->input('status') === 'archived', fn ($q) => $q->whereNotNull('archived_at'))
            ->when($request->input('status') === 'active', fn ($q) => $q->whereNull('archived_at'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('customers/index', [
            'customers' => $customers,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'type' => $request->filled('type') ? $request->integer('type') : null,
                'status' => $request->input('status', 'active'),
            ],
            'customerTypes' => CustomerType::query()->where('is_active', true)->orderBy('label')->get(['id', 'key', 'label']),
            'can' => [
                'create' => $request->user()->can('customers.create'),
                'archive' => $request->user()->can('customers.archive'),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Customer::class);

        return Inertia::render('customers/create', [
            'customerTypes' => CustomerType::query()->where('is_active', true)->orderBy('label')->get(['id', 'key', 'label']),
            'fieldDefinitions' => $this->activeFieldDefinitions(),
        ]);
    }

    public function store(Request $request, CustomFieldService $fields): RedirectResponse
    {
        $this->authorize('create', Customer::class);

        $validated = $request->validate([
            'customer_type_id' => ['nullable', Rule::exists('customer_types', 'id')],
            'name' => ['required', 'string', 'max:190'],
            'trade_name' => ['nullable', 'string', 'max:190'],
            'tax_number' => ['nullable', 'string', 'max:20'],
            'tax_office' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'city' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:2000'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $customer = Customer::create($validated + ['created_by' => $request->user()->id]);

        $fields->syncCustomerValues($customer, $validated['custom_fields'] ?? []);

        app(AuditLogger::class)->logModelChange('created', $customer);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Müşteri kaydı oluşturuldu.');
    }

    public function show(Customer $customer): Response
    {
        $this->authorize('view', $customer);

        // Aktivite = audit kayıtları (append-only, salt-okunur)
        $activities = AuditEvent::query()
            ->where('auditable_type', Customer::class)
            ->where('auditable_id', $customer->id)
            ->with('actor:id,name')
            ->latest('id')
            ->limit(30)
            ->get();

        return Inertia::render('customers/show', [
            'customer' => [
                ...$customer->only(['id', 'name', 'trade_name', 'tax_number', 'tax_office', 'email', 'phone', 'city', 'address', 'archived_at']),
                'customer_type' => $customer->customerType?->only(['id', 'label']),
                'contacts' => $customer->contacts,
                'custom_field_values' => $customer->customFieldValues->map(fn (CustomFieldValue $value) => [
                    'id' => $value->id,
                    'definition_id' => $value->definition_id,
                    'typed_value' => $value->typedValue(),
                ]),
            ],
            'activities' => $activities->map(fn ($event) => [
                'id' => $event->id,
                'actor' => ($event->actor !== null ? $event->actor->name : ($event->actor_name ?? 'sistem')),
                'action' => $event->action,
                'created_at' => $event->created_at->format('d.m.Y H:i'),
            ]),
            'fieldDefinitions' => $this->activeFieldDefinitions(),
            'contacts' => $customer->contacts,
        ]);
    }

    public function edit(Customer $customer): Response
    {
        $this->authorize('update', $customer);

        return Inertia::render('customers/edit', [
            'customerData' => [
                'id' => $customer->id,
                'customer_type_id' => $customer->customer_type_id,
                'name' => $customer->name,
                'trade_name' => $customer->trade_name,
                'tax_number' => $customer->tax_number,
                'tax_office' => $customer->tax_office,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'city' => $customer->city,
                'address' => $customer->address,
                'custom_fields' => $customer->customFieldValues
                    ->mapWithKeys(fn ($value) => [$value->definition_id => $value->typedValue()])
                    ->all(),
            ],
            'customerTypes' => CustomerType::query()->where('is_active', true)->orderBy('label')->get(['id', 'key', 'label']),
            'fieldDefinitions' => $this->activeFieldDefinitions(),
        ]);
    }

    public function update(Request $request, Customer $customer, CustomFieldService $fields): RedirectResponse
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'customer_type_id' => ['nullable', Rule::exists('customer_types', 'id')],
            'name' => ['required', 'string', 'max:190'],
            'trade_name' => ['nullable', 'string', 'max:190'],
            'tax_number' => ['nullable', 'string', 'max:20'],
            'tax_office' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'city' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:2000'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $original = $customer->getAttributes();
        $customer->update($validated);

        $fields->syncCustomerValues($customer, $validated['custom_fields'] ?? []);

        app(AuditLogger::class)->logModelChange('updated', $customer, $original);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Müşteri kaydı güncellendi.');
    }

    /**
     * Arşivleme: kayıt ve ilişkili audit geçmişi korunur; kalıcı silme yok.
     */
    public function archive(Customer $customer): RedirectResponse
    {
        $this->authorize('archive', $customer);

        $original = $customer->getAttributes();
        $customer->update(['archived_at' => now()]);

        app(AuditLogger::class)->log('archived', $customer, ['archived_at' => $original['archived_at']], ['archived_at' => ($customer->archived_at !== null ? $customer->archived_at->toISOString() : null)]);

        return redirect()->route('customers.index')
            ->with('success', 'Müşteri arşivlendi; kayıt ve geçmişi korundu.');
    }

    /**
     * @return Collection<int, CustomFieldDefinition>
     */
    private function activeFieldDefinitions()
    {
        return CustomFieldDefinition::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get(['id', 'key', 'label', 'type', 'required', 'options', 'default_value', 'visible_to_roles']);
    }
}
