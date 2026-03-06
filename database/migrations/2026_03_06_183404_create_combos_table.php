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
        Schema::create('combos', function (Blueprint $table) {
            $table->uuid('combo_id')->primary()->comment('ID combo');
            $table->string('name')->comment('Tên combo');
            $table->decimal('price', 15, 0)->nullable()->comment('Giá combo');
            $table->unsignedInteger('stock')->nullable()->comment('Số lượng tồn kho');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combos');
    }
};
