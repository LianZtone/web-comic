<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comic_comment_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_comment_id')->constrained('comic_comments')->cascadeOnDelete();
            $table->string('voter_key', 100);
            $table->string('vote', 20);
            $table->timestamps();

            $table->unique(['comic_comment_id', 'voter_key']);
            $table->index(['comic_comment_id', 'vote']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comic_comment_votes');
    }
};
