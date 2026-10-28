<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_settings', function (Blueprint $t) {
            $t->id();
            $t->string('provider')->unique();
            $t->json('settings');
            $t->text('credentials')->nullable();
            $t->timestamps();
        });
        foreach (['sslcommerz', 'steadfast', 'pathao', 'redx'] as $provider) {
            DB::table('integration_settings')->insert(['provider' => $provider, 'settings' => json_encode($provider === 'sslcommerz' ? ['mode' => 'test', 'status' => false] : new stdClass), 'created_at' => now(), 'updated_at' => now()]);
        }
        Schema::create('faqs', function (Blueprint $t) {
            $t->id();
            $t->string('question', 500);
            $t->text('answer');
            $t->boolean('status')->default(false)->index();
            $t->timestamps();
        });
        foreach (['payments.manage' => 'Manage payment setup', 'couriers.manage' => 'Manage courier setup', 'faqs.manage' => 'Manage frequently asked questions'] as $name => $description) {
            DB::table('permissions')->insertOrIgnore(['name' => $name, 'description' => $description, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('integration_settings');
    }
};
