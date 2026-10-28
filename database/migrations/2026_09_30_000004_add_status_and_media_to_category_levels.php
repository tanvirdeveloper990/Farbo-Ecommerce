<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::table('child_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
        });

        foreach (['subcategories', 'child_categories'] as $table) {
            $used = [];
            DB::table($table)->orderBy('id')->each(function ($record) use (&$used, $table) {
                $base = Str::slug($record->name) ?: 'category';
                $slug = $base;
                $suffix = 2;
                while (isset($used[$slug])) {
                    $slug = $base.'-'.$suffix++;
                }
                $used[$slug] = true;
                DB::table($table)->where('id', $record->id)->update(['slug' => $slug]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('child_categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'image', 'is_active']);
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'image', 'is_active']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
