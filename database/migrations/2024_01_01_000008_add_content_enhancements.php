<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('body');
            $table->string('seo_title')->nullable()->after('slug');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('seo_keywords')->nullable()->after('seo_description');
            $table->string('canonical_url')->nullable()->after('seo_keywords');
            $table->integer('reading_time')->nullable()->after('canonical_url');
            $table->integer('word_count')->nullable()->after('reading_time');
            $table->boolean('is_featured')->default(false)->after('word_count');
            $table->boolean('is_sticky')->default(false)->after('is_featured');
            $table->boolean('allow_comments')->default(true)->after('is_sticky');
            $table->boolean('password_protected')->default(false)->after('allow_comments');
            $table->string('content_password')->nullable()->after('password_protected');
            $table->string('template')->nullable()->after('content_password');
            $table->json('custom_fields')->nullable()->after('template');
            $table->json('tags')->nullable()->after('custom_fields');
            $table->json('related_content_ids')->nullable()->after('tags');
            $table->json('social_shares')->nullable()->after('related_content_ids');
            $table->foreignId('last_modified_by')->nullable()->constrained('users')->after('social_shares');
            $table->decimal('content_score', 5, 2)->nullable()->after('last_modified_by');
            $table->decimal('readability_score', 5, 2)->nullable()->after('content_score');
        });
    }

    public function down()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn([
                'excerpt',
                'seo_title',
                'seo_description', 
                'seo_keywords',
                'canonical_url',
                'reading_time',
                'word_count',
                'is_featured',
                'is_sticky',
                'allow_comments',
                'password_protected',
                'content_password',
                'template',
                'custom_fields',
                'tags',
                'related_content_ids',
                'social_shares',
                'last_modified_by',
                'content_score',
                'readability_score',
            ]);
        });
    }
};