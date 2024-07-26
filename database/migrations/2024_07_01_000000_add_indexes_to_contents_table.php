<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->index('author_id');
            $table->index('published_at');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex(['author_id']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['status']);
        });
    }
};