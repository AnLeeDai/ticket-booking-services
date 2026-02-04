<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumb_url')->nullable();
            $table->string('trailer_url')->nullable();
            $table->integer('duration_minutes');
            $table->string('language');
            $table->integer('age');
            $table->decimal('rating', 2, 1)->nullable();
            $table->date('release_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['IS_PENDING', 'IN_ACTIVE', 'UN_ACTIVE'])->default('IS_PENDING');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
