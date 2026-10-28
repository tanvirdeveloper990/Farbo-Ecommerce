<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name', 150);
            $t->string('image')->nullable();
            $t->unsignedInteger('serial')->index();
            $t->timestamps();
        });
        Schema::create('subcategories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->string('name', 150);
            $t->unsignedInteger('serial');
            $t->timestamps();
        });
        Schema::create('child_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('subcategory_id')->constrained()->cascadeOnDelete();
            $t->string('name', 150);
            $t->unsignedInteger('serial');
            $t->timestamps();
        });
        DB::table('permissions')->insertOrIgnore(['name' => 'categories.manage', 'description' => 'Manage categories, subcategories and child categories', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('child_categories');
        Schema::dropIfExists('subcategories');
        Schema::dropIfExists('categories');
    }
};
