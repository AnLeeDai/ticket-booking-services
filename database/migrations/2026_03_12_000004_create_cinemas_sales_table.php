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
        Schema::create('cinemas_sales', function (Blueprint $table) {
            $table->uuid('cinema_sale_id')->primary()->comment('ID doanh thu rạp');
            $table->foreignUuid('cinema_id')->constrained('cinemas', 'cinema_id')->onDelete('cascade')->comment('ID rạp chiếu');
            $table->date('sale_date')->comment('Ngày doanh thu');
            $table->decimal('gross_amount', 15, 0)->nullable()->comment('Tổng doanh thu');
            $table->timestamps();

            $table->unique(['cinema_id', 'sale_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cinemas_sales');
    }
};
