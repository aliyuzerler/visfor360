<?php

namespace App\Http\Controllers;

use App\Domain\Customers\Models\Customer;
use App\Domain\Documents\Models\Document;
use App\Domain\Finance\Models\FinanceSetting;
use App\Domain\Finance\Models\FinancialEntry;
use App\Domain\Goals\Models\StrategicGoal;
use App\Domain\Projects\Models\Project;
use App\Domain\Tasks\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Genel Bakis (Prompt 10): role-aware KPI'lar — yalniz gercek aggregate
 * sorgulari; sahte sayi yok, izni olmayan modulun verisi hic gonderilmez
 * (finans sizmasi engeli: cache/props uzerinden de gecmez).
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $kpi = [];

        if ($user->can('tasks.view')) {
            $kpi['tasks'] = [
                'my_open' => Task::query()
                    ->whereHas('assignments', fn ($q) => $q->where('user_id', $user->id))
                    ->whereNotIn('status', ['done', 'cancelled'])
                    ->count(),
                'my_overdue' => Task::query()
                    ->whereHas('assignments', fn ($q) => $q->where('user_id', $user->id))
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now()->toDateString())
                    ->whereNotIn('status', ['done', 'cancelled'])
                    ->count(),
                'team_overdue' => Task::query()
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now()->toDateString())
                    ->whereNotIn('status', ['done', 'cancelled'])
                    ->count(),
            ];
        }

        if ($user->can('projects.view')) {
            $kpi['projects'] = [
                'active' => Project::query()->whereNull('archived_at')->count(),
            ];
        }

        if ($user->can('customers.view')) {
            $kpi['customers'] = [
                'active' => Customer::query()->whereNull('archived_at')->count(),
            ];
        }

        if ($user->can('documents.view')) {
            $kpi['documents'] = [
                // 30 gun icinde suresi dolacak aktif evraklar (Prompt 05 uyari penceresi)
                'expiring_soon' => Document::query()
                    ->whereNull('superseded_at')
                    ->whereNotNull('valid_until')
                    ->whereBetween('valid_until', [now()->toDateString(), now()->addDays(30)->toDateString()])
                    ->count(),
            ];
        }

        if ($user->can('goals.view')) {
            $kpi['goals'] = [
                'slots' => StrategicGoal::query()->count(),
                'configured' => StrategicGoal::query()->whereNotNull('label')->count(),
                'needs_configuration' => StrategicGoal::query()->whereNull('label')->count(),
            ];
        }

        // Finans: izin YOKSA veri hic uretilmez/props'a girmez.
        if ($user->can('finance.view')) {
            $kpi['finance'] = [
                'pending_approvals' => FinancialEntry::query()->where('status', 'pending')->count(),
                'needs_configuration' => FinanceSetting::get('pnl_formula_approved') === null ? 1 : 0,
            ];
        }

        return Inertia::render('dashboard', [
            'kpi' => $kpi,
            'can' => [
                'tasks' => $user->can('tasks.view'),
                'projects' => $user->can('projects.view'),
                'customers' => $user->can('customers.view'),
                'documents' => $user->can('documents.view'),
                'goals' => $user->can('goals.view'),
                'finance' => $user->can('finance.view'),
                'createTask' => $user->can('tasks.create'),
                'createCustomer' => $user->can('customers.create'),
            ],
        ]);
    }
}
