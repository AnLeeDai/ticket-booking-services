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
        Schema::create('seats', function (Blueprint $table) {
            $table->uuid('seat_id')->primary()->comment('ID ghế');
            $table->foreignUuid('showtime_id')->constrained('showtimes', 'showtime_id')->onDelete('cascade')->comment('ID suất chiếu');
            $table->string('seat_code')->comment('Mã ghế: A1, A2...');
            $table->enum('seat_type', ['VIP', 'COUPLE', 'NORMAL'])->comment('Loại ghế');
            $table->decimal('price', 15, 0)->comment('Giá ghế');
            $table->enum('active', ['IN_ACTIVE', 'UN_ACTIVE', 'HOLD', 'SOLD'])
                ->default('IN_ACTIVE')
                ->comment('Trạng thái: IN_ACTIVE=trống, UN_ACTIVE=không khả dụng, HOLD=đang giữ, SOLD=đã bán');
            $table->dateTime('hold_until')->nullable()->comment('Giữ ghế đến thời điểm');
            $table->timestamps();

            $table->unique(['showtime_id', 'seat_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
