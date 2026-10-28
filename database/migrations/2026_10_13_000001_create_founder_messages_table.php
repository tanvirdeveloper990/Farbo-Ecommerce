<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('founder_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('founder_name', 150);
            $table->string('designation', 180)->nullable();
            $table->string('image', 2048);
            $table->longText('content');
            $table->unsignedInteger('serial')->default(1)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('founder_messages'); }
};
