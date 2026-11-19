<?php

namespace App\Http\Controllers\Finance;

use App\Domain\Audit\AuditLogger;
use App\Domain\Customers\Models\Customer;
use App\Domain\Finance\Actions\FinanceCalculationService;
use App\Domain\Finance\Models\CashBalanceSnapshot;
use App\Domain\Finance\Models\FinancialCategory;
use App\Domain\Finance\Models\FinancialEntry;
use App\Domain\Projects\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FinancialEntry::class);

        // Query-level filtre: yön, tahmini/gerçekleşen, durum, tarih aralığı.
        $entries = FinancialEntry::query()
            ->with(['category:id,label,direction', 'customer:id,name', 'project:id,name'])
            ->when(in_array($request->input('direction'), ['income', 'expense'], true), fn ($q) => $q->where('direction', $request->input('direction')))
            ->when(in_array($request->input('type'), ['estimated', 'actual'], true), fn ($q) => $q->where('entry_type', $request->input('type')))
            ->when(in_array($request->input('status'), FinancialEntry::STATUS_ALLOWLIST, true), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('entry_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('entry_date', '<=', $request->input('to')))
            ->orderByDesc('entry_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('finance/index', [
            'entries' => [
                'data' => $entries->getCollection()->map(fn (FinancialEntry $entry) => [
                    'id' => $entry->id,
                    'direction' => $entry->direction,
                    'entry_type' => $entry->entry_type,
                    'status' => $entry->status,
                    'amount' => $entry->amount,
                    'currency_code' => $entry->currency_code,
                    'entry_date' => $entry->entry_date?->format('Y-m-d'),
                    'category' => $entry->category?->label,
                    'customer' => $entry->customer?->name,
                    'project' => $entry->project?->name,
                    'parent_entry_id' => $entry->parent_entry_id,
                ])->all(),
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'links' => $entries->linkCollection()->all(),
            ],
            'filters' => $request->only(['direction', 'type', 'status', 'from', 'to']),
            'can' => [
                'create' => $request->user()->can('finance.create'),
                'export' => $request->user()->can('finance.export'),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', FinancialEntry::class);

        return Inertia::render('finance/create', [
            'categories' => FinancialCategory::query()->where('is_active', true)->orderBy('label')->get(['id', 'label', 'direction']),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', FinancialEntry::class);

        $validated = $request->validate([
            'direction' => ['required', Rule::in(FinancialEntry::DIRECTION_ALLOWLIST)],
            'entry_type' => ['required', Rule::in(FinancialEntry::TYPE_ALLOWLIST)],
            'category_id' => ['nullable', Rule::exists('financial_categories', 'id')],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999.9999'],
            'currency_code' => ['required', 'string', 'size:3'],
            'entry_date' => ['required', 'date'],
            'status' => ['nullable', Rule::in(FinancialEntry::STATUS_ALLOWLIST)],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'project_id' => ['nullable', Rule::exists('projects', 'id')],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $entry = FinancialEntry::create($validated + ['created_by' => $request->user()->id]);

        app(AuditLogger::class)->logModelChange('created', $entry);

        return redirect()->route('finance.index')->with('success', 'Finans kaydı oluşturuldu.');
    }

    public function edit(FinancialEntry $entry): Response
    {
        $this->authorize('update', $entry);

        return Inertia::render('finance/edit', [
            'entry' => [
                ...$entry->only(['id', 'direction', 'entry_type', 'category_id', 'amount', 'currency_code', 'entry_date', 'status', 'customer_id', 'project_id', 'description']),
            ],
            'categories' => FinancialCategory::query()->where('is_active', true)->orderBy('label')->get(['id', 'label', 'direction']),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Düzeltme geçmişi korunur: mevcut kayıt cancelled + yeni kayıt parent_entry_id
     * ile zincirlenir (ADR-0005 karar bekliyorsa bile veri kaybı olmaz).
     */
    public function update(Request $request, FinancialEntry $entry): RedirectResponse
    {
        $this->authorize('update', $entry);

        $validated = $request->validate([
            'direction' => ['required', Rule::in(FinancialEntry::DIRECTION_ALLOWLIST)],
            'entry_type' => ['required', Rule::in(FinancialEntry::TYPE_ALLOWLIST)],
            'category_id' => ['nullable', Rule::exists('financial_categories', 'id')],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999.9999'],
            'currency_code' => ['required', 'string', 'size:3'],
            'entry_date' => ['required', 'date'],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'project_id' => ['nullable', Rule::exists('projects', 'id')],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $original = $entry->getAttributes();

        // Aynı alanlar değiştiyse düzeltme kaydı: eski kayıt iptal, yenisi zincirli
        $materialChange = ($original['amount'] !== $validated['amount'])
            || ($original['direction'] !== $validated['direction'])
            || ($original['entry_date'] !== $validated['entry_date']);

        if ($materialChange && $entry->status === 'approved') {
            $entry->update(['status' => 'cancelled']);
            $entry = FinancialEntry::create($validated + [
                'created_by' => $request->user()->id,
                'parent_entry_id' => $entry->id,
            ]);
        } else {
            $entry->update($validated);
        }

        app(AuditLogger::class)->logModelChange('updated', $entry, $original);

        return redirect()->route('finance.index')->with('success', 'Finans kaydı güncellendi.');
    }

    public function approve(Request $request, FinancialEntry $entry): RedirectResponse
    {
        $this->authorize('approve', $entry);

        $original = $entry->getAttributes();
        $entry->update(['status' => 'approved', 'approved_by' => $request->user()->id, 'approved_at' => now()]);

        app(AuditLogger::class)->logModelChange('approved', $entry, $original);

        return back()->with('success', 'Kayıt onaylandı.');
    }

    public function cancel(Request $request, FinancialEntry $entry): RedirectResponse
    {
        $this->authorize('update', $entry);

        $original = $entry->getAttributes();
        $entry->update(['status' => 'cancelled']);

        app(AuditLogger::class)->logModelChange('cancelled', $entry, $original);

        return back()->with('success', 'Kayıt iptal edildi; geçmişi korundu.');
    }

    /** P&L raporu — nakit akışından AYRI ekran; onaysızsa needs_configuration. */
    public function pnlReport(FinanceCalculationService $calculation): Response
    {
        $this->authorize('viewAny', FinancialEntry::class);

        return Inertia::render('finance/pnl', [
            'summary' => $calculation->pnlSummary(),
        ]);
    }

    /** Nakit akışı + runway raporu — P&L'den AYRI ekran; varsayımlar açıkça gösterilir. */
    public function cashReport(Request $request, FinanceCalculationService $calculation): Response
    {
        $this->authorize('viewAny', FinancialEntry::class);

        $snapshots = CashBalanceSnapshot::query()->orderByDesc('calculated_at')->limit(10)->get();

        return Inertia::render('finance/cash', [
            'snapshots' => $snapshots->map(fn ($snapshot) => [
                'id' => $snapshot->id,
                'as_of_date' => $snapshot->as_of_date?->format('Y-m-d'),
                'cash_balance' => $snapshot->cash_balance,
                'monthly_burn' => $snapshot->monthly_burn,
                'runway_months' => $snapshot->runway_months,
                'currency_code' => $snapshot->currency_code,
                'assumptions' => $snapshot->assumptions,
                'status' => $snapshot->status,
                'formula_version' => $snapshot->formula_version,
                'calculated_at' => $snapshot->calculated_at?->format('d.m.Y H:i'),
            ]),
            'can' => [
                'snapshot' => $request->user()->can('finance.update'),
            ],
        ]);
    }

    public function takeCashSnapshot(Request $request, FinanceCalculationService $calculation): RedirectResponse
    {
        $this->authorize('update', FinancialEntry::class);

        $snapshot = $calculation->cashSnapshot($request->user());

        return back()->with('success', $snapshot->status === 'calculated'
            ? 'Nakit snapshot hesaplandı.'
            : 'Nakit formülü onaysız; snapshot needs_configuration olarak alındı.');
    }
}
