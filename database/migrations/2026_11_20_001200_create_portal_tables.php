<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Müşteri portalı + firma bazlı izolasyon (Prompt 15, ADR-0014).
 * - portal_users iç staff'tan AYRI tablo: portal kullanıcısı iç modüllere
 *   (finans, fırsat, iç notlar) hiçbir şekilde yetkilendirilemez.
 * - portal_memberships: kullanıcı ↔ müşteri üyeliği; çoklu kullanıcı kararı
 *   bekliyor olsa da tablo M:N hazır (ADR-0014). is_active=false → anında
 *   erişim kesilir.
 * - portal_document_requests: iç kullanıcının istediği belgeler; müşteri
 *   yalnız kendi talebine yükleme yapabilir; iç inceleme/onay müşteri kararı.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('email', 190)->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::create('portal_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('portal_user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['portal_user_id', 'customer_id']);
        });

        Schema::create('portal_document_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained('document_types')->restrictOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->string('status', 20)->default('requested'); // requested | uploaded (iç inceleme kararı: ADR-0014)
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable(); // portal user; FK ayrı tablo
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'document_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_document_requests');
        Schema::dropIfExists('portal_memberships');
        Schema::dropIfExists('portal_users');
    }
};
