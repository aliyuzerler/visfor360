<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * REST API + Webhook altyapısı (Prompt 16, ADR-0015).
 * - api_tokens: Bearer token hash (plaintext saklanmaz), scopes, expiry,
 *   revocation. Secret frontend bundle/URL'de gösterilmez.
 * - webhook_endpoints: secret hash + aktiflik + enabled events (müşteri
 *   onayına bağlı). Rotation eski secret'ı geçersiz kılar.
 * - webhook_events + webhook_deliveries: idempotency (event uuid unique),
 *   HMAC sign, attempt log, retry/backoff, dead-letter.
 * - Gerçek WhatsApp/e-posta/muhasebe entegrasyonu KAPSAM DIŞI (ADR-0015).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('token_hash', 64)->unique(); // sha256
            $table->jsonb('scopes'); // ['customers.read', ...]
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('webhook_endpoints', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('url', 500);
            $table->string('secret_hash', 64); // sha256 — plaintext bir kez gösterilir
            $table->jsonb('enabled_events'); // müşteri onayına bağlı olay listesi
            $table->boolean('is_active')->default(true);
            $table->timestamp('rotated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_uuid', 64)->unique(); // idempotency/replay
            $table->string('event_name', 60);
            $table->jsonb('payload');
            $table->timestamp('occurred_at');
        });

        Schema::create('webhook_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('endpoint_id')->constrained('webhook_endpoints')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('webhook_events')->cascadeOnDelete();
            $table->unsignedInteger('attempts')->default(0);
            $table->string('status', 20)->default('pending'); // pending | delivered | failed | dead
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();
            $table->unique(['endpoint_id', 'event_id']); // retry duplicate side-effect yok
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('webhook_endpoints');
        Schema::dropIfExists('api_tokens');
    }
};
