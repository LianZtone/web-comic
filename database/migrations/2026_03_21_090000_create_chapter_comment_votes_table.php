<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapter_comment_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_comment_id')->constrained('chapter_comments')->cascadeOnDelete();
            $table->string('voter_key', 100);
            $table->string('vote', 20);
            $table->timestamps();

            $table->unique(['chapter_comment_id', 'voter_key']);
            $table->index(['chapter_comment_id', 'vote']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_comment_votes');
    }
};
