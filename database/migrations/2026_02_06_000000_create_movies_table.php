<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->uuid('movie_id')->primary()->comment('ID phim');
            $table->uuid('gender_id')->comment('ID the loai (categories)');
            $table->string('code')->comment('Ma phim');
            $table->string('title')->comment('Tieu de phim');
            $table->string('name')->comment('Ten phim');
            $table->string('slug')->index()->comment('Slug phim');
            $table->text('description')->nullable()->comment('Mo ta phim');
            $table->string('thumb_url')->comment('Anh thumbnail');
            $table->string('trail_url')->comment('Link trailer');
            $table->time('duration')->comment('Thoi luong phim');
            $table->string('language')->comment('Ngon ngu');
            $table->unsignedInteger('age')->comment('Do tuoi gioi han');
            $table->decimal('rating', 2, 1)->nullable()->comment('Danh gia');
            $table->date('release_date')->comment('Ngay khoi chieu');
            $table->date('end_date')->nullable()->comment('Ngay ket thuc');
            $table->enum('status', ['IN_ACTIVE', 'UN_ACTIVE', 'IS_PENDING'])->default('IS_PENDING');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
