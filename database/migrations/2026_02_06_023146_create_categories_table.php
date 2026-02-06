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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('ID danh mục');
            $table->string('name')->unique()->comment('Tên danh mục');
            $table->string('slug')->unique()->comment('Đường dẫn tĩnh của danh mục');
            $table->text('description')->nullable()->comment('Mô tả về danh mục');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
