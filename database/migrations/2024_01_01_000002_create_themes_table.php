<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('version');
            $table->string('author')->nullable();
            $table->string('author_uri')->nullable();
            $table->string('theme_uri')->nullable();
            $table->string('screenshot')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable();
            $table->json('template_parts')->nullable();
            $table->longText('custom_css')->nullable();
            $table->longText('custom_js')->nullable();
            $table->json('color_scheme')->nullable();
            $table->json('typography')->nullable();
            $table->json('layout_options')->nullable();
            $table->json('widget_areas')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('themes');
    }
};