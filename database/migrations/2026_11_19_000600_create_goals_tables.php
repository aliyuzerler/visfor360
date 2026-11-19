<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vizyon ve 12 ana hedef OKR altyapısı (Prompt 08, ADR-0009).
 * SINIR: 12 hedefin adı/tanımı/formülü kaynak veride yok — seed yalnızca
 * is_example=true ile etiketlenmiş placeholder set verir; üretim verisi
 * müşteri girdisiyle girilir. Formül onaylı olmadan ilerleme YÜZDESİ üretilmez.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vision_statements', function (Blueprint $table): void {
            $table->id();
            $table->text('content');
            $table->unsignedSmallInteger('horizon_years')->default(5);
            $table->boolean('is_approved')->default(false); // müşteri onayı
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('strategic_goals', function (Blueprint $table): void {
            $table->id();
            $table->string('slot_key', 30)->unique(); // goal-01 ... goal-12 (12 slot sabit)
            $table->string('label', 190)->nullable(); // müşteri kararı — placeholder'da null
            $table->text('description')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('period', 60)->nullable();
            $table->decimal('baseline', 18, 4)->nullable();
            $table->decimal('target', 18, 4)->nullable();
            $table->string('unit', 40)->nullable();
            $table->string('calculation_method', 60)->nullable(); // null = needs-configuration
            $table->jsonb('calculation_config')->nullable(); // formül parametreleri + sürüm
            $table->boolean('is_example')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('goal_task_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('goal_id')->constrained('strategic_goals')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->nullable(); // ağırlık müşteri kararı
            $table->foreignId('linked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['goal_id', 'task_id']);
        });

        Schema::create('goal_default_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('goal_id')->constrained('strategic_goals')->cascadeOnDelete();
            $table->foreignId('service_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['goal_id', 'service_type_id', 'department_id']);
        });

        Schema::create('goal_progress_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('goal_id')->constrained('strategic_goals')->cascadeOnDelete();
            $table->decimal('progress_value', 10, 4)->nullable();
            $table->string('formula_version', 40)->nullable();
            $table->unsignedInteger('source_task_count')->default(0);
            $table->unsignedInteger('source_done_count')->default(0);
            $table->string('status', 30); // calculated | needs_configuration
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 160)->nullable();
            $table->timestamp('calculated_at');
            $table->jsonb('meta')->nullable(); // kaynak görev listesi vb.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_progress_snapshots');
        Schema::dropIfExists('goal_default_mappings');
        Schema::dropIfExists('goal_task_links');
        Schema::dropIfExists('strategic_goals');
        Schema::dropIfExists('vision_statements');
    }
};
