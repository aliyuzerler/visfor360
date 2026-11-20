<?php

use App\Domain\Customers\Models\CustomFieldDefinition;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Checkups\CheckupController;
use App\Http\Controllers\Customers\ContactController;
use App\Http\Controllers\Customers\CustomerController;
use App\Http\Controllers\Customers\CustomFieldController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Documents\DocumentController;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Goals\GoalController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Opportunities\OpportunityController;
use App\Http\Controllers\Partners\PartnerController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Projects\PipelineController;
use App\Http\Controllers\Projects\ProjectController;
use App\Http\Controllers\Projects\ServiceTypeController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\DepartmentController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\SettingsHubController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\Tasks\TaskController;
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
// Müşteri Portalı (Prompt 15) — ayrı guard + sade layout (ADR-0014)
Route::middleware(['web'])->prefix('portal')->name('portal.')->group(function (): void {
    Route::middleware('portal.guest')->group(function (): void {
        Route::get('/login', [PortalAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [PortalAuthController::class, 'login'])->name('login.submit');
    });
    Route::middleware('portal.access')->group(function (): void {
        Route::get('/', [PortalDashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/documents/upload', [PortalDashboardController::class, 'uploadDocument'])->name('documents.upload');
        Route::post('/logout', [PortalAuthController::class, 'logout'])->name('logout');
    });
});

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

    // Proje + pipeline (Prompt 06)
    Route::get('/projects', [ProjectController::class, 'index'])
        ->middleware('can:projects.view')->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])
        ->middleware('can:projects.create')->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])
        ->middleware('can:projects.create')->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])
        ->middleware('can:view,project')->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])
        ->middleware('can:update,project')->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])
        ->middleware('can:update,project')->name('projects.update');
    Route::patch('/projects/{project}/archive', [ProjectController::class, 'archive'])
        ->middleware('can:archive,project')->name('projects.archive');
    Route::patch('/projects/{project}/stage', [ProjectController::class, 'transitionStage'])
        ->middleware('can:transitionStage,project')->name('projects.stage');

    // Görevler (Prompt 07)
    Route::get('/tasks', [TaskController::class, 'index'])
        ->name('tasks.index');
    Route::get('/tasks/create', [TaskController::class, 'create'])
        ->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])
        ->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])
        ->name('tasks.show');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])
        ->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])
        ->name('tasks.update');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'changeStatus'])
        ->name('tasks.status');
    Route::patch('/tasks/{task}/assign', [TaskController::class, 'assign'])
        ->name('tasks.assign');
    Route::patch('/tasks/{task}/archive', [TaskController::class, 'archive'])
        ->name('tasks.archive');

    // Check-Up (Prompt 11) — müşteri soru seti/rule matrix bekleniyor (ADR-0010)
    Route::get('/checkups', [CheckupController::class, 'index'])->name('checkups.index');
    Route::get('/checkups/create', [CheckupController::class, 'create'])
        ->middleware('can:create,App\\Domain\\Checkups\\Models\\CheckupSession')->name('checkups.create');
    Route::post('/checkups', [CheckupController::class, 'store'])
        ->middleware('can:create,App\\Domain\\Checkups\\Models\\CheckupSession')->name('checkups.store');
    Route::get('/checkups/{session}', [CheckupController::class, 'show'])
        ->middleware('can:view,session')->name('checkups.show');
    Route::patch('/checkups/{session}/complete', [CheckupController::class, 'complete'])
        ->middleware('can:create,App\\Domain\\Checkups\\Models\\CheckupSession')->name('checkups.complete');
    Route::get('/settings/checkups', [CheckupController::class, 'templates'])
        ->middleware('can:manage,App\\Domain\\Checkups\\Models\\CheckupTemplate')->name('settings.checkups.index');

    // Fırsat Havuzu (Prompt 12) — statü seti ÖRNEK (ADR-0011)
    Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/{opportunity}', [OpportunityController::class, 'show'])
        ->middleware('can:view,opportunity')->name('opportunities.show');
    Route::patch('/opportunities/{opportunity}/decide', [OpportunityController::class, 'decide'])
        ->middleware('can:decide,opportunity')->name('opportunities.decide');
    Route::post('/checkups/{result}/import', [OpportunityController::class, 'importFromCheckup'])
        ->middleware('can:create,App\\Domain\\Opportunities\\Models\\Opportunity')->name('checkups.import');
    Route::post('/opportunities/{opportunity}/convert', [OpportunityController::class, 'convert'])
        ->middleware('can:convert,opportunity')->name('opportunities.convert');

    // Çözüm ortakları (Prompt 14) — partner portal/login YOK (ADR-0013)
    Route::get('/partners', [PartnerController::class, 'index'])->name('partners.index');
    Route::get('/partners/create', [PartnerController::class, 'create'])
        ->middleware('can:create,App\\Domain\\Partners\\Models\\Partner')->name('partners.create');
    Route::post('/partners', [PartnerController::class, 'store'])
        ->middleware('can:create,App\\Domain\\Partners\\Models\\Partner')->name('partners.store');
    Route::get('/partners/{partner}', [PartnerController::class, 'show'])
        ->middleware('can:view,partner')->name('partners.show');
    Route::put('/partners/{partner}', [PartnerController::class, 'update'])
        ->middleware('can:update,partner')->name('partners.update');
    Route::post('/partners/{partner}/works', [PartnerController::class, 'storeWork'])
        ->middleware('can:update,partner')->name('partners.works.store');
    Route::post('/partners/{partner}/agreements', [PartnerController::class, 'storeAgreement'])
        ->middleware('can:update,partner')->name('partners.agreements.store');
    Route::patch('/partners/{partner}/agreements/{agreement}/approve', [PartnerController::class, 'approveAgreement'])
        ->middleware('can:update,partner')->name('partners.agreements.approve');
    Route::post('/partners/{partner}/works/{work}/commission', [PartnerController::class, 'calculateCommission'])
        ->middleware('can:update,partner')->name('partners.commission.calculate');

    // Finans (Prompt 09) — resmi muhasebe değildir (SRS notu)
    Route::get('/finance', [FinanceController::class, 'index'])
        ->name('finance.index');
    Route::get('/finance/create', [FinanceController::class, 'create'])
        ->middleware('can:finance.create')->name('finance.create');
    Route::post('/finance', [FinanceController::class, 'store'])
        ->middleware('can:finance.create')->name('finance.store');
    Route::get('/finance/reports/pnl', [FinanceController::class, 'pnlReport'])
        ->name('finance.pnl');
    Route::get('/finance/reports/cash', [FinanceController::class, 'cashReport'])
        ->name('finance.cash');
    Route::patch('/finance/reports/cash/snapshot', [FinanceController::class, 'takeCashSnapshot'])
        ->name('finance.cash.snapshot');
    Route::get('/finance/{entry}/edit', [FinanceController::class, 'edit'])
        ->middleware('can:update,entry')->name('finance.edit');
    Route::put('/finance/{entry}', [FinanceController::class, 'update'])
        ->middleware('can:update,entry')->name('finance.update');
    Route::patch('/finance/{entry}/approve', [FinanceController::class, 'approve'])
        ->middleware('can:approve,entry')->name('finance.approve');
    Route::patch('/finance/{entry}/cancel', [FinanceController::class, 'cancel'])
        ->middleware('can:update,entry')->name('finance.cancel');

    // Vizyon + stratejik hedefler (Prompt 08)
    Route::get('/goals', [GoalController::class, 'index'])
        ->name('goals.index');
    Route::get('/goals/{goal}', [GoalController::class, 'show'])
        ->middleware('can:view,goal')->name('goals.show');
    Route::patch('/goals/{goal}/progress', [GoalController::class, 'snapshot'])
        ->middleware('can:view,goal')->name('goals.snapshot');
    Route::post('/goals/{goal}/tasks', [GoalController::class, 'linkTask'])
        ->middleware('can:linkTask,goal')->name('goals.tasks.link');
    Route::patch('/goals/{goal}', [GoalController::class, 'update'])
        ->middleware('can:update,goal')->name('goals.update');

    Route::get('/settings/services', [ServiceTypeController::class, 'index'])
        ->middleware('can:services.view')->name('settings.services.index');
    Route::post('/settings/services', [ServiceTypeController::class, 'store'])
        ->middleware('can:services.manage')->name('settings.services.store');
    Route::put('/settings/services/{serviceType}', [ServiceTypeController::class, 'update'])
        ->middleware('can:services.manage')->name('settings.services.update');
    Route::post('/settings/pipelines', [PipelineController::class, 'store'])
        ->middleware('can:services.manage')->name('settings.pipelines.store');
    Route::post('/settings/pipelines/{pipeline}/stages', [PipelineController::class, 'storeStage'])
        ->middleware('can:services.manage')->name('settings.pipelines.stages.store');
    Route::put('/settings/pipelines/stages/{stage}', [PipelineController::class, 'updateStage'])
        ->middleware('can:services.manage')->name('settings.pipelines.stages.update');

    // Evrak deposu (Prompt 05)
    Route::post('/customers/{customer}/documents', [DocumentController::class, 'store'])
        ->middleware('can:documents.create')->name('customers.documents.store');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])
        ->middleware('can:download,document')->name('documents.download');
    Route::patch('/documents/{document}/archive', [DocumentController::class, 'archive'])
        ->middleware('can:archive,document')->name('documents.archive');

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
