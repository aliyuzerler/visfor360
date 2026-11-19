<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\DepartmentController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\SettingsHubController;
use App\Http\Controllers\Settings\UserController;
use Illuminate\Support\Facades\Route;

// Sağlık kontrolü: konfigürasyon/stack bilgisi sızdırmayan minimal yanıt.
Route::get('/health', HealthController::class)->name('health');

// Kimlik akışları (guest)
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
 * Uygulama kabuğu ekranları (docs/SCREEN_MAP.md ile eşleşir).
 * Tüm rotalar auth + ilgili izin middleware'ıyla korunur; UI gizleme yeterli değildir.
 */
Route::middleware(['auth'])->prefix('app')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    foreach (PlaceholderController::pageKeys() as $pageKey) {
        Route::get('/'.$pageKey, [PlaceholderController::class, 'show'])
            ->name($pageKey.'.index')
            ->defaults('page', $pageKey);
    }

    // Ayarlar hub'ı ve gerçek yönetim ekranları
    Route::get('/settings', [SettingsHubController::class, 'index'])->name('settings.index');

    Route::get('/settings/users', [UserController::class, 'index'])
        ->middleware('can:users.view')->name('settings.users.index');
    Route::get('/settings/users/create', [UserController::class, 'create'])
        ->middleware('can:users.create')->name('settings.users.create');
    Route::post('/settings/users', [UserController::class, 'store'])
        ->middleware('can:users.create')->name('settings.users.store');
    Route::get('/settings/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('can:update,user')->name('settings.users.edit');
    Route::put('/settings/users/{user}', [UserController::class, 'update'])
        ->middleware('can:update,user')->name('settings.users.update');
    Route::patch('/settings/users/{user}/archive', [UserController::class, 'archive'])
        ->middleware('can:archive,user')->name('settings.users.archive');

    Route::get('/settings/roles', [RoleController::class, 'index'])
        ->middleware('can:roles.view')->name('settings.roles.index');
    Route::post('/settings/roles', [RoleController::class, 'store'])
        ->middleware('can:roles.create')->name('settings.roles.store');
    Route::put('/settings/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
        ->middleware('can:update,role')->name('settings.roles.update-permissions');
    Route::patch('/settings/roles/{role}/archive', [RoleController::class, 'archive'])
        ->middleware('can:archive,role')->name('settings.roles.archive');

    Route::get('/settings/departments', [DepartmentController::class, 'index'])
        ->middleware('can:departments.view')->name('settings.departments.index');
    Route::post('/settings/departments', [DepartmentController::class, 'store'])
        ->middleware('can:departments.create')->name('settings.departments.store');
    Route::put('/settings/departments/{department}', [DepartmentController::class, 'update'])
        ->middleware('can:update,department')->name('settings.departments.update');
    Route::patch('/settings/departments/{department}/archive', [DepartmentController::class, 'archive'])
        ->middleware('can:archive,department')->name('settings.departments.archive');

    Route::get('/settings/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('can:audit.view')->name('settings.audit-logs.index');
});
