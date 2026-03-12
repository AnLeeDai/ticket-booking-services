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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('ticket_id')->primary()->comment('ID vé');
            $table->foreignUuid('showtime_id')->constrained('showtimes', 'showtime_id')->onDelete('cascade')->comment('ID suất chiếu');
            $table->foreignUuid('seat_id')->unique()->constrained('seats', 'seat_id')->onDelete('cascade')->comment('ID ghế (1 ghế chỉ bán 1 lần theo showtime)');
            $table->foreignUuid('user_id')->constrained('users', 'user_id')->onDelete('cascade')->comment('ID người đặt');
            $table->foreignUuid('movie_id')->constrained('movies', 'movie_id')->onDelete('cascade')->comment('ID phim (denormalize để query nhanh)');
            $table->string('code')->unique()->comment('Mã vé (tự sinh)');
            $table->decimal('price', 15, 0)->nullable()->comment('Giá vé');
            $table->enum('status', ['IS_PENDING', 'IN_ACTIVE', 'UN_ACTIVE'])
                ->default('IS_PENDING')
                ->comment('Trạng thái: IS_PENDING=chờ, IN_ACTIVE=đã xác nhận, UN_ACTIVE=đã huỷ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
