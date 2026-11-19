<?php

namespace App\Providers;

use App\Domain\Customers\Models\Customer;
use App\Domain\Customers\Models\CustomFieldDefinition;
use App\Domain\Identity\PermissionRegistry;
use App\Models\AuditEvent;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Policies\Customers\CustomerPolicy;
use App\Policies\Customers\CustomFieldPolicy;
use App\Policies\Identity\AuditEventPolicy;
use App\Policies\Identity\DepartmentPolicy;
use App\Policies\Identity\RolePolicy;
use App\Policies\Identity\UserPolicy;
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
