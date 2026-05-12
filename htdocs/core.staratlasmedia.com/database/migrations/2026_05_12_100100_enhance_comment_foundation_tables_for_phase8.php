<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('comment_thread_id')->nullable()->constrained('comment_threads')->nullOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->nullOnDelete();
            $table->foreignId('root_id')->nullable()->constrained('comments')->nullOnDelete();
            $table->unsignedTinyInteger('depth')->default(0)->index();
            $table->char('source_url_hash', 64)->nullable()->index();
            $table->string('author_display_name')->nullable();
            $table->char('author_email_hash', 64)->nullable()->index();
            $table->text('author_avatar_url')->nullable();
            $table->longText('body_html')->nullable();
            $table->integer('score')->default(0)->index();
            $table->char('ip_hash', 64)->nullable()->index();
            $table->char('user_agent_hash', 64)->nullable()->index();
            $table->string('created_by_provider')->nullable()->index();
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('trashed_at')->nullable();
            $table->json('metadata_json')->nullable();

            $table->index(['comment_thread_id', 'status', 'created_at'], 'comments_thread_status_created_index');
            $table->index(['site_id', 'source_url_hash'], 'comments_site_source_hash_index');
            $table->index(['parent_id', 'status']);
            $table->index(['root_id', 'created_at']);
            $table->index(['bridge_installation_id', 'status'], 'comments_installation_status_index');
        });

        Schema::table('comment_reactions', function (Blueprint $table): void {
            $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            $table->char('ip_hash', 64)->nullable()->index();
            $table->char('user_agent_hash', 64)->nullable()->index();
            $table->json('metadata_json')->nullable();

            $table->index(['site_id', 'reaction_type']);
            $table->unique(['comment_id', 'user_id', 'reaction_type'], 'comment_reactions_user_unique');
        });

        Schema::table('comment_reports', function (Blueprint $table): void {
            $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->char('ip_hash', 64)->nullable()->index();
            $table->char('user_agent_hash', 64)->nullable()->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('metadata_json')->nullable();

            $table->index(['site_id', 'status']);
            $table->index(['comment_id', 'status']);
        });

        Schema::table('comment_moderation_events', function (Blueprint $table): void {
            $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->json('metadata_json')->nullable();

            $table->index(['site_id', 'event_type']);
            $table->index(['comment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('comment_moderation_events', function (Blueprint $table): void {
            $table->dropIndex(['site_id', 'event_type']);
            $table->dropIndex(['comment_id', 'created_at']);
            $table->dropConstrainedForeignId('site_id');
            $table->dropColumn(['reason', 'metadata_json']);
        });

        Schema::table('comment_reports', function (Blueprint $table): void {
            $table->dropIndex(['site_id', 'status']);
            $table->dropIndex(['comment_id', 'status']);
            $table->dropConstrainedForeignId('site_id');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['message', 'ip_hash', 'user_agent_hash', 'reviewed_at', 'metadata_json']);
        });

        Schema::table('comment_reactions', function (Blueprint $table): void {
            $table->dropUnique('comment_reactions_user_unique');
            $table->dropIndex(['site_id', 'reaction_type']);
            $table->dropConstrainedForeignId('site_id');
            $table->dropColumn(['ip_hash', 'user_agent_hash', 'metadata_json']);
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropIndex('comments_thread_status_created_index');
            $table->dropIndex('comments_site_source_hash_index');
            $table->dropIndex(['parent_id', 'status']);
            $table->dropIndex(['root_id', 'created_at']);
            $table->dropIndex('comments_installation_status_index');
            $table->dropUnique(['uuid']);
            $table->dropConstrainedForeignId('comment_thread_id');
            $table->dropConstrainedForeignId('push_group_id');
            $table->dropConstrainedForeignId('bridge_installation_id');
            $table->dropConstrainedForeignId('root_id');
            $table->dropColumn([
                'uuid',
                'depth',
                'source_url_hash',
                'author_display_name',
                'author_email_hash',
                'author_avatar_url',
                'body_html',
                'score',
                'ip_hash',
                'user_agent_hash',
                'created_by_provider',
                'edited_at',
                'approved_at',
                'rejected_at',
                'trashed_at',
                'metadata_json',
            ]);
        });
    }
};
