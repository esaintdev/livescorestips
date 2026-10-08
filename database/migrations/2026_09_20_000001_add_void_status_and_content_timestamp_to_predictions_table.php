<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen the predictions.status enum to include 'void' and add a
     * meaningful content modification timestamp for sitemap lastmod.
     *
     * Non-destructive: existing status values (pending/won/lost) are
     * preserved. The enum is only widened. Nullability is left exactly as
     * the previous migration defined it (the column has no explicit
     * NOT NULL since 2025_12_06_225758_modify_status_in_predictions_table).
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            // Inspect current definition is done out-of-band (see deploy notes).
            // Widening the enum cannot lose existing values.
            DB::statement("ALTER TABLE predictions MODIFY COLUMN status ENUM('pending','won','lost','void') DEFAULT 'pending'");
        }

        if (! Schema::hasColumn('predictions', 'content_updated_at')) {
            Schema::table('predictions', function (Blueprint $table) {
                $table->timestamp('content_updated_at')->nullable()->after('updated_at');
            });
        }

        // Backfill from the existing updated_at so lastmod is meaningful
        // immediately rather than null.
        DB::table('predictions')
            ->whereNull('content_updated_at')
            ->update(['content_updated_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('predictions', 'content_updated_at')) {
            Schema::table('predictions', function (Blueprint $table) {
                $table->dropColumn('content_updated_at');
            });
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            // Never leave rows with a value the narrower enum cannot hold.
            DB::table('predictions')->where('status', 'void')->update(['status' => 'pending']);
            DB::statement("ALTER TABLE predictions MODIFY COLUMN status ENUM('pending','won','lost') DEFAULT 'pending'");
        }
    }
};
