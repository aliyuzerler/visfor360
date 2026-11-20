<?php

namespace App\Http\Controllers\Opportunities;

use App\Domain\Checkups\Models\CheckupResult;
use App\Domain\Opportunities\Actions\ConvertOpportunityToProject;
use App\Domain\Opportunities\Actions\OpportunityService;
use App\Domain\Opportunities\Models\Opportunity;
use App\Domain\Opportunities\Models\OpportunityHistory;
use App\Domain\Opportunities\Models\RoadmapItem;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Opportunity::class);

        $opportunities = Opportunity::query()
            ->with(['customer:id,name', 'responsible:id,name'])
            ->when(in_array($request->input('status'), Opportunity::STATUS_EXAMPLE_SET, true), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->input('duplicates') === '1', fn ($q) => $q->where('is_duplicate', true))
            ->when($request->string('search')->toString(), function ($q, string $search): void {
                $q->where('title', 'ilike', "%{$search}%");
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $rows = [];
        foreach ($opportunities->getCollection() as $opportunity) {
            $rows[] = self::opportunityRow($opportunity);
        }

        return Inertia::render('opportunities/index', [
            'opportunities' => [
                'data' => $rows,
                'current_page' => $opportunities->currentPage(),
                'last_page' => $opportunities->lastPage(),
                'links' => $opportunities->linkCollection()->all(),
            ],
            'filters' => $request->only(['status', 'search', 'duplicates']),
            'can' => [
                'decide' => $request->user()->can('opportunities.approve'),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private static function opportunityRow(Opportunity $opportunity): array
    {
        return [
            'id' => $opportunity->id,
            'title' => $opportunity->title,
            'status' => $opportunity->status,
            'is_duplicate' => $opportunity->is_duplicate,
            'service_key' => $opportunity->service_key,
            'customer' => $opportunity->customer->only(['id', 'name']),
            'responsible' => $opportunity->responsible?->only(['id', 'name']),
            'created_at' => $opportunity->created_at?->format('d.m.Y'),
        ];
    }

    public function show(Opportunity $opportunity): Response
    {
        $this->authorize('view', $opportunity);

        $roadmapItem = RoadmapItem::query()->where('opportunity_id', $opportunity->id)->first();

        return Inertia::render('opportunities/show', [
            'opportunity' => [
                'id' => $opportunity->id,
                'title' => $opportunity->title,
                'status' => $opportunity->status,
                'service_key' => $opportunity->service_key,
                'detection_reason' => $opportunity->detection_reason,
                'discussion_note' => $opportunity->discussion_note,
                'decision_note' => $opportunity->decision_note,
                'decided_at' => $opportunity->decided_at?->format('d.m.Y H:i'),
                'converted_project_id' => $opportunity->converted_project_id,
                'converted_at' => $opportunity->converted_at?->format('d.m.Y H:i'),
                'decided_by' => $opportunity->decidedBy?->name,
                'is_duplicate' => $opportunity->is_duplicate,
                'duplicate_of_id' => $opportunity->duplicate_of_id,
                'customer' => $opportunity->customer->only(['id', 'name']),
                'responsible' => $opportunity->responsible?->only(['id', 'name']),
                'source_session_id' => $opportunity->source_session_id,
                'status_set' => Opportunity::STATUS_EXAMPLE_SET,
                'status_set_is_example' => true,
            ],
            'history' => $opportunity->history->map(fn (OpportunityHistory $entry) => [
                'id' => $entry->id,
                'field' => $entry->field,
                'from' => $entry->old_value,
                'to' => $entry->new_value,
                'note' => $entry->note,
                'actor' => $entry->actor_name ?? 'sistem',
                'at' => $entry->changed_at->format('d.m.Y H:i'),
            ]),
            'roadmap_status' => $roadmapItem?->status,
            'can' => [
                'decide' => auth()->user()->can('decide', $opportunity),
                'convert' => auth()->user()->can('convert', $opportunity),
            ],
        ]);
    }

    /** Onaylı fırsat → proje (idempotent; hata → rollback, yarım veri yok). */
    public function convert(Request $request, Opportunity $opportunity, ConvertOpportunityToProject $converter): RedirectResponse
    {
        $project = $converter->convert($opportunity, $request->user());

        return redirect()->route('projects.show', $project)->with('success', 'Fırsat projeye dönüştürüldü.');
    }

    /** Check-Up sonucunu yol haritasına + fırsat havuzuna aktar. */
    public function importFromCheckup(Request $request, CheckupResult $result, OpportunityService $service): RedirectResponse
    {
        $created = $service->importFromCheckup($result, $request->user());

        return redirect()->route('opportunities.index')->with('success', count($created).' öneri fırsat havuzuna aktarıldı.');
    }

    /** Karar: mutabakat notu ZORUNLU; ret/ertelemede gerekçe zorunlu. */
    public function decide(Request $request, Opportunity $opportunity, OpportunityService $service): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,deferred'],
            'discussion_note' => ['required', 'string', 'min:10', 'max:5000'],
            'decision_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $service->decide(
            $opportunity,
            $validated['status'],
            $validated['discussion_note'],
            $validated['decision_note'] ?? '',
            $request->user(),
        );

        return back()->with('success', 'Fırsat kararı kaydedildi.');
    }
}
