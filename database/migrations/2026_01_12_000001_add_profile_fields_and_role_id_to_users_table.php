<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('id');
            $table->string('profile_picture')->after('name');
            $table->string('phone_number')->unique()->after('email');
            $table->string('address')->after('phone_number');
            $table->date('date_of_birth')->nullable()->after('address');

            $table->foreignId('role_id')
                ->nullable()
                ->after('date_of_birth')
                ->constrained('roles')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn([
                'username',
                'profile_picture',
                'phone_number',
                'address',
                'date_of_birth',
                'role_id',
            ]);
        });
    }
};
