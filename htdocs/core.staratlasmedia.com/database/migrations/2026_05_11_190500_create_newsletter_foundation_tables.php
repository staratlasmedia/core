<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('email_hash', 64)->index();
            $table->text('email_encrypted');
            $table->string('status')->default('subscribed')->index();
            $table->string('language', 16)->nullable()->index();
            $table->text('source_url')->nullable();
            $table->char('source_url_hash', 64)->nullable()->index();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'email_hash'], 'newsletter_site_email_unique');
            $table->index(['site_id', 'status']);
        });

        Schema::create('newsletter_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->string('name');
            $table->string('status')->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'slug'], 'newsletter_lists_site_slug_unique');
        });

        Schema::create('newsletter_list_subscriber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('subscribed')->index();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->unique(['newsletter_list_id', 'newsletter_subscriber_id'], 'newsletter_list_subscriber_unique');
        });

        Schema::create('newsletter_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_subscriber_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('newsletter_list_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type')->index();
            $table->string('provider')->nullable()->index();
            $table->string('provider_message_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['site_id', 'created_at']);
        });

        Schema::create('ses_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('message_id')->nullable()->index();
            $table->string('event_type')->index();
            $table->char('payload_hash', 64)->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ses_webhook_events');
        Schema::dropIfExists('newsletter_events');
        Schema::dropIfExists('newsletter_list_subscriber');
        Schema::dropIfExists('newsletter_lists');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
