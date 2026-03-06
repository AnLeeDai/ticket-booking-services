<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_movie', function (Blueprint $table) {
            $table->foreignUuid('movie_id')->constrained('movies', 'movie_id')->onDelete('cascade')->comment('ID phim');
            $table->foreignUuid('category_id')->constrained('categories')->onDelete('cascade')->comment('ID thể loại');
            $table->primary(['movie_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_movie');
    }
};
