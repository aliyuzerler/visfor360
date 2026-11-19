<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM çekirdeği (Prompt 04).
 * P0 notu: müşteri tipleri ve zorunlu sicil alanları müşteri girdisi beklediği
 * için çekirdek minimum tutuldu; esnek alanlar Custom Fields (EAV) ile yönetilir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_types', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('label', 120);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 190);
            $table->string('trade_name', 190)->nullable();
            $table->string('tax_number', 20)->nullable()->index();
            $table->string('tax_office', 80)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('city', 80)->nullable()->index();
            $table->text('address')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['name', 'created_at']);
        });

        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('title', 120)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('phone', 40)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer_types');
    }
};
