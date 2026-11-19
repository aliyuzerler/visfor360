<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Varsayılan seed: yalnızca rol/izin yapısı.
 * Sabit parolalı kullanıcı SEED EDİLMEZ (AGENTS.md kuralı); ilk yönetici
 * `php artisan app:init-admin` komutuyla oluşturulur.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);
    }
}
