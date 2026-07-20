<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chapter_comments', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('chapter_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('comic_comments', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('comic_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chapter_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('comic_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
