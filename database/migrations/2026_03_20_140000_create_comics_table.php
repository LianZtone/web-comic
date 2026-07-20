<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->string('tagline')->nullable();
            $table->text('summary');
            $table->string('author');
            $table->string('artist')->nullable();
            $table->string('status')->default('Ongoing');
            $table->string('schedule')->nullable();
            $table->string('year', 10)->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->string('readers')->nullable();
            $table->json('genres')->nullable();
            $table->json('features')->nullable();
            $table->text('cover_url')->nullable();
            $table->text('banner_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comics');
    }
};
