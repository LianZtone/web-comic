<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comics', function (Blueprint $table) {
            $table->string('comic_type', 30)->default('Manhwa')->after('status');
            $table->string('source_type', 30)->default('Project')->after('comic_type');
        });
    }

    public function down(): void
    {
        Schema::table('comics', function (Blueprint $table) {
            $table->dropColumn(['comic_type', 'source_type']);
        });
    }
};
