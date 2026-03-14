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
        Schema::create('ticket_combos', function (Blueprint $table) {
            $table->foreignUuid('ticket_id')->constrained('tickets', 'ticket_id')->onDelete('cascade')->comment('ID vé');
            $table->foreignUuid('combo_id')->constrained('combos', 'combo_id')->onDelete('cascade')->comment('ID combo');
            $table->unsignedInteger('qty')->default(1)->comment('Số lượng');

            $table->primary(['ticket_id', 'combo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_combos');
    }
};
