<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('potential_trade_routes', function (Blueprint $table) {
            $table->foreignId('ship_id')
                ->nullable()
                ->constrained()
                ->default(null)
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('potential_trade_routes', function (Blueprint $table) {
            $table->dropForeign(['ship_id']);
            $table->dropColumn('ship_id');
        });
    }
};
