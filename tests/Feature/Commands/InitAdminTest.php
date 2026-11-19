<?php

namespace Tests\Feature\Commands;

use App\Console\Commands\InitAdminCommand;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * İlk yönetici bootstrap komutunun güvenlik davranışı:
 * parola kod/seed içinde değildir, komut argümanı olarak geçmez.
 */
class InitAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    #[Test]
    public function ortam_degiskeninden_guclu_parolayla_yonetici_olusturur(): void
    {
        putenv('INIT_ADMIN_PASSWORD=Bootstrap12345');

        $this->artisan('app:init-admin', [
            '--name' => 'Yönetici',
            '--email' => 'admin@visfor.example',
            '--from-env' => true,
        ])->assertExitCode(0);

        $user = User::query()->where('email', 'admin@visfor.example')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('sistem-yoneticisi'));
        $this->assertTrue($user->is_active);
        $this->assertTrue(
            Hash::check('Bootstrap12345', $user->password)
        );

        putenv('INIT_ADMIN_PASSWORD=');
    }

    #[Test]
    public function ayni_epostayla_ikinci_komut_basarisiz_olur(): void
    {
        putenv('INIT_ADMIN_PASSWORD=Bootstrap12345');

        $this->artisan('app:init-admin', [
            '--name' => 'Yönetici',
            '--email' => 'admin@visfor.example',
            '--from-env' => true,
        ])->assertExitCode(0);

        $this->artisan('app:init-admin', [
            '--name' => 'İkinci',
            '--email' => 'admin@visfor.example',
            '--from-env' => true,
        ])->assertExitCode(1);

        $this->assertSame(1, User::query()->where('email', 'admin@visfor.example')->count());

        putenv('INIT_ADMIN_PASSWORD=');
    }

    #[Test]
    public function parola_arguman_olarak_kabul_eklmedi(): void
    {
        // Parola option listesinde yok — argümanla parola geçilemez.
        $signature = (new InitAdminCommand)->getDefinition()->getOptions();

        $this->assertArrayNotHasKey('password', $signature);
        $this->assertArrayHasKey('generate', $signature);
        $this->assertArrayHasKey('from-env', $signature);
    }
}
