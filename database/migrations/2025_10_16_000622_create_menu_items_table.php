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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->enum('type', ['url', 'page', 'route'])->default('url');
            $table->json('label'); // translatable text
            $table->string('url')->nullable(); // when type=url
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('route_name')->nullable(); // when type=route
            $table->json('route_params')->nullable(); // { slug: '...' }
            $table->enum('target', ['self', 'blank'])->default('self');
            $table->string('icon')->nullable(); // lucide:home
            $table->boolean('visible')->default(true);
            $table->json('roles_visible')->nullable(); // ["admin","editor"]
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['menu_id', 'parent_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
