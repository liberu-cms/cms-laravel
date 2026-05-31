<?php

use Biostate\FilamentMenuBuilder\Models\Menu;
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
        Schema::create('menu_items', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('menuable');
            $table->string('name');
            $table->string('type');
            $table->string('url')->nullable();
            $table->string('route')->nullable();
            $table->json('route_parameters')->nullable();
            $table->string('target')->default('_self');
            $table->boolean('use_menuable_name')->default(false);
            $table->string('link_class')->nullable();
            $table->string('wrapper_class')->nullable();
            $table->json('parameters')->nullable();

            $table->foreignIdFor(Menu::class)->constrained()->cascadeOnDelete();

            $table->nestedSet();
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
