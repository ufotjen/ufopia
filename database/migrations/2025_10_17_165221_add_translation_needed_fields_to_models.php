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
        foreach (['user_profiles', 'sites', 'pages', 'menus', 'menu_items'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                if (!Schema::hasColumn($t->getTable(), 'auto_translate')) {
                    $t->boolean('auto_translate')->default(true)->after('updated_at');
                }
                if (!Schema::hasColumn($t->getTable(), 'i18n_overrides')) {
                    $t->json('i18n_overrides')->nullable()->after('auto_translate');
                }
            });
        }

        // 2) MenuItem icon-mode + icon-class
        Schema::table('menu_items', function (Blueprint $t) {
            if (!Schema::hasColumn('menu_items', 'icon_mode')) {
                // none|media|class
                $t->string('icon_mode', 16)->default('none')->after('i18n_overrides');
            }
            if (!Schema::hasColumn('menu_items', 'icon_class')) {
                $t->string('icon_class')->nullable()->after('icon_mode'); // bv. "lucide-home"
            }
        });

        // 3) Site → Theme (optioneel per site een thema kiezen)
        Schema::table('sites', function (Blueprint $t) {
            if (!Schema::hasColumn('sites', 'theme_id')) {
                $t->foreignId('theme_id')->nullable()->constrained()->nullOnDelete()->after('i18n_overrides');
            }
        });
    }

    public function down(): void
    {
        foreach (['user_profiles', 'sites', 'pages', 'menus', 'menu_items'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                if (Schema::hasColumn($t->getTable(), 'i18n_overrides')) $t->dropColumn('i18n_overrides');
                if (Schema::hasColumn($t->getTable(), 'auto_translate')) $t->dropColumn('auto_translate');
            });
        }
        Schema::table('menu_items', function (Blueprint $t) {
            if (Schema::hasColumn('menu_items', 'icon_class')) $t->dropColumn('icon_class');
            if (Schema::hasColumn('menu_items', 'icon_mode')) $t->dropColumn('icon_mode');
        });
        Schema::table('sites', function (Blueprint $t) {
            if (Schema::hasColumn('sites', 'theme_id')) $t->dropConstrainedForeignId('theme_id');
        });
    }
};

