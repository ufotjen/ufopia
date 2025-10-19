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
        Schema::table('menu_items', function (Blueprint $t) {
            if (!Schema::hasColumn('menu_items','icon_mode')) {
                $t->string('icon_mode', 16)->default('none')->after('i18n_overrides'); // none|media|class
            }
            if (!Schema::hasColumn('menu_items','icon_class')) {
                $t->string('icon_class')->nullable()->after('icon_mode'); // bv. "lucide-home"
            }
        });
    }
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $t) {
            if (Schema::hasColumn('menu_items','icon_class')) $t->dropColumn('icon_class');
            if (Schema::hasColumn('menu_items','icon_mode'))  $t->dropColumn('icon_mode');
        });
    }
};
