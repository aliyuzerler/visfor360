<?php

namespace App\Domain\Identity;

/**
 * Merkezi izin kayıt defteri (Prompt 03).
 * Modül/aksiyon bazında izin anahtarları; yeni modüller buraya eklenir.
 * Rol matrisi müşteri onayı beklediği için seed yalnızca ÖNERİ'dir
 * (docs/ROLES_AND_PERMISSIONS.md); izin sınırları değiştirilebilir.
 */
final class PermissionRegistry
{
    public const ACTIONS = ['view', 'create', 'update', 'archive', 'approve', 'export', 'download', 'manage_settings'];

    /**
     * @return array<string, array<int, string>> modül => aksiyonlar
     */
    public static function modules(): array
    {
        return [
            'users' => ['view', 'create', 'update', 'archive', 'manage_settings'],
            'roles' => ['view', 'update', 'create', 'archive', 'manage_settings'],
            'departments' => ['view', 'create', 'update', 'archive', 'manage_settings'],
            'audit' => ['view', 'export'],
            'customers' => ['view', 'create', 'update', 'archive', 'export', 'manage_settings'],
            'documents' => ['view', 'create', 'update', 'archive', 'download', 'manage_settings'],
            'projects' => ['view', 'create', 'update', 'archive', 'approve', 'manage_settings'],
            'tasks' => ['view', 'create', 'update', 'archive', 'manage_settings'],
            'goals' => ['view', 'create', 'update', 'archive', 'approve', 'manage_settings'],
            'finance' => ['view', 'create', 'update', 'archive', 'approve', 'export', 'manage_settings'],
            'checkups' => ['view', 'create', 'update', 'archive', 'approve', 'manage_settings'],
            'opportunities' => ['view', 'create', 'update', 'archive', 'approve', 'manage_settings'],
            'partners' => ['view', 'create', 'update', 'archive', 'approve', 'export', 'manage_settings'],
            'portal' => ['view', 'create', 'update', 'archive', 'manage_settings'],
            'api' => ['view', 'create', 'update', 'archive', 'manage_settings'],
        ];
    }

    /**
     * @return list<string> tüm izin anahtarları ("module.action")
     */
    public static function keys(): array
    {
        $keys = [];

        foreach (self::modules() as $module => $actions) {
            foreach ($actions as $action) {
                $keys[] = $module.'.'.$action;
            }
        }

        return $keys;
    }

    public static function label(string $key): string
    {
        [$module, $action] = explode('.', $key);

        $moduleLabels = [
            'users' => 'Kullanıcılar', 'roles' => 'Roller', 'departments' => 'Departmanlar',
            'audit' => 'Audit Log', 'customers' => 'Müşteriler', 'documents' => 'Evraklar',
            'projects' => 'Projeler', 'tasks' => 'Görevler', 'goals' => 'Stratejik Hedefler',
            'finance' => 'Finans', 'checkups' => 'Check-Up', 'opportunities' => 'Fırsat Havuzu',
            'partners' => 'Çözüm Ortakları', 'portal' => 'Müşteri Portalı', 'api' => 'API',
        ];

        $actionLabels = [
            'view' => 'Görüntüleme', 'create' => 'Oluşturma', 'update' => 'Güncelleme',
            'archive' => 'Arşivleme', 'approve' => 'Onaylama', 'export' => 'Dışa Aktarma',
            'download' => 'İndirme', 'manage_settings' => 'Ayar Yönetimi',
        ];

        return ($moduleLabels[$module] ?? $module).' - '.($actionLabels[$action] ?? $action);
    }
}
