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
        Schema::table('predictions', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('fixture_id');
            $table->integer('home_score')->nullable()->after('away_team');
            $table->integer('away_score')->nullable()->after('home_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['slug', 'home_score', 'away_score']);
        });
    }
};
