<?php

use App\Enums\SurveySizes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('signature')
                ->unique();
            $table->tinyText('waypoint_symbol');
            $table->dateTime('expiration');
            $table->enum('size', SurveySizes::values());
            $table->foreignId('agent_id')->constrained();
            $table->text('raw_response');
        });

        Schema::create('deposit_survey', function (Blueprint $table) {
            $table->foreignId('survey_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('deposit_id')
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_survey');
        Schema::dropIfExists('surveys');
    }
};
