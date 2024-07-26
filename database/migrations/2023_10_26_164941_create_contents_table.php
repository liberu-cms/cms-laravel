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
        Schema::create('contents', function (Blueprint $table) {
            $table->integer('content_id', true);
            $table->string('content_title');
            $table->text('content_body');
            $table->integer('author_id');
            $table->date('published_date')->nullable();
            $table->string('content_type');
            $table->integer('category_id');
            $table->string('content_status');
            $table->string('featured_image_url')->nullable();
            $table->string('language_code', 5);
            $table->integer('translation_group_id');
            $table->timestamps();
        
            $table->foreign('author_id')->references('author_id')->on('authors');
            $table->foreign('category_id')->references('content_category_id')->on('content_categories');
            $table->unique(['content_title', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
