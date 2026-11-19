<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Görev ve ekip iş yönetimi (Prompt 07).
 * - Atama: task_assignments ayrı tablo — tek atama varsayılan (ADR-0008,
 *   müşteri cevabı bekliyor), çoklu atamaya genişletilebilir.
 * - Konfigüre edilebilir zorunluluk: TaskFieldSettings aracılığıyla
 *   (Prompt 03 kanıt modeli ile aynı desen).
 * - Yorum/ek/alt görev: müşteri onayı bekliyor — kapsam dışı (ADR-0008).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open')->index(); // open, in_progress, done, cancelled
            $table->string('priority', 20)->default('medium')->index(); // low, medium, high, urgent
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('task_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(true);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at');
            $table->unique(['task_id', 'user_id']);
        });

        Schema::create('task_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 160)->nullable();
            $table->string('field', 60); // status, assignment, due_date, priority...
            $table->string('old_value', 190)->nullable();
            $table->string('new_value', 190)->nullable();
            $table->timestamp('changed_at');
            $table->jsonb('meta')->nullable();
        });

        // Konfigüre edilebilir zorunluluk (müşteri kararı ile doldurulur)
        Schema::create('task_field_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('field', 60)->unique(); // title, description, due_date, project_id...
            $table->boolean('required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_field_settings');
        Schema::dropIfExists('task_history');
        Schema::dropIfExists('task_assignments');
        Schema::dropIfExists('tasks');
    }
};
