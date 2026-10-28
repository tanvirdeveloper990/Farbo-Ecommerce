<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_id', 50)->unique();
            $table->string('mode', 10);
            $table->text('credentials');
            $table->decimal('amount', 12, 2);
            $table->string('status', 30)->default('pending');
            $table->string('validation_id')->nullable()->unique();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payment_attempts'); }
};
