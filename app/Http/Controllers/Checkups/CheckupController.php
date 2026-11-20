<?php

namespace App\Http\Controllers\Checkups;

use App\Domain\Audit\AuditLogger;
use App\Domain\Checkups\Actions\CheckupService;
use App\Domain\Checkups\Models\CheckupSession;
use App\Domain\Checkups\Models\CheckupTemplate;
use App\Domain\Customers\Models\Customer;
use App\Domain\Opportunities\Models\Opportunity;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CheckupController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', CheckupSession::class);

        $sessions = CheckupSession::query()
            ->with(['customer:id,name', 'template:id,key,title,version'])
            ->orderByDesc('id')
            ->paginate(20);

        $rows = [];

        foreach ($sessions->getCollection() as $session) {
            $rows[] = self::sessionRow($session);
        }

        return Inertia::render('checkups/index', [
            'sessions' => [
                'data' => $rows,
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'links' => $sessions->linkCollection()->all(),
            ],
            'can' => [
                'create' => auth()->user()->can('create', CheckupSession::class),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private static function sessionRow(CheckupSession $session): array
    {
        $firstResult = $session->results->first();

        return [
            'id' => $session->id,
            'status' => $session->status,
            'template_version' => $session->template_version,
            'completed_at' => $session->completed_at?->format('d.m.Y H:i'),
            'customer' => $session->customer->only(['id', 'name']),
            'template' => $session->template->only(['id', 'title', 'version']),
            'recommendation_count' => $firstResult !== null ? count($firstResult->recommendations ?? []) : null,
        ];
    }

    public function create(): Response
    {
        $this->authorize('create', CheckupSession::class);

        return Inertia::render('checkups/create', [
            'templates' => CheckupTemplate::query()->where('status', 'published')->get(['id', 'key', 'title', 'version', 'is_example']),
            'customers' => Customer::query()->whereNull('archived_at')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CheckupSession::class);

        $validated = $request->validate([
            'template_id' => ['required', Rule::exists('checkup_templates', 'id')->where('status', 'published')],
            'customer_id' => ['required', Rule::exists('customers', 'id')],
        ]);

        $template = CheckupTemplate::query()->findOrFail($validated['template_id']);

        $session = CheckupSession::create([
            'template_id' => $template->id,
            'template_version' => $template->version,
            'customer_id' => $validated['customer_id'],
            'started_by' => $request->user()->id,
        ]);

        app(AuditLogger::class)->logModelChange('created', $session);

        return redirect()->route('checkups.show', $session);
    }

    public function show(CheckupSession $session): Response
    {
        $this->authorize('view', $session);

        $template = $session->template()->with(['questions', 'rules'])->firstOrFail();
        $result = $session->results()->latest('id')->first();

        return Inertia::render('checkups/show', [
            'session' => [
                'id' => $session->id,
                'status' => $session->status,
                'template_version' => $session->template_version,
                'answers' => $session->answers_snapshot ?? [],
                'customer' => $session->customer?->only(['id', 'name']),
                'template' => [
                    'id' => $template->id,
                    'title' => $template->title,
                    'version' => $template->version,
                    'is_example' => $template->is_example,
                    'questions' => $template->questions->map(fn ($question) => [
                        'key' => $question->key,
                        'label' => $question->label,
                        'type' => $question->type,
                        'required' => $question->required,
                        'config' => $question->config,
                    ]),
                ],
            ],
            'result' => $result !== null ? [
                'id' => $result->id,
                'recommendations' => $result->recommendations,
                'rule_trace' => $result->rule_trace,
                'engine_version' => $result->engine_version,
                'deterministic_hash' => $result->deterministic_hash,
            ] : null,
            'can' => [
                'complete' => $session->status === 'draft' && auth()->user()->can('create', CheckupSession::class),
                'importToOpportunity' => auth()->user()->can('create', Opportunity::class),
            ],
        ]);
    }

    /**
     * Tamamlama: cevaplar snapshot'a yazılır, sonuç snapshot kurallarıyla
     * hesaplanır. Yalnız öneri üretilir — proje/task OLUŞTURULMAZ.
     */
    public function complete(Request $request, CheckupSession $session, CheckupService $service): RedirectResponse
    {
        $this->authorize('create', CheckupSession::class);

        $answers = $request->input('answers', []);

        $service->complete($session, is_array($answers) ? $answers : [], $request->user());

        return redirect()->route('checkups.show', $session)->with('success', 'Check-Up tamamlandı; sonuçlar snapshot ile saklandı.');
    }

    /** Şablon yöneticisi (prototip): yalnız is_example şablonlar listelenir. */
    public function templates(): Response
    {
        $this->authorize('manage', CheckupTemplate::class);

        return Inertia::render('settings/checkups/index', [
            'templates' => CheckupTemplate::query()->with(['questions', 'rules'])->get()->map(fn (CheckupTemplate $template) => [
                'id' => $template->id,
                'key' => $template->key,
                'title' => $template->title,
                'version' => $template->version,
                'status' => $template->status,
                'is_example' => $template->is_example,
                'question_count' => $template->questions->count(),
                'rule_count' => $template->rules->count(),
            ]),
            'blocked' => true, // müşteri soru seti/rule matrix bekleniyor (ADR-0010)
        ]);
    }
}
