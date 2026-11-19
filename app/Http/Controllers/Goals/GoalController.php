<?php

namespace App\Http\Controllers\Goals;

use App\Domain\Audit\AuditLogger;
use App\Domain\Goals\Actions\GoalProgressService;
use App\Domain\Goals\Models\GoalDefaultMapping;
use App\Domain\Goals\Models\GoalProgressSnapshot;
use App\Domain\Goals\Models\GoalTaskLink;
use App\Domain\Goals\Models\StrategicGoal;
use App\Domain\Goals\Models\VisionStatement;
use App\Domain\Tasks\Models\Task;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GoalController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', StrategicGoal::class);

        $vision = VisionStatement::query()->first();
        $goals = StrategicGoal::query()
            ->with('owner:id,name')
            ->withCount('taskLinks')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('goals/index', [
            'vision' => $vision !== null ? [
                'content' => $vision->content,
                'horizon_years' => $vision->horizon_years,
                'is_approved' => $vision->is_approved,
            ] : null,
            'goals' => $goals->map(fn (StrategicGoal $goal) => [
                'id' => $goal->id,
                'slot_key' => $goal->slot_key,
                'label' => $goal->label,
                'owner' => $goal->owner?->name,
                'period' => $goal->period,
                'unit' => $goal->unit,
                'is_placeholder' => $goal->isPlaceholder(),
                'progress_status' => $goal->hasApprovedCalculation() ? 'calculated' : 'needs_configuration',
                'task_count' => $goal->task_links_count,
                'can_update' => auth()->user()->can('update', $goal),
            ]),
            'can' => [
                'manage' => auth()->user()->can('create', StrategicGoal::class),
            ],
        ]);
    }

    public function show(StrategicGoal $goal): Response
    {
        $this->authorize('view', $goal);

        $latest = $goal->snapshots()->first();

        return Inertia::render('goals/show', [
            'goal' => [
                'id' => $goal->id,
                'slot_key' => $goal->slot_key,
                'label' => $goal->label,
                'description' => $goal->description,
                'owner' => $goal->owner?->name,
                'period' => $goal->period,
                'baseline' => $goal->baseline,
                'target' => $goal->target,
                'unit' => $goal->unit,
                'is_placeholder' => $goal->isPlaceholder(),
                'calculation_status' => $goal->hasApprovedCalculation() ? 'calculated' : 'needs_configuration',
            ],
            'linkedTasks' => $goal->taskLinks()->with('task:id,title,status')->get()->map(fn (GoalTaskLink $link) => [
                'id' => $link->id,
                'task_id' => $link->task_id,
                'title' => $link->task !== null ? $link->task->title : '—',
                'status' => $link->task?->status,
                'weight' => $link->weight,
            ]),
            'availableTasks' => Task::query()->whereNotIn('id', $goal->taskLinks()->pluck('task_id'))->orderBy('title')->limit(50)->get(['id', 'title']),
            'latestSnapshot' => $latest !== null ? [
                'progress_value' => $latest->progress_value,
                'status' => $latest->status,
                'formula_version' => $latest->formula_version,
                'source_task_count' => $latest->source_task_count,
                'source_done_count' => $latest->source_done_count,
                'calculated_at' => $latest->calculated_at?->format('d.m.Y H:i'),
            ] : null,
            'snapshotHistory' => $goal->snapshots()->limit(20)->get()->map(fn (GoalProgressSnapshot $snapshot) => [
                'id' => $snapshot->id,
                'status' => $snapshot->status,
                'progress_value' => $snapshot->progress_value,
                'formula_version' => $snapshot->formula_version,
                'calculated_at' => $snapshot->calculated_at?->format('d.m.Y H:i'),
            ]),
            'can' => [
                'update' => auth()->user()->can('update', $goal),
                'linkTask' => auth()->user()->can('linkTask', $goal),
            ],
        ]);
    }

    public function update(Request $request, StrategicGoal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:5000'],
            'owner_user_id' => ['nullable', Rule::exists('users', 'id')],
            'period' => ['nullable', 'string', 'max:60'],
            'baseline' => ['nullable', 'numeric'],
            'target' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:40'],
        ]);

        $original = $goal->getAttributes();
        $goal->update($validated);

        app(AuditLogger::class)->logModelChange('updated', $goal, $original);

        return back()->with('success', 'Hedef güncellendi.');
    }

    public function linkTask(Request $request, StrategicGoal $goal): RedirectResponse
    {
        $this->authorize('linkTask', $goal);

        $validated = $request->validate([
            'task_id' => ['required', Rule::exists('tasks', 'id')],
        ]);

        GoalTaskLink::firstOrCreate([
            'goal_id' => $goal->id,
            'task_id' => $validated['task_id'],
        ], ['linked_by' => $request->user()->id]);

        app(AuditLogger::class)->log('goal_task_linked', $goal, null, ['task_id' => $validated['task_id']]);

        return back()->with('success', 'Görev hedefe bağlandı.');
    }

    public function unlinkTask(Request $request, StrategicGoal $goal, GoalTaskLink $link): RedirectResponse
    {
        $this->authorize('unlinkTask', $link);

        $link->delete();
        app(AuditLogger::class)->log('goal_task_unlinked', $goal, null, ['task_id' => $link->task_id]);

        return back()->with('success', 'Görev bağlantısı kaldırıldı.');
    }

    /**
     * İlerleme snapshot'ı: formül onaylıysa hesaplar, değilse
     * needs_configuration kaydı yazar (uydurma yüzden koruması).
     */
    public function snapshot(Request $request, StrategicGoal $goal, GoalProgressService $service): RedirectResponse
    {
        $this->authorize('view', $goal);

        $snapshot = $service->snapshotFor($goal, $request->user());

        return back()->with('success', $snapshot->status === 'calculated'
            ? 'İlerleme hesaplandı ve snapshot alındı.'
            : 'İlerleme formülü konfigüre edilmedi; snapshot needs_configuration olarak alındı.');
    }

    /** Görev oluştururken önerilen varsayılan hedef (departman/hizmet mapping). */
    public static function suggestedGoalFor(?int $serviceTypeId, ?int $departmentId): ?StrategicGoal
    {
        $mapping = GoalDefaultMapping::resolveFor($serviceTypeId, $departmentId);

        return $mapping?->goal;
    }
}
