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
        Schema::create('showtimes', function (Blueprint $table) {
            $table->uuid('showtime_id')->primary()->comment('ID suất chiếu');
            $table->foreignUuid('cinema_id')->constrained('cinemas', 'cinema_id')->onDelete('cascade')->comment('ID rạp chiếu');
            $table->foreignUuid('movie_id')->constrained('movies', 'movie_id')->onDelete('cascade')->comment('ID phim');
            $table->dateTime('starts_at')->comment('Thời gian bắt đầu');
            $table->dateTime('ends_at')->comment('Thời gian kết thúc');
            $table->enum('screen_type', ['2D', '3D'])->comment('Loại màn hình');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showtimes');
    }
};
