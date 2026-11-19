<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Custom Fields EAV modeli (ADR-0004, Prompt 04).
 * - Definition ve value ayrılır; tipler allowlist ile sınırlıdır.
 * - Değerler typed kolonlarda saklanır (tek string'e gömme yok).
 * - EAV yalnızca müşteri sicilinde kullanılır; çekirdek alanlara yayılmaz.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label', 160);
            $table->string('type', 20); // allowlist: text, textarea, integer, decimal, date, boolean, select, multi_select
            $table->boolean('required')->default(false);
            $table->string('default_value', 500)->nullable();
            $table->jsonb('options')->nullable(); // select/multi_select seçenekleri
            $table->boolean('searchable')->default(false);
            $table->boolean('filterable')->default(false);
            $table->boolean('reportable')->default(false);
            $table->string('visible_to_roles', 500)->nullable(); // null = herkes; boşluklu rol key listesi
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'display_order']);
        });

        Schema::create('custom_field_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('definition_id')->constrained('custom_field_definitions')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 18, 4)->nullable();
            $table->date('value_date')->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->jsonb('value_json')->nullable(); // multi_select
            $table->timestamps();
            $table->unique(['definition_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_field_definitions');
    }
};
