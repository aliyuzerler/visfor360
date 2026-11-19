<?php

use App\Domain\Customers\Models\CustomFieldDefinition;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Customers\ContactController;
use App\Http\Controllers\Customers\CustomerController;
use App\Http\Controllers\Customers\CustomFieldController;
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

    // CRM (Prompt 04)
    Route::get('/customers', [CustomerController::class, 'index'])
        ->middleware('can:customers.view')->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])
        ->middleware('can:customers.create')->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])
        ->middleware('can:customers.create')->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])
        ->middleware('can:view,customer')->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
        ->middleware('can:update,customer')->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])
        ->middleware('can:update,customer')->name('customers.update');
    Route::patch('/customers/{customer}/archive', [CustomerController::class, 'archive'])
        ->middleware('can:archive,customer')->name('customers.archive');

    Route::post('/customers/{customer}/contacts', [ContactController::class, 'store'])
        ->middleware('can:update,customer')->name('customers.contacts.store');
    Route::put('/contacts/{contact}', [ContactController::class, 'update'])
        ->middleware('can:update,contact.customer')->name('customers.contacts.update');
    Route::patch('/contacts/{contact}/archive', [ContactController::class, 'archive'])
        ->middleware('can:update,contact.customer')->name('customers.contacts.archive');

    Route::get('/settings/custom-fields', [CustomFieldController::class, 'index'])
        ->middleware('can:customers.manage_settings')->name('settings.custom-fields.index');
    Route::post('/settings/custom-fields', [CustomFieldController::class, 'store'])
        ->middleware('can:create,'.CustomFieldDefinition::class)
        ->name('settings.custom-fields.store');
    Route::put('/settings/custom-fields/{definition}', [CustomFieldController::class, 'update'])
        ->middleware('can:update,definition')->name('settings.custom-fields.update');
    Route::patch('/settings/custom-fields/{definition}/deactivate', [CustomFieldController::class, 'deactivate'])
        ->middleware('can:archive,definition')->name('settings.custom-fields.deactivate');

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
