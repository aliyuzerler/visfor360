<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * İlk sistem yöneticisi bootstrap (Prompt 03).
 * Parola kod içine/seed'e yazılmaz: komut etkileşimli sorar, --generate ile
 * rastgele güçlü parola üretip TEK SEFER ekrana basar, ya da
 * INIT_ADMIN_PASSWORD ortam değişkeninden okur.
 */
class InitAdminCommand extends Command
{
    protected $signature = 'app:init-admin
                            {--name= : Yönetici adı}
                            {--email= : Yönetici e-posta adresi}
                            {--generate : Rastgele parola üret (çıktıda bir kez gösterilir)}
                            {--from-env : Parolayı INIT_ADMIN_PASSWORD ortam değişkeninden al}';

    protected $description = 'İlk sistem yöneticisi hesabını güvenli şekilde oluşturur';

    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Yönetici adı');
        $email = $this->option('email') ?? $this->ask('Yönetici e-posta');

        if (User::withTrashed()->where('email', $email)->exists()) {
            $this->error('Bu e-posta adresiyle bir kullanıcı zaten var.');

            return self::FAILURE;
        }

        $password = $this->resolvePassword();

        if ($password === null) {
            return self::FAILURE;
        }

        $adminRole = Role::query()->where('key', 'sistem-yoneticisi')->first();

        if (! $adminRole) {
            $this->error('sistem-yoneticisi rolü bulunamadı; önce `php artisan db:seed` çalıştırın.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);

        $user->roles()->attach($adminRole->id);

        $this->info('Sistem yöneticisi oluşturuldu: '.$email);
        $this->warn('İlk girişten sonra parolayı değiştirin. Parola hiçbir log/dosyaya yazılmaz.');

        return self::SUCCESS;
    }

    private function resolvePassword(): ?string
    {
        if ($this->option('generate')) {
            $password = base64_encode(random_bytes(18)); // 24 karakter güçlü parola
            $this->line('PAROLA (bir kez gösterilir): '.$password);

            return $password;
        }

        if ($this->option('from-env')) {
            $password = (string) getenv('INIT_ADMIN_PASSWORD');

            if ($password === '') {
                $this->error('INIT_ADMIN_PASSWORD ortam değişkeni boş.');

                return null;
            }

            return $password;
        }

        return $this->secret('Parola (en az 12 karakter, harf+sayı)');
    }
}
