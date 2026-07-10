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
        Schema::create('site_links', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('url');
            $table->string('image')->nullable(); // For partner logos
            $table->enum('section', ['partner', 'footer']); // Differentiate between Partner and Footer links
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_links');
    }
};
