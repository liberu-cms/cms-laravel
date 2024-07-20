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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');

            $table->unsignedBigInteger('category_id')->nullable()->after('author_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');

            $table->integer('category_id');
            $table->foreign('category_id')->references('content_category_id')->on('content_categories');
        });

        Schema::dropIfExists('categories');
    }
};