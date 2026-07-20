<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapter_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('reactor_key', 100);
            $table->timestamps();

            $table->unique(['chapter_id', 'type', 'reactor_key']);
            $table->index(['chapter_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_reactions');
    }
};
