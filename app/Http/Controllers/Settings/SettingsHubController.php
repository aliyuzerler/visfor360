<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Ayarlar hub ekranı: kullanıcı rolüne göre erişilebilir alt ekranlara bağlantı.
 * Erişimi olmayan kartlar gösterilmez (UI yardımcı; server policy yine devrede).
 */
class SettingsHubController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $sections = collect([
            ['key' => 'users', 'title' => 'Kullanıcılar', 'description' => 'Kullanıcı hesapları, rol ve departman atamaları', 'route' => 'settings.users.index', 'can' => $user->can('users.view')],
            ['key' => 'roles', 'title' => 'Roller ve İzinler', 'description' => 'Rol tanımları ve izin matrisi', 'route' => 'settings.roles.index', 'can' => $user->can('roles.view')],
            ['key' => 'departments', 'title' => 'Departmanlar', 'description' => 'Departman yapısı ve üyelikler', 'route' => 'settings.departments.index', 'can' => $user->can('departments.view')],
            ['key' => 'audit', 'title' => 'Audit Log', 'description' => 'Kritik değişikliklerin izlenebilir kaydı (salt-okunur)', 'route' => 'settings.audit-logs.index', 'can' => $user->can('audit.view')],
            ['key' => 'custom-fields', 'title' => 'Özel Alanlar', 'description' => 'Müşteri siciline dinamik alan tanımlama (EAV)', 'route' => 'settings.custom-fields.index', 'can' => $user->can('customers.manage_settings')],
        ])->filter(fn (array $section) => $section['can']);

        return Inertia::render('settings/index', [
            'sections' => $sections->values(),
            'stats' => [
                'users' => User::query()->count(),
                'departments' => Department::query()->where('is_active', true)->count(),
            ],
        ]);
    }
}
