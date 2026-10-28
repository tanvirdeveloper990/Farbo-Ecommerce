<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->unsignedInteger('serial')->default(1);
            $table->timestamps();
        });
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->json('data');
            $table->timestamps();
        });
        DB::table('banners')->insert([
            ['image' => '/assets/img/banner/1.webp', 'serial' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['image' => '/assets/img/banner/2.webp', 'serial' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('site_settings')->insert(['id' => 1, 'data' => json_encode([
            'company_name' => 'Rupnagar', 'phone_one' => '+8801332510551', 'phone_two' => '+8801988316422',
            'email_one' => 'help.mariabbd@closetsocialbd.com', 'email_two' => 'info@example.com',
            'address' => 'House 12, Road 5, Block A, Mirpur 10, Dhaka 1216',
            'maps' => 'https://www.google.com/maps/search/?api=1&query=Mirpur+10+Dhaka+1216',
            'copyright' => 'Copyright © 2026 Rupnagar. All rights reserved.',
            'header_logo' => '/assets/img/logo/logo.jpg', 'footer_logo' => '/assets/img/logo/logo.jpg',
            'favicon' => '/assets/img/logo/logo.jpg',
            'marquee' => 'Welcome to Rupnagar | Free delivery across Bangladesh on orders over ৳5,000',
            'facebook' => null, 'instagram' => null, 'youtube' => null, 'tiktok' => null,
        ], JSON_UNESCAPED_UNICODE), 'created_at' => now(), 'updated_at' => now()]);
        foreach (['banners.manage' => 'Manage storefront banners', 'settings.manage' => 'Manage company and storefront settings'] as $name => $description) {
            DB::table('permissions')->insertOrIgnore(['name' => $name, 'description' => $description, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
        Schema::dropIfExists('site_settings');
    }
};
