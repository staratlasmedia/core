<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_origin_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->char('external_post_url_hash', 64)->index();
            $table->text('source_url')->nullable();
            $table->longText('body');
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('replies_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('reports_count')->default(0);
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index(['site_id', 'external_post_url_hash']);
        });

        Schema::create('comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('anonymous_id')->nullable()->index();
            $table->string('reaction_type')->index();
            $table->timestamps();

            $table->index(['comment_id', 'reaction_type']);
        });

        Schema::create('comment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason')->index();
            $table->string('status')->default('open')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('comment_moderation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('moderator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type')->index();
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_moderation_events');
        Schema::dropIfExists('comment_reports');
        Schema::dropIfExists('comment_reactions');
        Schema::dropIfExists('comments');
    }
};
