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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('employee_id')->primary()->comment('ID nhân viên');
            $table->foreignUuid('employee_role_id')->constrained('employee_roles', 'employee_role_id')->onDelete('cascade')->comment('ID vai trò nhân viên');
            $table->foreignUuid('user_id')->unique()->constrained('users', 'user_id')->onDelete('cascade')->comment('ID tài khoản (1 user -> 1 employee)');
            $table->string('name')->comment('Tên nhân viên');
            $table->string('code')->unique()->comment('Mã nhân viên (tự sinh)');
            $table->date('hire_date')->comment('Ngày bắt đầu làm việc');
            $table->date('end_date')->nullable()->comment('Ngày kết thúc làm việc');
            $table->enum('status', ['IN_ACTIVE', 'UN_ACTIVE'])
                ->default('IN_ACTIVE')
                ->comment('Trạng thái: IN_ACTIVE=đang làm việc, UN_ACTIVE=nghỉ việc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
