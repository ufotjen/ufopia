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
        Schema::create('images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_profile_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('media_id');       // verwijst naar spatie media
            $t->string('alt')->nullable();
            $t->boolean('is_main')->default(false);
            $t->unsignedInteger('position')->default(0);
            $t->string('status')->default('published');   // bv. draft|pending|published (optioneel)
            $t->json('extra')->nullable();                // vrije metadata (exif, palette)

            $t->timestamps();

            $t->unique(['user_profile_id', 'media_id']);
            $t->index(['user_profile_id', 'is_main']);
        });

        Schema::table('images', function (Blueprint $t) {
            $t->foreign('media_id')->references('id')->on('media')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('images');
    }
};
