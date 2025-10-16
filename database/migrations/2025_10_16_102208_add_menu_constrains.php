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
        if (Schema::hasTable('menus')) {
            // Sites → menus
            Schema::table('sites', function (Blueprint $table) {
                if (Schema::hasColumn('sites', 'header_menu_id')) {
                    $table->foreign('header_menu_id')->references('id')->on('menus')->nullOnDelete();
                }
                if (Schema::hasColumn('sites', 'footer_menu_id')) {
                    $table->foreign('footer_menu_id')->references('id')->on('menus')->nullOnDelete();
                }
                if (Schema::hasColumn('sites', 'sidebar_menu_id')) {
                    $table->foreign('sidebar_menu_id')->references('id')->on('menus')->nullOnDelete();
                }
            });

            // Pages → menus
            Schema::table('pages', function (Blueprint $table) {
                if (Schema::hasColumn('pages', 'header_menu_id')) {
                    $table->foreign('header_menu_id')->references('id')->on('menus')->nullOnDelete();
                }
                if (Schema::hasColumn('pages', 'footer_menu_id')) {
                    $table->foreign('footer_menu_id')->references('id')->on('menus')->nullOnDelete();
                }
                if (Schema::hasColumn('pages', 'sidebar_menu_id')) {
                    $table->foreign('sidebar_menu_id')->references('id')->on('menus')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
        if (Schema::hasColumn('pages', 'site_id')) $table->dropForeign(['site_id']);
        if (Schema::hasColumn('pages', 'header_menu_id')) $table->dropForeign(['header_menu_id']);
        if (Schema::hasColumn('pages', 'footer_menu_id')) $table->dropForeign(['footer_menu_id']);
        if (Schema::hasColumn('pages', 'sidebar_menu_id')) $table->dropForeign(['sidebar_menu_id']);
    });

        Schema::table('sites', function (Blueprint $table) {
            if (Schema::hasColumn('sites', 'header_menu_id')) $table->dropForeign(['header_menu_id']);
            if (Schema::hasColumn('sites', 'footer_menu_id')) $table->dropForeign(['footer_menu_id']);
            if (Schema::hasColumn('sites', 'sidebar_menu_id')) $table->dropForeign(['sidebar_menu_id']);
        });
    }
};
