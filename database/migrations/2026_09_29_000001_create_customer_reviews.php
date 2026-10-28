<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_reviews', function (Blueprint $t) {
            $t->id();
            $t->string('name', 150);
            $t->string('image')->nullable();
            $t->unsignedTinyInteger('star');
            $t->text('description');
            $t->boolean('status')->default(false)->index();
            $t->timestamps();
        });
        DB::table('permissions')->insertOrIgnore(['name' => 'reviews.manage', 'description' => 'Manage customer reviews', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_reviews');
    }
};
