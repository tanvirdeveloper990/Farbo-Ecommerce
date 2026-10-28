<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('career_applications', function (Blueprint $table) {
        $table->id(); $table->string('name',160); $table->string('email'); $table->string('phone',40);
        $table->text('address'); $table->decimal('years_of_experience',5,1); $table->decimal('expected_salary',12,2);
        $table->text('motivation'); $table->string('attachment_path')->nullable(); $table->string('attachment_name')->nullable();
        $table->unsignedBigInteger('attachment_size')->nullable(); $table->timestamps();
    }); }
    public function down(): void { Schema::dropIfExists('career_applications'); }
};
