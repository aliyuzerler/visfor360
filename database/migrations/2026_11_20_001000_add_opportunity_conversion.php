<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Onaylı fırsat → proje dönüşümü (Prompt 13, ADR-0012).
 * Idempotency: converted_project_id UNIQUE — çift tıklama/retry duplicate
 * proje üretmez. Kaynak geri erişimi: proje üzerinden Opportunity::where(
 * 'converted_project_id', project->id). Aynı fırsattan çoklu iş üretimi
 * ayrı müşteri kararıdır (bu şemada 1:1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table): void {
            $table->foreignId('converted_project_id')->nullable()->unique()->constrained('projects')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table): void {
            $table->dropForeign(['converted_project_id']);
            $table->dropColumn(['converted_project_id', 'converted_at', 'converted_by']);
        });
    }
};
