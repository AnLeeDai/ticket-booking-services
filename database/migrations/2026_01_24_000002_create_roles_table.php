<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('role_id')->primary()->comment('ID vai trò');
            $table->string('name', 50)->unique()->comment('Tên vai trò: admin, manager, employee, customer');
            $table->string('description')->nullable()->comment('Mô tả vai trò');
            $table->timestamps();
        });

        DB::table('roles')->upsert([
            ['role_id' => (string) Str::uuid(), 'name' => 'admin', 'description' => 'Quản trị viên hệ thống', 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => (string) Str::uuid(), 'name' => 'manager', 'description' => 'Quản lý', 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => (string) Str::uuid(), 'name' => 'employee', 'description' => 'Nhân viên', 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => (string) Str::uuid(), 'name' => 'customer', 'description' => 'Khách hàng', 'created_at' => now(), 'updated_at' => now()],
        ], ['name'], ['description', 'updated_at']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
