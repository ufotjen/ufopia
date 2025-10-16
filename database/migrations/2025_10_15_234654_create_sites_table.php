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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();

            // GEEN ->after('id') hier
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->boolean('is_active')->default(true);

            $table->string('primary_domain')->nullable()->unique();
            $table->json('extra_domains')->nullable();

            $table->string('default_locale', 8)->default('nl');
            $table->json('locales')->nullable();

            $table->string('theme_key')->default('tailwind-daisyui');
            $table->json('theme_overrides')->nullable();

            $table->string('timezone')->nullable();
            $table->string('contact_email')->nullable();

            $table->json('feature_flags')->nullable();
            $table->json('options')->nullable();

            // Default menu's op site-niveau
            $table->foreignId('header_menu_id')->nullable();
            $table->foreignId('footer_menu_id')->nullable();
            $table->foreignId('sidebar_menu_id')->nullable();

            // als je 'team_meta' wilde:
            $table->json('team_meta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
