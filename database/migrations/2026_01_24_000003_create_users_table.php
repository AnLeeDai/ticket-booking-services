<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('ID người dùng');
            $table->uuid('role_id')
                ->comment('ID vai trò của người dùng');
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('full_name')->comment('Đầy đủ họ tên người dùng');
            $table->string('username')->unique()->index()->comment('Tên đăng nhập của người dùng');
            $table->string('email')->unique()->index()->comment('Địa chỉ email của người dùng');
            $table->string('phone')->nullable()->unique()->comment('Số điện thoại của người dùng');
            $table->string('address')->nullable()->comment('Địa chỉ của người dùng');
            $table->string('avatar_url')->nullable()->comment('Ảnh đại diện của người dùng');
            $table->timestamp('email_verified_at')->nullable()->comment('Thời gian xác minh email');
            $table->string('password')->comment('Mật khẩu đã được mã hóa của người dùng');
            $table->rememberToken()->comment('Token để ghi nhớ phiên đăng nhập');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()->comment('Địa chỉ email để đặt lại mật khẩu');
            $table->string('token')->comment('Token để đặt lại mật khẩu');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('ID phiên làm việc');
            $table->foreignUuid('user_id')->nullable()->index()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete()->comment('ID người dùng');
            $table->string('ip_address', 45)->nullable()->comment('Địa chỉ IP');
            $table->text('user_agent')->nullable()->comment('User agent');
            $table->longText('payload')->comment('Dữ liệu phiên làm việc');
            $table->integer('last_activity')->index()->comment('Thời gian hoạt động cuối cùng');
        });

        DB::table('users')->upsert([
            'id' => (string) Str::uuid(),
            'role_id' => DB::table('roles')->where('name', 'admin')->value('id'),
            'full_name' => 'System Administrator',
            'username' => 'ticketbooking2002',
            'email' => 'ticketbooking2002@gmail.com',
            'phone' => '0334920373',
            'address' => 'Đống Đa, Hà Nội, Việt Nam',
            'avatar_url' => null,
            'password' => Hash::make('Ticketbooking2002@'),
            'created_at' => now(),
            'updated_at' => now(),
        ], ['email'], ['full_name', 'username', 'password', 'updated_at']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
