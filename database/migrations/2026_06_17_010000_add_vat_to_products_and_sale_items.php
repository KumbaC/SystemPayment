<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_vat')->default(true)->after('active');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('tax_usd', 14, 4)->default(0)->after('subtotal_usd');
            $table->decimal('tax_ves', 18, 2)->default(0)->after('subtotal_ves');
            $table->decimal('total_ves', 18, 2)->default(0)->after('tax_ves');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['tax_usd', 'tax_ves', 'total_ves']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('has_vat');
        });
    }
};
