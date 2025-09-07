<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('blockables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_block_id')->constrained()->onDelete('cascade');
            $table->morphs('blockable');
            $table->integer('order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['content_block_id', 'blockable_id', 'blockable_type'], 'blockable_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blockables');
    }
};