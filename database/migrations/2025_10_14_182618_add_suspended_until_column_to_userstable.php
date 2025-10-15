<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_active')->default(false);
            $t->boolean('soft_blocked')->default(false);
            $t->timestamp('suspended_until')->nullable();
       });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['is_active','soft_blocked','suspended_until','two_factor_confirmed_at']);
        });
    }
};
