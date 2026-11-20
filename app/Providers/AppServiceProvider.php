<?php

namespace App\Providers;

use App\Domain\Checkups\Models\CheckupSession;
use App\Domain\Checkups\Models\CheckupTemplate;
use App\Domain\Customers\Models\Customer;
use App\Domain\Customers\Models\CustomFieldDefinition;
use App\Domain\Documents\Models\Document;
use App\Domain\Finance\Models\FinancialEntry;
use App\Domain\Goals\Models\StrategicGoal;
use App\Domain\Identity\PermissionRegistry;
use App\Domain\Opportunities\Models\Opportunity;
use App\Domain\Partners\Models\Partner;
use App\Domain\Projects\Models\Project;
use App\Domain\Tasks\Models\Task;
use App\Models\AuditEvent;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Policies\Checkups\CheckupPolicy;
use App\Policies\Customers\CustomerPolicy;
use App\Policies\Customers\CustomFieldPolicy;
use App\Policies\Documents\DocumentPolicy;
use App\Policies\Finance\FinancialEntryPolicy;
use App\Policies\Goals\StrategicGoalPolicy;
use App\Policies\Identity\AuditEventPolicy;
use App\Policies\Identity\DepartmentPolicy;
use App\Policies\Identity\RolePolicy;
use App\Policies\Identity\UserPolicy;
use App\Policies\Opportunities\OpportunityPolicy;
use App\Policies\Partners\PartnerPolicy;
use App\Policies\Projects\ProjectPolicy;
use App\Policies\Tasks\TaskPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // API rate limit: 60 istek/dakika, IP bazlı (Prompt 16). Cache
        // tabanlı RateLimiter — Redis zorunluluğu yok (ADR-0015).
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by((string) $request->ip()));

        // Giriş istekleri için brute-force sınırı (5 deneme/dakika, e-posta+IP başına).
        RateLimiter::for('login', function (Request $request) {
            $key = strtolower((string) $request->input('email')).'|'.$request->ip();

            return [Limit::perMinute(5)->by($key)];
        });

        // Alt-namespace policy'ler açıkça kaydedilir.
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(AuditEvent::class, AuditEventPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(CustomFieldDefinition::class, CustomFieldPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(StrategicGoal::class, StrategicGoalPolicy::class);
        Gate::policy(FinancialEntry::class, FinancialEntryPolicy::class);
        Gate::policy(CheckupTemplate::class, CheckupPolicy::class);
        Gate::policy(CheckupSession::class, CheckupPolicy::class);
        Gate::policy(Opportunity::class, OpportunityPolicy::class);
        Gate::policy(Partner::class, PartnerPolicy::class);

        // Her registry izni için server-side Gate tanımı.
        // Yetki yalnızca UI'da gizlenmez; her route ek olarak can: middleware ve
        // Policy ile kontrol edilir (deny by default).
        foreach (PermissionRegistry::keys() as $key) {
            Gate::define($key, function (User $user) use ($key): bool {
                return $user->hasPermission($key);
            });
        }

        // Sistem yöneticisi rolü tüm izinlere sahiptir (super admin bypass).
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->hasRole('sistem-yoneticisi')) {
                return true;
            }

            return null;
        });
    }
}
