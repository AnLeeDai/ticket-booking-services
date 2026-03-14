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
        Schema::create('employee_roles', function (Blueprint $table) {
            $table->uuid('employee_role_id')->primary()->comment('ID vai trò nhân viên');
            $table->enum('name', ['STAFF', 'PROBATION'])->comment('Loại vai trò: STAFF=nhân viên, PROBATION=thử việc');
            $table->text('description')->nullable()->comment('Mô tả vai trò');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_roles');
    }
};
