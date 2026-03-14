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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('payment_id')->primary()->comment('ID thanh toán');
            $table->foreignUuid('ticket_id')->unique()->constrained('tickets', 'ticket_id')->onDelete('cascade')->comment('ID vé (1-1)');
            $table->enum('method', ['TRANSFER', 'CARD', 'CASH'])->comment('Hình thức: TRANSFER=chuyển khoản, CARD=thẻ, CASH=tiền mặt');
            $table->enum('status', ['IN_ACTIVE', 'UN_ACTIVE', 'IS_PENDING'])
                ->default('IS_PENDING')
                ->comment('Trạng thái: IS_PENDING=chờ, IN_ACTIVE=thành công, UN_ACTIVE=thất bại');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
