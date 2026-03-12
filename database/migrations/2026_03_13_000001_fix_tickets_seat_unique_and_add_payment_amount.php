<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bỏ unique trên seat_id (cho phép đặt lại ghế sau khi huỷ vé)
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique(['seat_id']);

            // Thêm index thường cho seat_id (FK vẫn giữ)
            $table->index('seat_id');
            // Index cho query thường xuyên
            $table->index('user_id');
            $table->index('status');
        });

        // 2. Thêm cột amount vào payments
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 15, 0)->nullable()->after('method')->comment('Số tiền thanh toán');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['seat_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->unique('seat_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};
