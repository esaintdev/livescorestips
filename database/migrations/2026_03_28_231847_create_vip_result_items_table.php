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
        Schema::create('vip_result_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_result_id')->constrained('vip_results')->onDelete('cascade');
            $table->string('league_name');
            $table->time('match_time');
            $table->string('home_team');
            $table->string('away_team');
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->string('tip');
            $table->boolean('is_won')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vip_result_items');
    }
};
