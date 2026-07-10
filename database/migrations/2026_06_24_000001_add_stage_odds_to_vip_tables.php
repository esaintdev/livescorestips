<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_results', function (Blueprint $table) {
            $table->string('stage', 20)->default('completed')->after('win_rate_display');
        });

        Schema::table('vip_result_items', function (Blueprint $table) {
            $table->decimal('odds', 8, 2)->nullable()->after('tip');
            $table->boolean('is_won')->nullable()->default(null)->change();
        });

        // Set existing records as completed so they remain visible on public page
        DB::table('vip_results')->update(['stage' => 'completed']);
    }

    public function down(): void
    {
        Schema::table('vip_results', function (Blueprint $table) {
            $table->dropColumn('stage');
        });

        Schema::table('vip_result_items', function (Blueprint $table) {
            $table->dropColumn('odds');
            $table->boolean('is_won')->default(false)->change();
        });
    }
};
