<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chapter_comments', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('chapter_id')
                ->constrained('chapter_comments')
                ->nullOnDelete();

            $table->index(['chapter_id', 'parent_id', 'is_visible']);
        });
    }

    public function down(): void
    {
        Schema::table('chapter_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
