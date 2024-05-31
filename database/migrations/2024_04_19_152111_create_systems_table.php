<?php

use App\Enums\SystemTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('systems', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('symbol')->unique();
            $table->string('sector_symbol');
            $table->enum('type', SystemTypes::values());
            $table->integer('x');
            $table->integer('y');
        });

        Schema::create('faction_system', function (Blueprint $table) {
            $table->foreignId('faction_id')->constrained();
            $table->foreignId('system_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faction_system');
        Schema::dropIfExists('systems');
    }
};
