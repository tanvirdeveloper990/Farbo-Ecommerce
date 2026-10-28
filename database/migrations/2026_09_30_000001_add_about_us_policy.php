<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('policies')->insertOrIgnore([
            'slug' => 'about-us',
            'title' => 'About Us',
            'content' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('policies')->where('slug', 'about-us')->delete();
    }
};
