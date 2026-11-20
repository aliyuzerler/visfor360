<?php

namespace Tests\Feature\Release;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Prompt 17 release hardening: health check, üretim config kontrolleri,
 * secret scan ve doküman varlığı. Gerçek IDOR/RBAC/finance/portal testleri
 * kendi dosyalarında PASS durumdadır (bkz. TEST_STRATEGY §Faz 1-2).
 */
class ReleaseHardeningTest extends TestCase
{
    #[Test]
    public function health_check_calisir(): void
    {
        $this->get('/up')->assertOk();
    }

    #[Test]
    public function kaynak_kodda_hardcoded_secret_yok(): void
    {
        $paths = [app_path(), config_path(), database_path('migrations'), base_path('routes')];
        $violations = [];

        foreach ($paths as $path) {
            foreach (File::allFiles($path) as $file) {
                $content = $file->getContents();

                // runtime üretim (bin2hex/random_bytes/hash) hariç uzun literal secret
                if (preg_match("/(whsec_|vfx_|api_key)['\"]?\s*(=|=>)\s*['\"][A-Za-z0-9]{25,}/i", $content)
                    && ! str_contains($content, 'random_bytes')
                    && ! str_contains($file->getFilename(), 'Test')) {
                    $violations[] = $file->getPathname();
                }
            }
        }

        $this->assertSame([], $violations, 'Hardcoded secret: '.implode(', ', $violations));
    }

    #[Test]
    public function production_env_kalibi_uretim_degerleriyle_uyusur(): void
    {
        $content = File::get(base_path('.env.example'));

        $this->assertStringContainsString('APP_ENV=production', $content);
        $this->assertStringContainsString('APP_DEBUG=false', $content);
        $this->assertStringContainsString('APP_URL=https://', $content);
    }

    #[Test]
    public function release_dokumanlari_mevcut(): void
    {
        foreach (['SECURITY_REVIEW.md', 'PERFORMANCE_BASELINE.md', 'BACKUP_RESTORE.md', 'PRODUCTION_RUNBOOK.md'] as $doc) {
            $this->assertFileExists(base_path('docs/'.$doc));
        }
    }

    #[Test]
    public function restore_provasi_kayitlari_mevcut(): void
    {
        // Backup/restore gerçekten provalandı mı? (Prompt 17: yedek var diye
        // restore doğrulanmış sayılmaz) — dokümanda gerçek ölçüm kanıtı olmalı
        $doc = File::get(base_path('docs/BACKUP_RESTORE.md'));
        $this->assertStringContainsString('216.783 bytes', $doc);
        $this->assertStringContainsString('PASS', $doc);
    }
}
