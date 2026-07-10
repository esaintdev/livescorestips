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
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('article_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We cannot easily reverse this as it might violate constraints if we have NULLs
        // But for consistency we can try to make it nullable false if we handle data
        Schema::table('comments', function (Blueprint $table) {
            // $table->unsignedBigInteger('article_id')->nullable(false)->change();
        });
    }
};
