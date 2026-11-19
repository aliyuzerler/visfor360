<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hizmet kataloğu ve pipeline altyapısı (Prompt 06).
 * Aşama adları ve geçiş kuralları müşteri girdisidir; seed yalnızca
 * is_example=true ile etiketlenmiş ÖRNEK şablon verir (SRS notu).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipelines', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label', 160);
            $table->boolean('is_example')->default(false); // müşteri onayı bekleyen örnek şablon
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pipeline_stages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pipeline_id')->constrained()->cascadeOnDelete();
            $table->string('key', 60);
            $table->string('label', 160);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_terminal')->default(false);
            $table->jsonb('required_fields')->nullable(); // ['name','responsible_user_id',...]
            $table->foreignId('required_document_type_id')->nullable()->constrained('document_types')->nullOnDelete();
            $table->timestamps();
            $table->unique(['pipeline_id', 'key']);
        });

        Schema::create('service_types', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label', 160);
            $table->text('description')->nullable();
            $table->foreignId('pipeline_id')->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_types');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('pipelines');
    }
};
