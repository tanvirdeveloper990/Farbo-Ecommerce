<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number', 40);
            $table->string('subject', 500);
            $table->text('description');
            $table->timestamps();
        });
        DB::table('permissions')->insertOrIgnore(['name' => 'contacts.manage', 'description' => 'View and delete contact messages', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
