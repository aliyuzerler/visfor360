<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Çözüm ortağı, komisyon ve hakediş (Prompt 14, ADR-0013).
 * SINIRLAR: Komisyon yüzdesi/KDV/matrah/vade uydurulmaz — komisyon hesabı
 * yalnız approved agreement + örnek formül (fixed_amount | percent_of_value,
 * fixture testli) ile çalışır; müşteri örnek hesaplarıyla kalıcılaşır.
 * Partner portal/login YOK — partner iç kayıt; dış erişim ayrı karar.
 * Komisyon hesabı financial_entries ÜRETMEZ (çift kayıt engeli); finans
 * bağlantısı yalnız partner_id kolonuyla manuel eşleme.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 190);
            $table->string('expertise_type', 60)->nullable(); // uzmanlık türü — müşteri kararı
            $table->string('contact_name', 160)->nullable();
            $table->string('contact_email', 190)->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('partner_agreements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('commission_type', 30); // fixed_amount | percent_of_value (ÖRNEK set)
            $table->decimal('commission_rate', 18, 4)->nullable(); // tutar ya da yüzde
            $table->string('currency_code', 3)->default('TRY');
            $table->string('status', 20)->default('draft'); // draft | approved
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('partner_works', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 190);
            $table->string('referral_type', 20)->default('work'); // work | referral
            $table->decimal('value', 18, 4)->nullable(); // iş değeri — komisyon matrahı kaynağı
            $table->string('currency_code', 3)->default('TRY');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('partner_commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_work_id')->constrained('partner_works')->cascadeOnDelete();
            $table->foreignId('agreement_id')->constrained('partner_agreements')->restrictOnDelete();
            $table->decimal('base_amount', 18, 4);
            $table->decimal('rate', 10, 4)->nullable();
            $table->decimal('calculated_amount', 18, 4);
            $table->string('currency_code', 3)->default('TRY');
            $table->string('period', 20)->nullable(); // hesap dönemi (müşteri kararı)
            $table->string('status', 20)->default('calculated'); // calculated | approved | paid | cancelled
            $table->string('formula_version', 40)->nullable();
            $table->jsonb('meta')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('partner_payouts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 18, 4);
            $table->string('currency_code', 3)->default('TRY');
            $table->string('status', 20)->default('draft'); // draft | paid
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('partner_payout_commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payout_id')->constrained('partner_payouts')->cascadeOnDelete();
            $table->foreignId('commission_id')->constrained('partner_commissions')->cascadeOnDelete();
            $table->unique(['payout_id', 'commission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_payout_commissions');
        Schema::dropIfExists('partner_payouts');
        Schema::dropIfExists('partner_commissions');
        Schema::dropIfExists('partner_works');
        Schema::dropIfExists('partner_agreements');
        Schema::dropIfExists('partners');
    }
};
