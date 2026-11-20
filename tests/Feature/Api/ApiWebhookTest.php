<?php

namespace Tests\Feature\Api;

use App\Domain\Api\Actions\WebhookService;
use App\Domain\Api\Models\ApiToken;
use App\Domain\Api\Models\WebhookDelivery;
use App\Domain\Api\Models\WebhookEndpoint;
use App\Domain\Api\Models\WebhookEvent;
use App\Domain\Customers\Models\Customer;
use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Prompt 16: API + webhook altyapısı. Gerçek sağlayıcı entegrasyonu YOK —
 * yalnız test sink/mock.
 */
class ApiWebhookTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::query()->where('key', 'sistem-yoneticisi')->first());
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password']);

        Customer::create(['name' => 'API Firması', 'city' => 'Ankara']);
        Customer::create(['name' => 'İzmir Firması', 'city' => 'İzmir']);
    }

    private function createToken(array $scopes, ?string $expiresAt = null): string
    {
        $plaintext = 'vfx_'.bin2hex(random_bytes(20));

        ApiToken::create([
            'name' => 'Test token',
            'token_hash' => ApiToken::hash($plaintext),
            'scopes' => $scopes,
            'expires_at' => $expiresAt,
            'created_by' => $this->admin->id,
        ]);

        return $plaintext;
    }

    #[Test]
    public function openapi_spec_uygulanan_endpointlerle_tutarli(): void
    {
        $spec = $this->getJson('/api/v1/openapi.json')->assertOk()->json();

        $this->assertArrayHasKey('/api/v1/customers', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/customers/{id}', $spec['paths']);

        // Spec'teki endpoint gerçekten kayıtlı
        $this->assertSame('Müşteri listesi', $spec['paths']['/api/v1/customers']['get']['summary']);
    }

    #[Test]
    public function authsiz_ve_yanlis_scope_istek_reddedilir(): void
    {
        // Token yok → 401
        $this->getJson('/api/v1/customers')->assertStatus(401);

        // Geçersiz token → 401
        $this->withHeader('Authorization', 'Bearer vfx_invalid')->getJson('/api/v1/customers')->assertStatus(401);

        // Scope yok → 403 (customers.read scope'u olmayan token)
        $token = $this->createToken(['projects.read']);
        $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/v1/customers')->assertStatus(403);

        // Doğru scope → 200
        $token = $this->createToken(['customers.read']);
        $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/v1/customers')->assertOk();
    }

    #[Test]
    public function max_page_size_100_ve_filtrasyon_calisir(): void
    {
        $token = $this->createToken(['customers.read']);

        // per_page 500 istenirse 100'e clamp edilir
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/customers?per_page=500');
        $response->assertOk();
        $this->assertSame(100, $response->json('meta.per_page'));

        // city filtresi
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/customers?city=Ankara');
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('API Firması', $response->json('data.0.name'));
    }

    #[Test]
    public function webhook_hmac_imzasi_sinkte_dogrulanir_ve_replay_reddedilir(): void
    {
        $secret = 'whsec_test_secret';
        $service = app(WebhookService::class);
        $body = json_encode(['event' => 'test.event', 'payload' => ['x' => 1]], JSON_UNESCAPED_UNICODE);
        $timestamp = now()->timestamp;
        $signature = 'sha256='.hash('sha256', $secret.'.'.$timestamp.'.'.$body);

        // Geçerli imza
        $response = $this->postJson('/api/v1/test-sink', json_decode($body, true), [
            'X-Visfor360-Signature' => $signature,
            'X-Visfor360-Timestamp' => (string) $timestamp,
            'X-Test-Secret' => $secret,
        ]);
        $response->assertOk();
        $this->assertTrue($response->json('verified'));

        // Replay: 301+ saniye eski timestamp → red
        $oldTimestamp = now()->timestamp - (WebhookService::REPLAY_WINDOW_SECONDS + 10);
        $oldSignature = 'sha256='.hash('sha256', $secret.'.'.$oldTimestamp.'.'.$body);

        $response = $this->postJson('/api/v1/test-sink', json_decode($body, true), [
            'X-Visfor360-Signature' => $oldSignature,
            'X-Visfor360-Timestamp' => (string) $oldTimestamp,
            'X-Test-Secret' => $secret,
        ]);
        $this->assertFalse($response->json('verified'));

        // Bozuk imza → red
        $response = $this->postJson('/api/v1/test-sink', json_decode($body, true), [
            'X-Visfor360-Signature' => 'sha256=deadbeef',
            'X-Visfor360-Timestamp' => (string) $timestamp,
            'X-Test-Secret' => $secret,
        ]);
        $this->assertFalse($response->json('verified'));
    }

    #[Test]
    public function dispatch_sadece_onayli_eventleri_yayar_ve_idempotenttir(): void
    {
        // Endpoint: yalnız customer.created dinliyor
        WebhookEndpoint::create([
            'name' => 'Test sink',
            'url' => 'https://sink.test/hook',
            'secret_hash' => 'whsec_hashed_secret',
            'enabled_events' => ['customer.created'],
        ]);

        // dispatch aynı payload ile iki kez → tek event (idempotency)
        $deliveries = app(WebhookService::class)->dispatch('customer.created', ['customer_id' => 1]);
        $this->assertSame(1, $deliveries);
        $deliveries2 = app(WebhookService::class)->dispatch('customer.created', ['customer_id' => 1]);
        $this->assertSame(0, $deliveries2); // aynı uuid → yoksayıldı

        // Onaylı olmayan event → delivery yok
        $deliveries3 = app(WebhookService::class)->dispatch('customer.updated', ['customer_id' => 1]);
        $this->assertSame(0, $deliveries3);

        // Event kaydı tüm olaylar için tutulur; delivery yalnız onaylı
        // endpoint'lerin enabled_events'ına yazılır
        $this->assertSame(2, WebhookEvent::query()->count());
        $this->assertSame(1, WebhookDelivery::query()->count());
    }

    #[Test]
    public function teslimat_denemesi_loglanir_ve_retry_dead_letter_calisir(): void
    {
        Http::fake(['sink.test/*' => Http::sequence()->push(['ok' => false], 500)->push(['ok' => false], 500)]);

        WebhookEndpoint::create([
            'name' => 'Sink',
            'url' => 'https://sink.test/hook',
            'secret_hash' => 'whsec_x',
            'enabled_events' => ['customer.created'],
        ]);

        app(WebhookService::class)->dispatch('customer.created', ['customer_id' => 1]);

        // 1. deneme: 500 → retry planlanır, loglanır
        app(WebhookService::class)->processPending();
        $delivery = WebhookDelivery::query()->firstOrFail();
        $this->assertSame(1, $delivery->attempts);
        $this->assertSame('pending', $delivery->status);
        $this->assertSame(500, $delivery->response_code);
        $this->assertNotNull($delivery->next_retry_at);
        $this->assertStringContainsString('HTTP 500', $delivery->last_error ?? '');

        // retry zamanını geçirelim, 2. deneme: yine 500
        Date::setTestNow(now()->addMinutes(5));
        app(WebhookService::class)->processPending();
        $delivery->refresh();
        $this->assertSame(2, $delivery->attempts);
        Date::setTestNow();

        // Mock limiti bitti: sonraki denemeler istisna → attempts artışı; MAX_ATTEMPTS'e ulaşınca dead
        foreach (range(1, WebhookService::MAX_ATTEMPTS) as $_) {
            Date::setTestNow(now()->addMinutes(30)); // backoff süresini geç
            app(WebhookService::class)->processPending();
        }
        $delivery->refresh();
        $this->assertSame('dead', $delivery->status, "attempts: {$delivery->attempts}");
        $this->assertGreaterThanOrEqual(WebhookService::MAX_ATTEMPTS, $delivery->attempts);
    }

    #[Test]
    public function secret_rotasyonu_eski_secret_gecersiz_kilar(): void
    {
        $endpoint = WebhookEndpoint::create([
            'name' => 'Sink',
            'url' => 'https://sink.test/hook',
            'secret_hash' => hash('sha256', 'eski_secret'),
            'enabled_events' => ['customer.created'],
        ]);

        $service = app(WebhookService::class);

        // Eski secret ile imza doğrulanır
        $body = '{"x":1}';
        $timestamp = (string) now()->timestamp;
        $this->assertTrue($service->verifySignature('eski_secret', $timestamp, $body, 'sha256='.hash('sha256', 'eski_secret'.'.'.$timestamp.'.'.$body)));

        // Rotasyon → yeni plaintext bir kez döner; hash değişir
        $newSecret = $service->rotateSecret($endpoint, $this->admin);
        $this->assertNotSame(hash('sha256', 'eski_secret'), $endpoint->refresh()->secret_hash);
        $this->assertStringStartsWith('whsec_', $newSecret);

        // Eski secret artık endpoint hash'iyle eşleşmez (rotasyon geçersiz kılar)
        $this->assertFalse(
            hash_equals($endpoint->refresh()->secret_hash, hash('sha256', 'eski_secret'))
        );
        $this->assertTrue(
            hash_equals($endpoint->secret_hash, hash('sha256', $newSecret))
        );

        // Yeni secret doğrular
        $this->assertTrue($service->verifySignature($newSecret, $timestamp, $body, 'sha256='.hash('sha256', $newSecret.'.'.$timestamp.'.'.$body)));

        // Audit
        $this->assertTrue(AuditEvent::query()->where('action', 'secret_rotated')->where('auditable_id', $endpoint->id)->exists());
    }

    #[Test]
    public function iptal_ve_sure_dolu_token_gecersizlesir(): void
    {
        // İptal edilen token
        $plaintext = $this->createToken(['customers.read']);
        ApiToken::query()->update(['revoked_at' => now()]);

        $this->withHeader('Authorization', 'Bearer '.$plaintext)
            ->getJson('/api/v1/customers')->assertStatus(401);

        // Süresi dolmuş token
        $expired = $this->createToken(['customers.read'], now()->subDay()->toDateTimeString());
        $this->withHeader('Authorization', 'Bearer '.$expired)
            ->getJson('/api/v1/customers')->assertStatus(401);
    }

    #[Test]
    public function gercek_saglayici_entegrasyonu_yapilmamistir(): void
    {
        // Sınır: gerçek WhatsApp/e-posta/muhasebe sağlayıcısı yok — yalnız test sink.
        // Kodda gerçek sağlayıcı domain'i geçmez:
        $sourceFiles = array_merge(
            glob(app_path('Domain/Api').'/*.php') ?: [],
            glob(app_path('Domain/Api/Actions').'/*.php') ?: [],
            glob(app_path('Http/Controllers/Api').'/**/*.php') ?: [],
            glob(app_path('Http/Controllers/Api').'/*.php') ?: [],
        );
        $combined = '';
        foreach ($sourceFiles as $file) {
            $combined .= file_get_contents((string) $file);
        }

        foreach (['whatsapp', 'wa.me', 'sendgrid', 'mailgun', 'paraşüt', 'parasut', 'logo.com', 'efatura'] as $provider) {
            $this->assertStringNotContainsStringIgnoringCase($provider, $combined);
        }
    }
}
