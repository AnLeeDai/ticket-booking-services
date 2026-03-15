<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cinemas', function (Blueprint $table) {
            $table->foreignUuid('manager_id')
                ->nullable()
                ->after('code')
                ->constrained('users', 'user_id')
                ->nullOnDelete()
                ->comment('ID người quản lý rạp');
        });
    }

    public function down(): void
    {
        Schema::table('cinemas', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
};
