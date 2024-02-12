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
        Schema::create('heartbeats_single_gameplay', function (Blueprint $table) {
            $table->id();
            $table->integer('heartbeat');
            $table->string('variation');
            $table->string('player_id');
            $table->string('player_score')->nullable();
            $table->string('player_number');
            $table->integer('threshold');
            $table->integer('threshold_breach_status');
            $table->string('gender');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heartbeats_single_gameplay');
    }
};
