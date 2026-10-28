<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('catalog_attributes')->nullOnDelete();
            $table->decimal('regular_price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('unit', 40)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->longText('description')->nullable();
            $table->boolean('flash_sale')->default(false);
            $table->string('featured_image_one')->nullable();
            $table->string('featured_image_two')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
            $table->index(['category_id', 'status']);
            $table->index(['created_at', 'status']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('color_id')->nullable()->constrained('catalog_attributes')->nullOnDelete();
            $table->foreignId('size_id')->nullable()->constrained('catalog_attributes')->nullOnDelete();
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('price', 12, 2);
            $table->timestamps();
            $table->index(['product_id', 'color_id', 'size_id']);
        });

        DB::table('permissions')->insertOrIgnore([
            'name' => 'products.manage',
            'description' => 'Create, update, and delete products',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
