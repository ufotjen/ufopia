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
        Schema::table('menus', function (Blueprint $table) {
            $table->string('slug', 190)->after('title');
            // Per site unieke slug:
            $table->unique(['site_id', 'slug'], 'menus_site_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropUnique('menus_site_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
