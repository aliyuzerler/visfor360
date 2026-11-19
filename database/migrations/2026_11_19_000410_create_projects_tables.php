<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Projeler ve aşama geçmişi (Prompt 06).
 * Açık müşteri kararı (ADR-0007): bir projenin çok departmanlı hizmete
 * bağlanması — şimdilik tek department_id; karar gelirse M:N tablo eklenir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 190);
            $table->text('description')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pipeline_stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['archived_at', 'name']);
        });

        Schema::create('project_stage_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->constrained('pipeline_stages')->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 160)->nullable();
            $table->timestamp('transitioned_at');
            $table->jsonb('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_stage_history');
        Schema::dropIfExists('projects');
    }
};
