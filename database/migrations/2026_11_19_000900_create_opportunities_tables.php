<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gelişim yol haritası + fırsat havuzu (Prompt 12, ADR-0011).
 * SINIRLAR:
 * - Check-Up tespiti doğrudan proje/göreve dönüşMEZ; önce Fırsat Havuzu'na
 *   düşer, insan incelemesi + müşteri mutabakatı zorunludur.
 * - Status adları müşteri kararı: review_pending/customer_discussion/
 *   approved/rejected/deferred YALNIZ ÖRNEK set (ADR-0011).
 * - Müşteri mutabakatı boş checkbox kabul edilmez: karar için mutabakat
 *   notu zorunlu (iş kuralı müşteriyle netleştirilecek).
 * - Tahmini satış değeri müşteri isterse kullanılır (şimdilik nullable).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('service_key', 60);
            $table->string('title', 190);
            $table->foreignId('source_session_id')->nullable()->constrained('checkup_sessions')->nullOnDelete();
            $table->text('detection_reason')->nullable();
            $table->string('status', 30)->default('review_pending')->index();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('estimated_value', 18, 4)->nullable();
            $table->string('estimated_currency', 3)->nullable();
            $table->date('target_date')->nullable();
            $table->text('discussion_note')->nullable(); // müşteri mutabakat notu/kanıtı
            $table->text('decision_note')->nullable(); // ret/erteleme nedeni
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->boolean('is_duplicate')->default(false);
            $table->foreignId('duplicate_of_id')->nullable()->constrained('opportunities')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['customer_id', 'service_key', 'status']);
        });

        Schema::create('roadmap_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('checkup_sessions')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('service_key', 60);
            $table->string('label', 190); // önerilen hizmet
            $table->text('detection_reason')->nullable(); // tespit gerekçesi (kural izi)
            $table->unsignedInteger('priority')->default(100);
            $table->string('status', 30)->default('pending_review'); // pending_review | converted | dismissed
            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
        });

        // Karşılıklı referans: opportunities.roadmap_item_id burada eklenir
        Schema::table('opportunities', function (Blueprint $table): void {
            $table->foreignId('roadmap_item_id')->nullable()->constrained('roadmap_items')->nullOnDelete();
        });

        Schema::create('opportunity_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->string('field', 40); // status | discussion_note | responsible...
            $table->string('old_value', 190)->nullable();
            $table->string('new_value', 190)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 160)->nullable();
            $table->timestamp('changed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_history');
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('roadmap_items');
    }
};
