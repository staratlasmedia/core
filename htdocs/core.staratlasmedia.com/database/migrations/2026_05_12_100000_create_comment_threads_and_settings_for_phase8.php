<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_threads', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->nullOnDelete();
            $table->text('source_url');
            $table->char('source_url_hash', 64);
            $table->text('source_title')->nullable();
            $table->string('language', 16)->nullable()->index();
            $table->string('section')->nullable()->index();
            $table->string('status')->default('open')->index();
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('approved_comments_count')->default(0);
            $table->unsignedInteger('pending_comments_count')->default(0);
            $table->unsignedInteger('rejected_comments_count')->default(0);
            $table->unsignedInteger('reported_comments_count')->default(0);
            $table->timestamp('last_commented_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->json('wp_terms_json')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'source_url_hash'], 'comment_threads_site_source_hash_unique');
            $table->index(['site_id', 'status']);
            $table->index(['push_group_id', 'status']);
            $table->index(['bridge_installation_id', 'status'], 'comment_threads_installation_status_index');
            $table->index('source_url_hash');
        });

        Schema::create('comment_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->cascadeOnDelete();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->cascadeOnDelete();
            $table->string('scope')->index();
            $table->string('scope_key')->unique();
            $table->boolean('comments_enabled')->default(false);
            $table->boolean('require_login')->default(true);
            $table->boolean('allow_guest')->default(false);
            $table->boolean('require_moderation')->default(true);
            $table->boolean('auto_approve_trusted_users')->default(false);
            $table->unsignedTinyInteger('max_depth')->default(3);
            $table->unsignedInteger('max_length')->default(2000);
            $table->unsignedInteger('min_length')->default(2);
            $table->string('default_sort')->nullable();
            $table->unsignedInteger('close_after_days')->nullable();
            $table->json('rate_limit_json')->nullable();
            $table->json('banned_words_json')->nullable();
            $table->json('moderation_rules_json')->nullable();
            $table->boolean('notify_moderators')->default(false);
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index('site_id');
            $table->index('push_group_id');
            $table->index('bridge_installation_id', 'comment_settings_installation_id_index');
            $table->index(['scope', 'comments_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_settings');
        Schema::dropIfExists('comment_threads');
    }
};
