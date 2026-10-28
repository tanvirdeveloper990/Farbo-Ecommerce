<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_shipments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $t->string('provider', 20);
            $t->string('state', 20)->default('creating');
            $t->string('consignment_id', 100)->nullable();
            $t->string('tracking_code', 100)->nullable();
            $t->string('remote_status', 100)->nullable();
            $t->decimal('cod_amount', 12, 2);
            $t->json('options')->nullable();
            $t->timestamp('synced_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_shipments');
    }
};
