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
        Schema::create('movies', function (Blueprint $table) {
            $table->uuid('movie_id')->primary()->comment('ID phim');

            $table->string('code')->unique()->comment('Mã phim (tự sinh)');
            $table->string('title')->comment('Tiêu đề phim');
            $table->string('name')->comment('Tên phim');
            $table->string('slug')->unique()->comment('Đường dẫn tĩnh');

            $table->text('description')->nullable()->comment('Mô tả phim');
            $table->string('thumb_url')->comment('Ảnh thumbnail');
            $table->string('trailer_url')->comment('URL trailer');
            $table->json('gallery')->nullable()->comment('Danh sách ảnh gallery');

            $table->unsignedInteger('duration')->comment('Thời lượng (phút)');
            $table->string('language')->comment('Ngôn ngữ');
            $table->unsignedTinyInteger('age')->comment('Giới hạn độ tuổi');
            $table->decimal('rating', 2, 1)->nullable()->comment('Điểm đánh giá');

            $table->date('release_date')->comment('Ngày khởi chiếu');
            $table->date('end_date')->nullable()->comment('Ngày kết thúc chiếu');

            $table->enum('status', ['IN_ACTIVE', 'UN_ACTIVE', 'IS_PENDING'])
                ->default('IS_PENDING')
                ->comment('Trạng thái: IN_ACTIVE=đang chiếu, UN_ACTIVE=ngừng chiếu, IS_PENDING=sắp chiếu');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
