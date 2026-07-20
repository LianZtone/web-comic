<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comic_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('viewer_key', 100);
            $table->date('viewed_on');
            $table->timestamps();

            $table->unique(['chapter_id', 'viewer_key', 'viewed_on']);
            $table->index(['comic_id', 'viewed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comic_views');
    }
};
