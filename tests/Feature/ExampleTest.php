<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    #[Test]
    public function kok_uygulama_kabuguna_yonlendirir(): void
    {
        $this->get('/')->assertRedirect('/app/dashboard');
    }
}
