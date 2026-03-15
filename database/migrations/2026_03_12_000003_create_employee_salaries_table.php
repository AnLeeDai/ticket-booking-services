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
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->uuid('employee_salary_id')->primary()->comment('ID bảng lương');
            $table->foreignUuid('employee_id')->unique()->constrained('employees', 'employee_id')->onDelete('cascade')->comment('ID nhân viên (1-1)');
            $table->string('bank_number')->comment('Số tài khoản ngân hàng');
            $table->string('bank_name')->comment('Tên ngân hàng');
            $table->decimal('net_salary', 15, 0)->comment('Lương ròng');
            $table->decimal('bonus', 15, 0)->nullable()->comment('Thưởng');
            $table->decimal('total_earn', 15, 0)->comment('Tổng thu nhập');
            $table->enum('payment_status', ['IS_PENDING', 'IN_ACTIVE', 'UN_ACTIVE'])
                ->default('IS_PENDING')
                ->comment('Trạng thái thanh toán: IS_PENDING=chờ, IN_ACTIVE=đã thanh toán, UN_ACTIVE=huỷ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};
