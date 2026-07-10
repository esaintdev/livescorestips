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
        Schema::table('vip_plans', function (Blueprint $table) {
            $table->string('country_name')->nullable()->after('currency');
            $table->string('country_code', 5)->nullable()->after('country_name');
            $table->index(['country_name', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vip_plans', function (Blueprint $table) {
            $table->dropIndex(['country_name', 'is_active']);
            $table->dropColumn(['country_name', 'country_code']);
        });
    }
};
