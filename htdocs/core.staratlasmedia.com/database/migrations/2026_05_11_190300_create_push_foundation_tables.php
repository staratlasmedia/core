<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_push_apps', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_appid')->unique();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('origin');
            $table->string('language', 16)->nullable()->index();
            $table->string('section')->nullable()->index();
            $table->string('merge_group')->nullable()->index();
            $table->string('service_worker_url');
            $table->string('service_worker_scope');
            $table->unsignedBigInteger('vapid_key_set_id')->nullable()->index();
            $table->string('legacy_title')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'legacy_appid']);
        });

        Schema::create('vapid_key_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('legacy_push_app_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('public_key');
            $table->text('private_key_encrypted');
            $table->string('source')->default('core')->index();
            $table->boolean('active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'active']);
        });

        Schema::create('push_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('anonymous_id')->nullable()->index();
            $table->string('language', 16)->nullable()->index();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'user_id']);
        });

        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_origin_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_subscriber_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source')->index();
            $table->string('status')->index();
            $table->unsignedBigInteger('superseded_by_subscription_id')->nullable()->index();
            $table->foreignId('legacy_push_app_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('legacy_appid')->nullable()->index();
            $table->string('legacy_device_id')->nullable()->index();
            $table->string('legacy_userid')->nullable()->index();
            $table->unsignedSmallInteger('platform_id')->nullable()->index();
            $table->string('platform_name')->nullable();
            $table->string('origin');
            $table->string('service_worker_url');
            $table->string('service_worker_scope');
            $table->char('endpoint_hash', 64)->unique();
            $table->text('endpoint_encrypted');
            $table->text('p256dh_encrypted')->nullable();
            $table->text('auth_encrypted')->nullable();
            $table->foreignId('vapid_key_set_id')->constrained('vapid_key_sets')->restrictOnDelete();
            $table->string('language', 16)->nullable()->index();
            $table->string('section')->nullable()->index();
            $table->string('merge_group')->nullable()->index();
            $table->text('source_url')->nullable();
            $table->char('source_url_hash', 64)->nullable()->index();
            $table->timestamp('created_at_legacy')->nullable();
            $table->timestamp('last_active_at_legacy')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index(['site_id', 'source']);
        });

        Schema::create('push_subscription_contexts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('push_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_origin_id')->nullable()->constrained()->nullOnDelete();
            $table->text('source_url');
            $table->char('source_url_hash', 64)->index();
            $table->string('source_title')->nullable();
            $table->string('language', 16)->nullable()->index();
            $table->string('section')->nullable()->index();
            $table->json('wp_terms_json')->nullable();
            $table->text('referrer')->nullable();
            $table->json('utm_json')->nullable();
            $table->char('user_agent_hash', 64)->nullable()->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['site_id', 'created_at']);
        });

        Schema::create('push_reconfirmation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_origin_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('legacy_push_app_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('legacy_appid')->nullable()->index();
            $table->string('legacy_device_id')->nullable()->index();
            $table->string('old_status')->index();
            $table->string('new_status')->index();
            $table->string('match_method')->nullable()->index();
            $table->text('source_url')->nullable();
            $table->char('source_url_hash', 64)->nullable()->index();
            $table->string('language', 16)->nullable();
            $table->string('section')->nullable();
            $table->string('origin')->nullable();
            $table->string('service_worker_url')->nullable();
            $table->string('service_worker_scope')->nullable();
            $table->string('manifest_id')->nullable();
            $table->string('sw_version')->nullable();
            $table->char('user_agent_hash', 64)->nullable()->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['site_id', 'created_at']);
        });

        Schema::create('push_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('slug');
            $table->string('label');
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->unique(['site_id', 'type', 'slug'], 'push_topics_site_type_slug_unique');
        });

        Schema::create('push_subscription_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('push_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('push_topic_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['push_subscription_id', 'push_topic_id'], 'push_subscription_topic_unique');
        });

        Schema::create('push_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('status')->default('draft')->index();
            $table->json('payload');
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status']);
        });

        Schema::create('push_campaign_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('push_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('target_type')->index();
            $table->string('target_value');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['push_campaign_id', 'target_type']);
        });

        Schema::create('push_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('push_campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->index();
            $table->unsignedSmallInteger('response_code')->nullable()->index();
            $table->text('error')->nullable();
            $table->timestamp('attempted_at')->index();
            $table->timestamp('delivered_at')->nullable()->index();
            $table->json('metadata')->nullable();

            $table->index(['site_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_delivery_logs');
        Schema::dropIfExists('push_campaign_targets');
        Schema::dropIfExists('push_campaigns');
        Schema::dropIfExists('push_subscription_topics');
        Schema::dropIfExists('push_topics');
        Schema::dropIfExists('push_reconfirmation_events');
        Schema::dropIfExists('push_subscription_contexts');
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('push_subscribers');
        Schema::dropIfExists('vapid_key_sets');
        Schema::dropIfExists('legacy_push_apps');
    }
};
