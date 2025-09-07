<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('version');
            $table->string('author')->nullable();
            $table->string('author_uri')->nullable();
            $table->string('plugin_uri')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable();
            $table->json('dependencies')->nullable();
            $table->string('min_php_version')->nullable();
            $table->string('min_cms_version')->nullable();
            $table->string('namespace')->nullable();
            $table->string('main_file')->default('plugin.php');
            $table->json('hooks')->nullable();
            $table->json('shortcodes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plugins');
    }
};