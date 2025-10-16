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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('path'); // unique per site, e.g. /over-ons
            $table->string('slug'); // segment
// Translatable JSON columns
            $table->json('title')->nullable();
            $table->json('excerpt')->nullable();
            $table->json('content')->nullable(); // blocks/markdown later
            $table->string('template')->nullable(); // default, landing, etc.
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('noindex')->default(false);
            $table->string('canonical_url')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_homepage')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['site_id', 'path']);
            $table->index(['site_id', 'parent_id']);
            $table->index(['status', 'published_at']);
            $table->index(['is_homepage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
