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
        Schema::table('user_profiles', function (Blueprint $t) {
            // taglines zijn kort – TEXT volstaat ruim voor JSON
            $t->text('tagline')->nullable()->change();

            // bio kan lang zijn – LONGTEXT is veilig
            $t->longText('bio')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $t) {
            $t->string('tagline', 255)->nullable()->change();
            $t->text('bio')->nullable()->change(); // of string(255) als je dat eerder had
        });
    }
};
