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
        Schema::create('vip_results', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('total_odds', 8, 2);
            $table->enum('status', ['won', 'lost', 'pending'])->default('pending');
            $table->string('win_rate_display')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vip_results');
    }
};
