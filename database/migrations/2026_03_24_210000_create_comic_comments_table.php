<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comic_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained()->cascadeOnDelete();
            $table->string('display_name', 80);
            $table->unsignedTinyInteger('score');
            $table->text('body');
            $table->unsignedInteger('likes_count')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['comic_id', 'is_visible']);
            $table->index(['comic_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comic_comments');
    }
};
