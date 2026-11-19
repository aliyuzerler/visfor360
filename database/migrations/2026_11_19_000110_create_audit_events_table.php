<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Audit altyapısı (Prompt 03): append-only audit_events tablosu.
 * Append-only, hem model hem PostgreSQL trigger ile çift katmanda zorlanır:
 * UPDATE/DELETE denemeleri veritabanı seviyesinde exception üretir.
 * Trigger yalnızca pgsql'de kurulur; sqlite/autres motorlarda model katmanı yeterlidir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 160)->nullable();
            $table->string('action', 80)->index();
            $table->string('auditable_type', 120)->nullable()->index();
            $table->unsignedBigInteger('auditable_id')->nullable()->index();
            $table->jsonb('before')->nullable();
            $table->jsonb('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->uuid('correlation_id')->nullable()->index();
            $table->timestamp('created_at')->index();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                CREATE OR REPLACE FUNCTION forbid_audit_event_mutation()
                RETURNS trigger AS $$
                BEGIN
                    RAISE EXCEPTION 'audit_events tablosu append-onlydir: % yasak', TG_OP;
                END;
                $$ LANGUAGE plpgsql;
            SQL);

            DB::statement(<<<'SQL'
                CREATE TRIGGER audit_events_append_only
                BEFORE UPDATE OR DELETE ON audit_events
                FOR EACH ROW EXECUTE FUNCTION forbid_audit_event_mutation();
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS audit_events_append_only ON audit_events');
            DB::statement('DROP FUNCTION IF EXISTS forbid_audit_event_mutation()');
        }
        Schema::dropIfExists('audit_events');
    }
};
