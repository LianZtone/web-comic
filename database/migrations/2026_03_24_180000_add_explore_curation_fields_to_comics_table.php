<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comics', function (Blueprint $table) {
            $table->boolean('is_recommended')->default(false)->after('sort_order');
            $table->unsignedInteger('recommended_order')->default(0)->after('is_recommended');
            $table->boolean('is_admin_pick')->default(false)->after('recommended_order');
            $table->unsignedInteger('admin_pick_order')->default(0)->after('is_admin_pick');
        });
    }

    public function down(): void
    {
        Schema::table('comics', function (Blueprint $table) {
            $table->dropColumn([
                'is_recommended',
                'recommended_order',
                'is_admin_pick',
                'admin_pick_order',
            ]);
        });
    }
};
