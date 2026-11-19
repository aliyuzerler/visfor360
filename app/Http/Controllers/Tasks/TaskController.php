<?php

namespace App\Http\Controllers\Tasks;

use App\Domain\Audit\AuditLogger;
use App\Domain\Customers\Models\Customer;
use App\Domain\Goals\Models\GoalTaskLink;
use App\Domain\Projects\Models\Project;
use App\Domain\Tasks\Actions\TaskService;
use App\Domain\Tasks\Models\Task;
use App\Domain\Tasks\Models\TaskAssignment;
use App\Domain\Tasks\Models\TaskFieldSetting;
use App\Domain\Tasks\Models\TaskHistory;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Goals\GoalController;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        $view = $request->input('view', 'my'); // my | team

        // Query-level filtre + sayfalama; kayıtlar belleğe alinmaz.
        $tasks = Task::query()
            ->with(['assignments.user:id,name', 'customer:id,name', 'project:id,name', 'department:id,name'])
            ->when($view === 'my', fn ($q) => $q->whereHas('assignments', fn ($a) => $a->where('user_id', $request->user()->id)))
            ->when($view === 'team', fn ($q) => $q->whereDoesntHave('assignments', fn ($a) => $a->where('user_id', $request->user()->id)))
            ->when($request->input('status') === 'overdue', fn ($q) => $q
                ->whereNotNull('due_date')
                ->where('due_date', '<', now()->toDateString())
                ->whereNotIn('status', ['done', 'cancelled']))
            ->when(in_array($request->input('status'), ['open', 'in_progress', 'done', 'cancelled'], true), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->string('search')->toString(), function ($q, string $search): void {
                $q->where('title', 'ilike', "%{$search}%");
            })
            ->orderByRaw("CASE status WHEN 'done' THEN 1 WHEN 'cancelled' THEN 2 ELSE 0 END")
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('tasks/index', [
            'tasks' => [
                'data' => $tasks->getCollection()->map(fn (Task $task) => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date?->format('Y-m-d'),
                    'overdue' => $task->isOverdue(),
                    'assignees' => $task->assignments->map(fn (TaskAssignment $assignment) => $assignment->user->name),
                    'customer' => $task->customer?->only(['id', 'name']),
                    'project' => $task->project?->only(['id', 'name']),
                    'department' => $task->department?->only(['id', 'name']),
                ])->all(),
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'links' => $tasks->linkCollection()->all(),
                'total' => $tasks->total(),
            ],
            'view' => $view,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->input('status', ''),
            ],
            'counts' => [
                'my' => Task::query()->whereHas('assignments', fn ($a) => $a->where('user_id', $request->user()->id))->whereNotIn('status', ['done', 'cancelled'])->count(),
                'team' => Task::query()->whereDoesntHave('assignments', fn ($a) => $a->where('user_id', $request->user()->id))->whereNotIn('status', ['done', 'cancelled'])->count(),
                'overdue' => Task::query()->whereNotNull('due_date')->where('due_date', '<', now()->toDateString())->whereNotIn('status', ['done', 'cancelled'])->count(),
            ],
            'can' => [
                'create' => $request->user()->can('tasks.create'),
                'update' => $request->user()->can('tasks.update'),
                'archive' => $request->user()->can('tasks.archive'),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Task::class);

        return Inertia::render('tasks/create', [
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'customers' => Customer::query()->whereNull('archived_at')->orderBy('name')->get(['id', 'name']),
            'projects' => Project::query()->whereNull('archived_at')->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'requirements' => TaskFieldSetting::requirementMap(),
        ]);
    }

    public function store(Request $request, TaskService $service): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $requirements = TaskFieldSetting::requirementMap();

        $rules = [
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', Rule::in(Task::STATUS_ALLOWLIST)],
            'priority' => ['nullable', Rule::in(Task::PRIORITY_ALLOWLIST)],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'project_id' => ['nullable', Rule::exists('projects', 'id')],
            'service_type_id' => ['nullable', Rule::exists('service_types', 'id')],
            'department_id' => ['nullable', Rule::exists('departments', 'id')],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'assignee_id' => ['nullable', Rule::exists('users', 'id')],
        ];

        // Konfigure edilebilir zorunluluk (TaskFieldSettings)
        foreach (['description', 'due_date', 'project_id', 'customer_id', 'department_id'] as $field) {
            if ($requirements[$field] ?? false) {
                $rules[$field][] = 'required';
            }
        }

        $validated = $request->validate($rules);

        $task = Task::create($validated + [
            'status' => $validated['status'] ?? 'open',
            'priority' => $validated['priority'] ?? 'medium',
            'created_by' => $request->user()->id,
        ]);

        if (! empty($validated['assignee_id'])) {
            $service->assign($task, User::findOrFail($validated['assignee_id']), $request->user());
        }

        // Departman/hizmet mapping'e göre varsayılan hedef önerisi (Prompt 08):
        // kullanıcı düzeltme yetkisi müşteri kararı (ADR-0009) — şimdilik
        // oluşturma sonrası hedef detayından bağlantı yönetilir.
        $suggestedGoal = GoalController::suggestedGoalFor($validated['service_type_id'] ?? null, $validated['department_id'] ?? null);
        if ($suggestedGoal !== null) {
            GoalTaskLink::firstOrCreate([
                'goal_id' => $suggestedGoal->id,
                'task_id' => $task->id,
            ], ['linked_by' => $request->user()->id]);
        }

        app(AuditLogger::class)->logModelChange('created', $task);

        return redirect()->route('tasks.show', $task)->with('success', 'Görev oluşturuldu.');
    }

    public function show(Task $task): Response
    {
        $this->authorize('view', $task);

        return Inertia::render('tasks/show', [
            'task' => [
                ...$task->only(['id', 'title', 'description', 'status', 'priority', 'start_date', 'due_date']),
                'overdue' => $task->isOverdue(),
                'assignees' => $task->assignments->map(fn (TaskAssignment $assignment) => [
                    'id' => $assignment->user_id,
                    'name' => $assignment->user->name,
                ]),
                'customer' => $task->customer?->only(['id', 'name']),
                'project' => $task->project?->only(['id', 'name']),
                'department' => $task->department?->only(['id', 'name']),
            ],
            'history' => $task->history->map(fn (TaskHistory $entry) => [
                'id' => $entry->id,
                'field' => $entry->field,
                'from' => $entry->old_value,
                'to' => $entry->new_value,
                'actor' => $entry->actor_name ?? 'sistem',
                'at' => $entry->changed_at->format('d.m.Y H:i'),
            ]),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'can' => [
                'update' => auth()->user()->can('update', $task),
                'assign' => auth()->user()->can('assign', $task),
                'archive' => auth()->user()->can('archive', $task),
            ],
        ]);
    }

    public function edit(Task $task): Response
    {
        $this->authorize('update', $task);

        return Inertia::render('tasks/edit', [
            'task' => [
                ...$task->only(['id', 'title', 'description', 'status', 'priority', 'customer_id', 'project_id', 'department_id', 'start_date', 'due_date']),
            ],
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'requirements' => TaskFieldSetting::requirementMap(),
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['nullable', Rule::in(Task::PRIORITY_ALLOWLIST)],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'project_id' => ['nullable', Rule::exists('projects', 'id')],
            'department_id' => ['nullable', Rule::exists('departments', 'id')],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $original = $task->getAttributes();
        $oldDue = $task->due_date?->toDateString();
        $task->update($validated);

        // Tarih değişimi history'ye yazılır (TaskService)
        if (($validated['due_date'] ?? null) !== $oldDue) {
            app(TaskService::class)->updateDueDate($task->refresh(), $validated['due_date'] ?? null, $request->user(), true, $oldDue);
        }

        app(AuditLogger::class)->logModelChange('updated', $task, $original);

        return redirect()->route('tasks.show', $task)->with('success', 'Görev güncellendi.');
    }

    public function changeStatus(Request $request, Task $task, TaskService $service): RedirectResponse
    {
        $this->authorize('transition', $task);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Task::STATUS_ALLOWLIST)],
        ]);

        $service->changeStatus($task, $validated['status'], $request->user());

        return back()->with('success', 'Görev durumu güncellendi.');
    }

    public function assign(Request $request, Task $task, TaskService $service): RedirectResponse
    {
        $this->authorize('assign', $task);

        $validated = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')],
        ]);

        $service->assign($task, User::findOrFail($validated['user_id']), $request->user());

        return back()->with('success', 'Görev ataması güncellendi.');
    }

    public function archive(Task $task): RedirectResponse
    {
        $this->authorize('archive', $task);

        $original = $task->getAttributes();
        $task->update(['archived_at' => now()]);

        app(AuditLogger::class)->log('archived', $task, ['archived_at' => $original['archived_at']], ['archived_at' => $task->archived_at?->toISOString()]);

        return redirect()->route('tasks.index')->with('success', 'Görev arşivlendi.');
    }
}
