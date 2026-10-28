<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->longText('content')->nullable();
            $t->timestamps();
        });
        foreach (['delivery-policy' => 'Delivery Policy', 'return-policy' => 'Return Policy', 'refund-policy' => 'Refund Policy', 'terms-and-conditions' => 'Terms & Conditions', 'privacy-policy' => 'Privacy Policy'] as $slug => $title) {
            DB::table('policies')->insert(['slug' => $slug, 'title' => $title, 'content' => null, 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('permissions')->insertOrIgnore(['name' => 'policies.manage', 'description' => 'Update legal and policy pages', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
