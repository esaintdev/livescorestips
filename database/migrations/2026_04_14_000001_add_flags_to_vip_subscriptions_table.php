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
        Schema::table('vip_subscriptions', function (Blueprint $table) {
            $table->boolean('is_disabled')->default(false)->after('status');
            $table->boolean('is_flagged')->default(false)->after('is_disabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vip_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['is_disabled', 'is_flagged']);
        });
    }
};
