<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthTest extends TestCase
{
    #[Test]
    public function health_endpoints_up_durumunu_doner(): void
    {
        $this->get('/health')
            ->assertStatus(200)
            ->assertJson(['status' => 'ok', 'db' => 'up']);
    }

    #[Test]
    public function health_konfigurasyon_sizdirmaz(): void
    {
        $content = $this->get('/health')->getContent();

        // Sürümler, yol, ortam adı veya stack bilgisi sızmamalı.
        collect(['pgsql', 'PostgreSQL', 'Laravel', 'APP_KEY', 'base_path', 'PHP '])
            ->each(fn (string $secret) => $this->assertStringNotContainsStringIgnoringCase($secret, $content));
    }
}
