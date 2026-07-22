<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total_usd', 14, 4);
            $table->decimal('initial_payment_usd', 14, 4)->default(0);
            $table->decimal('financed_usd', 14, 4)->default(0);
            $table->unsignedTinyInteger('installments_count')->default(2);
            $table->unsignedSmallInteger('installment_gap_days')->default(7);
            $table->decimal('late_fee_usd', 14, 4)->default(1);
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('sale_credit_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_credit_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('installment_number');
            $table->date('due_date');
            $table->decimal('amount_usd', 14, 4);
            $table->decimal('paid_usd', 14, 4)->default(0);
            $table->decimal('late_fee_usd', 14, 4)->default(0);
            $table->boolean('late_fee_applied')->default(false);
            $table->timestamp('whatsapp_sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['sale_credit_id', 'installment_number'], 'sale_credit_inst_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_credit_installments');
        Schema::dropIfExists('sale_credits');
    }
};
