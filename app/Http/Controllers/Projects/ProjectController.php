<?php

namespace App\Http\Controllers\Projects;

use App\Domain\Audit\AuditLogger;
use App\Domain\Customers\Models\Customer;
use App\Domain\Opportunities\Models\Opportunity;
use App\Domain\Projects\Actions\ProjectService;
use App\Domain\Projects\Models\PipelineStage;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ProjectStageHistory;
use App\Domain\Projects\Models\ServiceType;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Project::class);

        // Kanban verisi: stage bazlı kartlar. Query-level filtre + limit;
        // tüm kayıtlar belleğe alınmaz (sayfa başına kart bütçesi).
        $projects = Project::query()
            ->with(['customer:id,name', 'serviceType:id,label,pipeline_id', 'currentStage:id,label,pipeline_id', 'responsible:id,name'])
            ->when($request->input('status') === 'archived', fn ($q) => $q->whereNotNull('archived_at'))
            ->when($request->input('status', 'active') === 'active', fn ($q) => $q->whereNull('archived_at'))
            ->when($request->filled('service'), fn ($q) => $q->where('service_type_id', $request->integer('service')))
            ->when($request->string('search')->toString(), function ($q, string $search): void {
                $q->where('name', 'ilike', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('projects/index', [
            'projects' => $projects,
            'serviceTypes' => ServiceType::query()->with('pipeline:id,label,is_example')->where('is_active', true)->get(['id', 'key', 'label', 'pipeline_id']),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'service' => $request->filled('service') ? $request->integer('service') : null,
                'status' => $request->input('status', 'active'),
            ],
            'can' => [
                'create' => $request->user()->can('projects.create'),
                'update' => $request->user()->can('projects.update'),
                'archive' => $request->user()->can('projects.archive'),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Project::class);

        return Inertia::render('projects/create', [
            'customers' => Customer::query()->whereNull('archived_at')->orderBy('name')->get(['id', 'name']),
            'serviceTypes' => ServiceType::query()->with('pipeline:id,label,is_example')->where('is_active', true)->get(['id', 'key', 'label', 'pipeline_id']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:5000'],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'service_type_id' => ['required', Rule::exists('service_types', 'id')->where('is_active', true)],
            'department_id' => ['nullable', Rule::exists('departments', 'id')],
            'responsible_user_id' => ['nullable', Rule::exists('users', 'id')],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $service = ServiceType::query()->with('pipeline')->findOrFail($validated['service_type_id']);

        $firstStage = $service->pipeline->stages->first(fn ($stage) => $stage->is_active);

        $project = Project::create($validated + ['pipeline_stage_id' => $firstStage?->id]);

        if ($firstStage !== null) {
            ProjectStageHistory::create([
                'project_id' => $project->id,
                'from_stage_id' => null,
                'to_stage_id' => $firstStage->id,
                'actor_id' => $request->user()->id,
                'actor_name' => $request->user()->name,
                'transitioned_at' => now(),
                'meta' => ['initial' => true],
            ]);
        }

        app(AuditLogger::class)->logModelChange('created', $project);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Proje oluşturuldu.');
    }

    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $pipeline = $project->serviceType->pipeline()->with('stages')->firstOrFail();

        return Inertia::render('projects/show', [
            'project' => [
                ...$project->only(['id', 'name', 'description', 'start_date', 'due_date', 'archived_at']),
                'customer' => $project->customer?->only(['id', 'name']),
                'service_type' => $project->serviceType->only(['id', 'label']),
                'department' => $project->department?->only(['id', 'name']),
                'responsible' => $project->responsible?->only(['id', 'name']),
                'current_stage_id' => $project->pipeline_stage_id,
                'stages' => $pipeline->stages->map(fn ($stage) => [
                    'id' => $stage->id,
                    'key' => $stage->key,
                    'label' => $stage->label,
                    'sort_order' => $stage->sort_order,
                    'is_active' => $stage->is_active,
                    'is_terminal' => $stage->is_terminal,
                ]),
                'pipeline_label' => $pipeline->label,
                'pipeline_is_example' => $pipeline->is_example,
                'source_opportunity' => Opportunity::query()
                    ->where('converted_project_id', $project->id)
                    ->first()
                    ?->only(['id', 'title']),
            ],
            'history' => $project->stageHistory()->with(['fromStage:id,label', 'toStage:id,label'])->limit(30)->get()
                ->map(fn ($entry) => [
                    'id' => $entry->id,
                    'from' => $entry->fromStage?->label,
                    'to' => $entry->toStage->label,
                    'actor' => $entry->actor_name ?? 'sistem',
                    'at' => $entry->transitioned_at->format('d.m.Y H:i'),
                ]),
            'can' => [
                'update' => auth()->user()->can('projects.update'),
                'archive' => auth()->user()->can('projects.archive'),
            ],
        ]);
    }

    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        return Inertia::render('projects/edit', [
            'project' => [
                ...$project->only(['id', 'name', 'description', 'customer_id', 'service_type_id', 'department_id', 'responsible_user_id', 'start_date', 'due_date']),
            ],
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'serviceTypes' => ServiceType::query()->where('is_active', true)->get(['id', 'key', 'label']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:5000'],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'service_type_id' => ['required', Rule::exists('service_types', 'id')->where('is_active', true)],
            'department_id' => ['nullable', Rule::exists('departments', 'id')],
            'responsible_user_id' => ['nullable', Rule::exists('users', 'id')],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        // Pipeline uyumu: hizmet değişirse aşama yeni pipeline'ın ilk aktif aşamasına alınır
        $original = $project->getAttributes();
        $serviceChanged = $validated['service_type_id'] !== $project->service_type_id;

        if ($serviceChanged) {
            $service = ServiceType::query()->with('pipeline.stages')->findOrFail($validated['service_type_id']);
            $firstStage = $service->pipeline->stages->first(fn ($stage) => $stage->is_active);
            $validated['pipeline_stage_id'] = $firstStage?->id;
        }

        $project->update($validated);

        app(AuditLogger::class)->logModelChange('updated', $project, $original);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Proje güncellendi.');
    }

    public function archive(Project $project): RedirectResponse
    {
        $this->authorize('archive', $project);

        $original = $project->getAttributes();
        $project->update(['archived_at' => now()]);

        app(AuditLogger::class)->log('archived', $project, ['archived_at' => $original['archived_at']], ['archived_at' => $project->archived_at?->toISOString()]);

        return redirect()->route('projects.index')->with('success', 'Proje arşivlendi.');
    }

    /**
     * Aşama geçişi: tüm kurallar ProjectService'te backend'de zorlanır;
     * drag/drop UI bu endpoint dışına çıkamaz.
     */
    public function transitionStage(Request $request, Project $project, ProjectService $service): RedirectResponse
    {
        $this->authorize('transitionStage', $project);

        $validated = $request->validate([
            'stage_id' => ['required', Rule::exists('pipeline_stages', 'id')],
        ]);

        $stage = PipelineStage::query()->findOrFail($validated['stage_id']);

        $service->transitionStage($project, $stage, $request->user(), [
            'ip' => $request->ip(),
        ]);

        return back()->with('success', 'Proje aşaması güncellendi.');
    }
}
