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

        Schema::create('site_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('viewer'); // owner|admin|editor|viewer
            $table->timestamps();

            $table->unique(['site_id','user_id']);     // één record per user per site
            $table->index(['site_id','role']);         // snelle filters
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_user_roles');

        Schema::table('sites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }
};
