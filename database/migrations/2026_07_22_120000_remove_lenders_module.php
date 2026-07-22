<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lender_loan_payments')) {
            Schema::drop('lender_loan_payments');
        }

        if (Schema::hasTable('lender_loans')) {
            Schema::drop('lender_loans');
        }

        if (Schema::hasTable('lenders')) {
            Schema::drop('lenders');
        }

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'lenders.manage')->delete();
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'lenders_module_enabled')->delete();
        }
    }

    public function down(): void
    {
        Schema::create('lenders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('document')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('lender_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lender_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('amount_ves', 18, 2);
            $table->decimal('paid_ves', 18, 2)->default(0);
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lender_loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lender_loan_id')->constrained()->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount_ves', 18, 2);
            $table->string('payment_method')->default('transferencia');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->insertOrIgnore([
                'name' => 'lenders.manage',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->insertOrIgnore([
                'key' => 'lenders_module_enabled',
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
