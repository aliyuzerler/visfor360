<?php

namespace App\Policies\Identity;

use App\Models\User;

/**
 * Kullanıcı yönetimi policy. Kendi kaydını güncelleme hariç,
 * tüm işlemler users.* iznine bağlıdır. Arşivleme sonrası pasif kullanıcı
 * geçmiş kayıtlardaki actor olarak korunur.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $target): bool
    {
        return $user->can('users.view') || $user->id === $target->id;
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return true;
        }

        // Sistem yöneticisi rolünü değiştirmek ayrı yetki gerektirir.
        if ($target->hasRole('sistem-yoneticisi') && ! $user->hasRole('sistem-yoneticisi')) {
            return false;
        }

        return $user->can('users.update');
    }

    public function archive(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false; // kendi hesabını arşivleyemez
        }

        return $user->can('users.archive');
    }

    public function manageSettings(User $user): bool
    {
        return $user->can('users.manage_settings');
    }
}
