<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('regular_price', 12, 2)->nullable()->change();
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable(false)->default(0)->change();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('regular_price', 12, 2)->nullable(false)->default(0)->change();
        });
    }
};
