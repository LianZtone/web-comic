<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('title');
            $table->string('release_label')->nullable();
            $table->text('summary')->nullable();
            $table->json('pages')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['comic_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
