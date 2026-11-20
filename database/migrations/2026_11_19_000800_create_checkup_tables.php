<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Check-Up şablonları + deklaratif kural motoru (Prompt 11, ADR-0010).
 * SINIRLAR:
 * - Müşteri soru seti ve cevap-hizmet karar tablosu hâlâ teslim edilmedi →
 *   üretim hizmet öneri kuralı YAZILMAZ; yalnız is_example=true prototip
 *   şablon + fixture kuralları (blokaj docs/OPEN_QUESTIONS.md'de).
 * - eval / dynamic code / user SQL yasaktır: kurallar yalnız deklaratif
 *   JSON condition groups + allowlist operator'lardır.
 * - Tamamlanan oturum exact version snapshot (sorular + kurallar + cevaplar)
 *   saklar; şablon sonradan değişse sonuç değişmez. Deterministik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkup_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('title', 190);
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 20)->default('draft'); // draft | published
            $table->boolean('is_example')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('checkup_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_id')->constrained('checkup_templates')->cascadeOnDelete();
            $table->string('key', 60);
            $table->string('label', 190);
            $table->string('type', 30); // allowlist: text, number, boolean, single_choice, multi_choice
            $table->boolean('required')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->jsonb('config')->nullable(); // options, min, max, unit
            $table->timestamps();
            $table->unique(['template_id', 'key']);
        });

        Schema::create('checkup_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_id')->constrained('checkup_templates')->cascadeOnDelete();
            $table->string('label', 190);
            $table->unsignedInteger('priority')->default(100); // küçük önce; deterministik
            $table->jsonb('condition_groups'); // OR of groups; group = AND of conditions
            $table->string('recommendation_service_key', 60);
            $table->string('recommendation_label', 190);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['template_id', 'priority']);
        });

        Schema::create('checkup_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_id')->constrained('checkup_templates')->restrictOnDelete();
            $table->unsignedInteger('template_version');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('draft'); // draft | completed
            $table->jsonb('answers_snapshot')->nullable();
            $table->jsonb('questions_snapshot')->nullable(); // tamamlama anındaki soru seti
            $table->jsonb('rules_snapshot')->nullable(); // tamamlama anındaki kurallar
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('checkup_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('checkup_sessions')->cascadeOnDelete();
            $table->jsonb('recommendations'); // [{service_key, label, matched_rule_id, matched_rule_label, reasons:[]}]
            $table->jsonb('rule_trace'); // kural bazında matched/failed açıklaması
            $table->string('engine_version', 40);
            $table->string('deterministic_hash', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkup_results');
        Schema::dropIfExists('checkup_sessions');
        Schema::dropIfExists('checkup_rules');
        Schema::dropIfExists('checkup_questions');
        Schema::dropIfExists('checkup_templates');
    }
};
