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
        Schema::create('cinemas', function (Blueprint $table) {
            $table->uuid('cinema_id')->primary()->comment('ID rạp chiếu');
            $table->string('code')->unique()->comment('Mã rạp (tự sinh)');
            $table->string('name')->comment('Tên rạp');
            $table->string('location')->comment('Địa chỉ rạp');
            $table->enum('active', ['IN_ACTIVE', 'UN_ACTIVE'])
                ->default('IN_ACTIVE')
                ->comment('Trạng thái: IN_ACTIVE=đang hoạt động, UN_ACTIVE=ngừng hoạt động');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cinemas');
    }
};
