<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignUuid('cinema_id')
                ->nullable()
                ->after('user_id')
                ->constrained('cinemas', 'cinema_id')
                ->nullOnDelete()
                ->comment('ID rạp chiếu nơi nhân viên làm việc');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['cinema_id']);
            $table->dropColumn('cinema_id');
        });
    }
};
