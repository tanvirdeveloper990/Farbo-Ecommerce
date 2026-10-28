<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $row = DB::table('site_settings')->where('id', 1)->first();
        if (! $row) {
            return;
        }
        $data = json_decode($row->data, true);
        $prefix = 'Welcome to '.($data['company_name'] ?? 'Rupnagar').' | ';
        if (str_starts_with($data['marquee'] ?? '', $prefix)) {
            $data['marquee'] = substr($data['marquee'], strlen($prefix));
        }
        if (! array_key_exists('shop_image', $data)) {
            $data['shop_image'] = 'https://images.unsplash.com/photo-1555529771-835f59fc5efe?w=1800&h=900&fit=crop&q=80&auto=format';
        }
        DB::table('site_settings')->where('id', 1)->update(['data' => json_encode($data, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Preserve editable company content on rollback.
    }
};
