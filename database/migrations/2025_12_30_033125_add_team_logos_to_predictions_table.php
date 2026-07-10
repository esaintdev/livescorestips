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
            $table->string('home_team_logo')->nullable()->after('home_team');
            $table->string('away_team_logo')->nullable()->after('away_team');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['home_team_logo', 'away_team_logo']);
        });
    }
};
