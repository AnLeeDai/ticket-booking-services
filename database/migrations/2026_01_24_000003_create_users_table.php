<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('user_id')->primary()->comment('ID người dùng');
            $table->uuid('role_id')->comment('ID vai trò của người dùng');
            $table->foreign('role_id')
                ->references('role_id')
                ->on('roles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('user_name')->unique()->index()->comment('Tên đăng nhập');
            $table->string('full_name')->comment('Họ tên đầy đủ');
            $table->string('email')->unique()->index()->comment('Email');
            $table->string('password')->comment('Mật khẩu đã mã hóa');
            $table->string('phone')->nullable()->unique()->comment('Số điện thoại');
            $table->date('dob')->nullable()->comment('Ngày sinh');
            $table->string('address')->nullable()->comment('Địa chỉ');
            $table->string('avatar_url')->nullable()->comment('Ảnh đại diện');
            $table->enum('status', ['IN_ACTIVE', 'UN_ACTIVE'])
                ->default('IN_ACTIVE')
                ->comment('Trạng thái');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()->comment('Địa chỉ email để đặt lại mật khẩu');
            $table->string('token')->comment('Token để đặt lại mật khẩu');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('ID phiên làm việc');
            $table->uuid('user_id')->nullable()->index()->comment('ID người dùng');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable()->comment('Địa chỉ IP');
            $table->text('user_agent')->nullable()->comment('User agent');
            $table->longText('payload')->comment('Dữ liệu phiên làm việc');
            $table->integer('last_activity')->index()->comment('Thời gian hoạt động cuối cùng');
        });

        $accounts = ['admin', 'manager', 'employee', 'customer'];

        $roles = DB::table('roles')->pluck('role_id', 'name')->toArray();

        $phones = [
            'admin' => '0900000001',
            'manager' => '0900000002',
            'employee' => '0900000003',
            'customer' => '0900000004',
        ];

        $data = collect($accounts)->map(function ($role) use ($roles, $phones) {
            return [
                'user_id' => (string)Str::uuid(),
                'role_id' => $roles[$role],
                'user_name' => $role . '_account',
                'full_name' => ucfirst($role) . ' Account',
                'email' => $role . '@ticketbooking.com',
                'password' => Hash::make('Password@123'),
                'phone' => $phones[$role],
                'dob' => null,
                'address' => 'Hà Nội',
                'avatar_url' => null,
                'status' => 'IN_ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        DB::table('users')->upsert(
            $data,
            ['email'],
            ['role_id', 'user_name', 'full_name', 'phone', 'password', 'dob', 'address', 'avatar_url', 'status', 'updated_at']
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
