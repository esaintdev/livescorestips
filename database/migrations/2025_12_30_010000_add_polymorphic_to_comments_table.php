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
            // Add polymorphic columns
            $table->string('commentable_type')->nullable()->after('id');
            $table->unsignedBigInteger('commentable_id')->nullable()->after('commentable_type');

            // Add index for polymorphic relationship
            $table->index(['commentable_type', 'commentable_id']);
        });

        // Migrate existing article comments to polymorphic format
        DB::table('comments')->whereNotNull('article_id')->update([
            'commentable_type' => 'App\\Models\\Article',
            'commentable_id' => DB::raw('article_id')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['commentable_type', 'commentable_id']);
            $table->dropColumn(['commentable_type', 'commentable_id']);
        });
    }
};
