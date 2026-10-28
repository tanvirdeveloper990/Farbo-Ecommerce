<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('integration_settings')->insertOrIgnore([
            'provider' => 'smtp',
            'settings' => json_encode(['mailer' => 'smtp', 'port' => 587, 'encryption' => 'tls']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('permissions')->insertOrIgnore([
            'name' => 'smtp.manage', 'description' => 'Manage SMTP setup',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('integration_settings')->where('provider', 'smtp')->delete();
    }
};
