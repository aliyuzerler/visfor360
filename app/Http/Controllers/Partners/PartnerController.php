<?php

namespace App\Http\Controllers\Partners;

use App\Domain\Audit\AuditLogger;
use App\Domain\Customers\Models\Customer;
use App\Domain\Partners\Actions\CommissionService;
use App\Domain\Partners\Models\Partner;
use App\Domain\Partners\Models\PartnerAgreement;
use App\Domain\Partners\Models\PartnerWork;
use App\Domain\Projects\Models\ServiceType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PartnerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Partner::class);

        $partners = Partner::query()
            ->when($request->string('search')->toString(), function ($q, string $search): void {
                $q->where('name', 'ilike', "%{$search}%");
            })
            ->when(in_array($request->input('status'), ['active', 'passive'], true), function ($q) use ($request): void {
                $q->where('is_active', $request->input('status') === 'active');
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $rows = [];
        foreach ($partners->getCollection() as $partner) {
            $rows[] = [
                'id' => $partner->id,
                'name' => $partner->name,
                'expertise_type' => $partner->expertise_type,
                'is_active' => $partner->is_active,
                'contact_name' => $partner->contact_name,
            ];
        }

        return Inertia::render('partners/index', [
            'partners' => [
                'data' => $rows,
                'current_page' => $partners->currentPage(),
                'last_page' => $partners->lastPage(),
                'links' => $partners->linkCollection()->all(),
            ],
            'filters' => $request->only(['search', 'status']),
            'can' => [
                'create' => $request->user()->can('create', Partner::class),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Partner::class);

        return Inertia::render('partners/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Partner::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'expertise_type' => ['nullable', 'string', 'max:60'],
            'contact_name' => ['nullable', 'string', 'max:160'],
            'contact_email' => ['nullable', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $partner = Partner::create($validated + ['created_by' => $request->user()->id]);

        app(AuditLogger::class)->logModelChange('created', $partner);

        return redirect()->route('partners.show', $partner)->with('success', 'Çözüm ortağı kaydedildi.');
    }

    public function show(Partner $partner): Response
    {
        $this->authorize('view', $partner);

        return Inertia::render('partners/show', [
            'partner' => [
                ...$partner->only(['id', 'name', 'expertise_type', 'contact_name', 'contact_email', 'contact_phone', 'notes', 'is_active']),
            ],
            'works' => $partner->works()->with(['customer:id,name', 'project:id,name', 'serviceType:id,label'])->get()
                ->map(fn (PartnerWork $work) => [
                    'id' => $work->id,
                    'title' => $work->title,
                    'referral_type' => $work->referral_type,
                    'value' => $work->value,
                    'currency_code' => $work->currency_code,
                    'customer' => $work->customer?->only(['id', 'name']),
                    'project' => $work->project?->only(['id', 'name']),
                    'service' => $work->serviceType?->only(['id', 'label']),
                    'commission_status' => $work->commissions()->first()?->status,
                ]),
            'agreements' => $partner->agreements->map(fn (PartnerAgreement $agreement) => [
                'id' => $agreement->id,
                'commission_type' => $agreement->commission_type,
                'commission_rate' => $agreement->commission_rate,
                'currency_code' => $agreement->currency_code,
                'status' => $agreement->status,
                'valid_from' => $agreement->valid_from?->format('Y-m-d'),
                'valid_until' => $agreement->valid_until?->format('Y-m-d'),
            ]),
            'customers' => Customer::query()->whereNull('archived_at')->orderBy('name')->get(['id', 'name']),
            'serviceTypes' => ServiceType::query()->where('is_active', true)->orderBy('label')->get(['id', 'label', 'key']),
            'can' => [
                'update' => auth()->user()->can('update', $partner),
                'archive' => auth()->user()->can('archive', $partner),
            ],
        ]);
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $this->authorize('update', $partner);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'expertise_type' => ['nullable', 'string', 'max:60'],
            'contact_name' => ['nullable', 'string', 'max:160'],
            'contact_email' => ['nullable', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $original = $partner->getAttributes();
        $partner->update($validated);

        app(AuditLogger::class)->logModelChange('updated', $partner, $original);

        return back()->with('success', 'Ortak güncellendi.');
    }

    /** Ortak iş/yönlendirme: müşteri + hizmet (+ proje) bağlantılı. */
    public function storeWork(Request $request, Partner $partner): RedirectResponse
    {
        $this->authorize('update', $partner);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'customer_id' => ['required', Rule::exists('customers', 'id')],
            'service_type_id' => ['nullable', Rule::exists('service_types', 'id')],
            'project_id' => ['nullable', Rule::exists('projects', 'id')],
            'referral_type' => ['required', Rule::in(['work', 'referral'])],
            'value' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $work = $partner->works()->create($validated + ['created_by' => $request->user()->id]);

        app(AuditLogger::class)->logModelChange('created', $work);

        return back()->with('success', 'Ortak iş kaydı eklendi.');
    }

    /** Anlaşma: draft olarak açılır; onaylanmadan komisyon hesaplanamaz. */
    public function storeAgreement(Request $request, Partner $partner): RedirectResponse
    {
        $this->authorize('update', $partner);

        $validated = $request->validate([
            'commission_type' => ['required', Rule::in(PartnerAgreement::TYPE_ALLOWLIST)],
            'commission_rate' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'size:3'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ]);

        $agreement = $partner->agreements()->create($validated + ['created_by' => $request->user()->id]);

        app(AuditLogger::class)->logModelChange('created', $agreement);

        return back()->with('success', 'Anlaşma taslak olarak oluşturuldu; onaylanmadan hesap yapılamaz.');
    }

    public function approveAgreement(Request $request, Partner $partner, PartnerAgreement $agreement): RedirectResponse
    {
        $this->authorize('update', $partner);

        $original = $agreement->getAttributes();
        $agreement->update(['status' => 'approved', 'approved_by' => $request->user()->id, 'approved_at' => now()]);

        app(AuditLogger::class)->logModelChange('approved', $agreement, $original);

        return back()->with('success', 'Anlaşma onaylandı; komisyon hesabı etkin.');
    }

    public function calculateCommission(Request $request, Partner $partner, PartnerWork $work, CommissionService $service): RedirectResponse
    {
        $this->authorize('update', $partner);

        $service->calculate($work, $request->user());

        return back()->with('success', 'Komisyon hesaplandı.');
    }
}
