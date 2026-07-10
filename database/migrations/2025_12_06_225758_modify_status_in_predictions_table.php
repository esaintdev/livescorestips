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
            // Update existing records to a valid value for the new ENUM
            // Mapping 'published' and 'completed' to 'pending' or keeping them if they were in the new list (they are not)
            \DB::table('predictions')->update(['status' => 'pending']);

            // We use DB::statement because changing ENUM values can be tricky with Schema builder alone
            // depending on the DB driver version.
            \DB::statement("ALTER TABLE predictions MODIFY COLUMN status ENUM('pending', 'won', 'lost') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            \DB::statement("ALTER TABLE predictions MODIFY COLUMN status ENUM('pending', 'published', 'completed') DEFAULT 'pending'");
        });
    }
};
