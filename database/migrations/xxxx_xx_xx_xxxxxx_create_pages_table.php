<?php

/**
 * Migration for creating the 'pages' table in the database.
 *
 * This migration file is responsible for creating the 'pages' table with fields for title, content, slug, published_at, user_id, and category_id.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('slug')->unique();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
