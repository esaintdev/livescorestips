<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->integer('fixture_id')->index();
            $table->string('league_name')->nullable();
            $table->string('league_country')->nullable();
            $table->string('league_logo')->nullable();
            $table->string('home_team');
            $table->string('away_team');
            $table->dateTime('match_date')->index();
            $table->string('match_time', 10);
            $table->json('home_form')->nullable();
            $table->json('away_form')->nullable();
            $table->string('tip', 100);
            $table->decimal('odds', 5, 2);
            $table->decimal('probability', 5, 2);
            $table->string('bookmaker_name', 100)->nullable();
            $table->string('bookmaker_image')->nullable();
            $table->text('bookmaker_url')->nullable();
            $table->enum('status', ['pending', 'published', 'completed'])->default('pending')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
