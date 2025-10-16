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
            $table->string('template')->nullable(); // default, landing, etc.
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('noindex')->default(false);
            $table->string('canonical_url')->nullable();
           $table->integer('sort_order')->default(0);
            $table->boolean('is_homepage')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['site_id', 'path']);
            $table->index(['site_id', 'parent_id']);
            $table->index(['status', 'published_at']);
            $table->index(['is_homepage']);

            // Inhoud / SEO / opties
            $table->json('content')->nullable(); // of blocks-json
            $table->json('seo')->nullable();     // title/desc/open graph …
            $table->json('meta')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();

            $table->json('options')->nullable();

            // Menu overrides op pagina-niveau (vallen terug naar site-defaults als null)
            $table->foreignId('header_menu_id')->nullable();
            $table->foreignId('footer_menu_id')->nullable();
            $table->foreignId('sidebar_menu_id')->nullable();

            // Extra, arbitraire menu’s (JSON array met items zoals { "key": "cta", "menu_id": 5 })
            $table->json('extra_menus')->nullable();
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
