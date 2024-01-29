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
        Schema::table('heartbeats', function (Blueprint $table) {
            $table->integer('threshold_breach_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('heartbeats', function (Blueprint $table) {
            $table->dropColumn('threshold_breach_status');
        });
    }
};
