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
        Schema::create('user_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();

            $t->string('username')->unique();     // openbare handle
            $t->string('slug')->unique();         // voor URLs (mag == username)
            $t->string('tagline')->nullable();    // slagzin
            $t->text('bio')->nullable();

            // admin toggle (los van users.is_active)
            $t->boolean('is_profile_active')->default(true);

            // optionele vrije velden; handig maar niet verplicht
            $t->json('social_links')->nullable(); // { twitter:"", github:"", ... }
            $t->json('preferences')->nullable();  // theming e.d.

            $t->softDeletes();
            $t->timestamps();

            $t->index(['is_profile_active']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('user_profiles');
    }
};
