<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Evrak deposu (Prompt 05).
 * - Dosyalar private storage'da (storage/app/private/documents); public erişim yok.
 * - Sürümler korunur: yeni yükleme önceki kaydı supersede eder, kayıt silinmez.
 * - Zorunluluk/gecerlilik uyarı periyodları yönetilebilir (müşteri P0 kararı bekliyor).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label', 160);
            $table->boolean('required')->default(false); // zorunlu evrak mı (müşteri kararı)
            $table->unsignedSmallInteger('validity_warn_days')->default(30); // son X gün içinde uyarı
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_name', 255); // yalnız güvenli metadata
            $table->string('storage_key', 255); // private diskte rastgele ad
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamp('valid_until')->nullable()->index(); // geçerlilik
            $table->timestamp('superseded_at')->nullable()->index(); // yeni sürüm geldi mi
            $table->foreignId('superseded_by')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['customer_id', 'document_type_id', 'superseded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_types');
    }
};
