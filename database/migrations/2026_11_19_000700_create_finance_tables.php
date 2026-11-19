<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Finans ve nakit akışı (Prompt 09, ADR-0005).
 * SINIRLAR: Resmi muhasebe değildir — fatura/e-fatura/vergi beyanı yok.
 * Para değerleri decimal(18,4) + ISO currency code (float yasak).
 * Tahsilat/ödeme onay akışı müşteri cevabı bekliyor → yalnız CRUD/rapor
 * iskeleti + durum makinesi (draft/pending/approved/cancelled) + düzeltme
 * zinciri (parent_entry_id ile eski kayıt korunur).
 * Hesap formülleri onaylanmadıkça gerçek total/runway hesaplanmaz.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label', 160);
            $table->string('direction', 10); // income | expense
            $table->boolean('is_example')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('financial_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('direction', 10)->index(); // income | expense
            $table->string('entry_type', 10)->index(); // estimated | actual
            $table->foreignId('category_id')->nullable()->constrained('financial_categories')->nullOnDelete();
            $table->decimal('amount', 18, 4);
            $table->string('currency_code', 3)->default('TRY');
            $table->date('entry_date')->index();
            $table->string('status', 20)->default('draft')->index(); // draft, pending, approved, cancelled
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('partner_id')->nullable(); // Faz 2 çözüm ortağı; FK sonra
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('parent_entry_id')->nullable()->constrained('financial_entries')->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['direction', 'entry_type', 'entry_date']);
        });

        Schema::create('finance_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 60)->unique();
            $table->jsonb('value')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_balance_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->date('as_of_date');
            $table->decimal('cash_balance', 18, 4)->nullable();
            $table->decimal('monthly_burn', 18, 4)->nullable();
            $table->decimal('runway_months', 10, 2)->nullable();
            $table->string('currency_code', 3)->default('TRY');
            $table->jsonb('assumptions')->nullable(); // varsayımlar açıkça saklanır
            $table->string('formula_version', 40)->nullable();
            $table->string('status', 30); // calculated | needs_configuration
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('calculated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_balance_snapshots');
        Schema::dropIfExists('finance_settings');
        Schema::dropIfExists('financial_entries');
        Schema::dropIfExists('financial_categories');
    }
};
