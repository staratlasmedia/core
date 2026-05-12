<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('newsletter_subscribers', 'uuid')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table): void {
                $table->uuid('uuid')->nullable()->unique()->after('id');
                $table->foreignId('push_group_id')->nullable()->after('site_id')->constrained('push_groups')->nullOnDelete();
                $table->foreignId('bridge_installation_id')->nullable()->after('push_group_id')->constrained('bridge_installations')->nullOnDelete();
                $table->char('normalized_email_hash', 64)->nullable()->after('email_hash')->index();
                $table->string('source_title')->nullable()->after('source_url_hash');
                $table->string('source_type')->nullable()->after('source_title')->index();
                $table->string('consent_version')->nullable()->after('source_type');
                $table->char('consent_ip_hash', 64)->nullable()->after('consent_version')->index();
                $table->char('consent_user_agent_hash', 64)->nullable()->after('consent_ip_hash')->index();
                $table->timestamp('consented_at')->nullable()->after('consent_user_agent_hash');
                $table->timestamp('confirmed_at')->nullable()->after('consented_at');
                $table->timestamp('bounced_at')->nullable()->after('unsubscribed_at');
                $table->timestamp('complained_at')->nullable()->after('bounced_at');
                $table->timestamp('last_sent_at')->nullable()->after('complained_at');

                $table->index(['push_group_id', 'status']);
                $table->index(['bridge_installation_id', 'status'], 'newsletter_subscribers_installation_status_idx');
            });
        }

        if (! Schema::hasColumn('newsletter_lists', 'uuid')) {
            Schema::table('newsletter_lists', function (Blueprint $table): void {
                $table->uuid('uuid')->nullable()->unique()->after('id');
                $table->foreignId('push_group_id')->nullable()->after('site_id')->constrained('push_groups')->nullOnDelete();
                $table->string('code')->nullable()->after('push_group_id')->index();
                $table->text('description')->nullable()->after('name');
                $table->string('language', 16)->nullable()->after('description')->index();
                $table->unsignedBigInteger('default_from_identity_id')->nullable()->after('status')->index();
                $table->boolean('double_opt_in')->nullable()->after('default_from_identity_id');

                $table->unique('code', 'newsletter_lists_code_unique');
                $table->index(['push_group_id', 'status']);
            });
        }

        if (! Schema::hasColumn('newsletter_list_subscriber', 'source_url')) {
            Schema::table('newsletter_list_subscriber', function (Blueprint $table): void {
                $table->text('source_url')->nullable()->after('unsubscribed_at');
                $table->json('metadata_json')->nullable()->after('source_url');
            });
        }

        Schema::create('email_sender_identities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('from_name');
            $table->string('from_email');
            $table->string('reply_to')->nullable();
            $table->string('ses_identity_arn')->nullable();
            $table->string('ses_configuration_set')->nullable();
            $table->string('region')->nullable();
            $table->text('aws_access_key_id_encrypted')->nullable();
            $table->text('aws_secret_access_key_encrypted')->nullable();
            $table->boolean('send_enabled')->default(false)->index();
            $table->boolean('test_send_enabled')->default(false);
            $table->unsignedInteger('max_send_rate_per_minute')->nullable();
            $table->string('status')->default('disabled')->index();
            $table->json('last_test_result_json')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->cascadeOnDelete();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->cascadeOnDelete();
            $table->string('scope')->index();
            $table->string('scope_key')->unique();
            $table->boolean('newsletter_enabled')->default(false);
            $table->boolean('double_opt_in')->default(true);
            $table->boolean('require_consent')->default(true);
            $table->boolean('send_enabled')->default(false);
            $table->boolean('allow_import')->default(false);
            $table->boolean('ai_generation_enabled')->default(false);
            $table->boolean('rss_import_enabled')->default(false);
            $table->boolean('wordpress_api_import_enabled')->default(false);
            $table->boolean('automatic_digest_enabled')->default(false);
            $table->foreignId('default_list_id')->nullable()->constrained('newsletter_lists')->nullOnDelete();
            $table->string('default_language', 16)->nullable();
            $table->foreignId('default_sender_identity_id')->nullable()->constrained('email_sender_identities')->nullOnDelete();
            $table->unsignedBigInteger('default_template_id')->nullable()->index();
            $table->unsignedInteger('max_send_rate_per_minute')->nullable();
            $table->json('rate_limit_json')->nullable();
            $table->json('editorial_workflow_json')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index('site_id');
            $table->index('push_group_id');
            $table->index('bridge_installation_id', 'newsletter_settings_installation_id_idx');
            $table->index(['scope', 'newsletter_enabled']);
        });

        Schema::create('audience_topics', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('audience_topics')->nullOnDelete();
            $table->string('type')->index();
            $table->string('slug');
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('language', 16)->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->integer('sort_order')->default(0);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('site_id');
            $table->index('push_group_id');
            $table->index('parent_id');
            $table->unique(['site_id', 'push_group_id', 'type', 'slug'], 'audience_topics_scope_type_slug_unique');
        });

        Schema::create('audience_topic_channel_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audience_topic_id')->constrained('audience_topics')->cascadeOnDelete();
            $table->string('channel')->index();
            $table->boolean('enabled')->default(true);
            $table->boolean('visible_in_forms')->default(true);
            $table->boolean('default_selected')->default(false);
            $table->boolean('requires_explicit_consent')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['audience_topic_id', 'channel'], 'audience_topic_channel_unique');
        });

        Schema::create('newsletter_subscriber_topic_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_subscriber_id')->constrained(indexName: 'nsp_subscriber_fk')->cascadeOnDelete();
            $table->foreignId('audience_topic_id')->constrained('audience_topics', indexName: 'nsp_topic_fk')->cascadeOnDelete();
            $table->string('status')->default('subscribed')->index();
            $table->string('source')->default('explicit')->index();
            $table->text('source_url')->nullable();
            $table->string('consent_version')->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['newsletter_subscriber_id', 'audience_topic_id'], 'newsletter_subscriber_topic_unique');
        });

        Schema::create('push_subscription_topic_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('push_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audience_topic_id')->constrained('audience_topics')->cascadeOnDelete();
            $table->string('status')->default('subscribed')->index();
            $table->string('source')->default('explicit')->index();
            $table->text('source_url')->nullable();
            $table->string('consent_version')->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['push_subscription_id', 'audience_topic_id'], 'push_subscription_topic_pref_unique');
        });

        Schema::create('audience_topic_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audience_topic_id')->constrained('audience_topics')->cascadeOnDelete();
            $table->string('source_type')->index();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('source_key')->nullable()->index();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_key']);
        });

        Schema::create('audience_preference_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('newsletter_subscriber_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('audience_topic_id')->nullable()->constrained('audience_topics')->nullOnDelete();
            $table->string('channel')->index();
            $table->string('event_type')->index();
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->string('source')->nullable()->index();
            $table->text('source_url')->nullable();
            $table->char('ip_hash', 64)->nullable()->index();
            $table->char('user_agent_hash', 64)->nullable()->index();
            $table->json('metadata_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('audience_preference_forms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->nullOnDelete();
            $table->string('channel')->index();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('active')->index();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('submit_label')->nullable();
            $table->boolean('require_at_least_one_topic')->default(false);
            $table->boolean('show_select_all')->default(true);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('audience_preference_form_topics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audience_preference_form_id')->constrained('audience_preference_forms', indexName: 'apft_form_fk')->cascadeOnDelete();
            $table->foreignId('audience_topic_id')->constrained('audience_topics')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('default_selected')->default(false);
            $table->boolean('required')->default(false);
            $table->boolean('visible')->default(true);
            $table->string('label_override')->nullable();
            $table->text('help_text')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['audience_preference_form_id', 'audience_topic_id'], 'audience_form_topic_unique');
        });

        Schema::create('audience_topic_taxonomy_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audience_topic_id')->constrained('audience_topics')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->nullOnDelete();
            $table->string('wp_taxonomy')->nullable()->index();
            $table->unsignedBigInteger('wp_term_id')->nullable()->index();
            $table->string('wp_term_slug')->nullable()->index();
            $table->string('wp_term_name')->nullable();
            $table->string('wp_post_type')->nullable()->index();
            $table->string('match_type')->default('manual')->index();
            $table->string('status')->default('active')->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_suppressions', function (Blueprint $table): void {
            $table->id();
            $table->char('email_hash', 64)->index();
            $table->string('reason')->index();
            $table->string('scope')->default('global')->index();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('newsletter_list_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source')->nullable()->index();
            $table->string('event_id')->nullable()->index();
            $table->timestamp('suppressed_at')->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['email_hash', 'scope']);
        });

        Schema::create('newsletter_import_batches', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->nullOnDelete();
            $table->foreignId('newsletter_list_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_type')->default('csv')->index();
            $table->string('original_filename')->nullable();
            $table->string('storage_path')->nullable();
            $table->string('status')->default('uploaded')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->unsignedInteger('duplicate_rows')->default(0);
            $table->unsignedInteger('suppressed_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->json('mapping_json')->nullable();
            $table->json('options_json')->nullable();
            $table->json('dry_run_report_json')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_import_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_import_batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('raw_row_json')->nullable();
            $table->char('normalized_email_hash', 64)->nullable()->index();
            $table->text('email_encrypted')->nullable();
            $table->string('status')->default('pending')->index();
            $table->json('validation_errors_json')->nullable();
            $table->foreignId('newsletter_subscriber_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['newsletter_import_batch_id', 'status'], 'newsletter_import_rows_batch_status_idx');
        });

        Schema::create('newsletter_templates', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('campaign')->index();
            $table->string('subject_template')->nullable();
            $table->string('preheader_template')->nullable();
            $table->longText('html_template')->nullable();
            $table->longText('text_template')->nullable();
            $table->json('editor_schema_json')->nullable();
            $table->json('design_tokens_json')->nullable();
            $table->longText('mjml_template')->nullable();
            $table->string('status')->default('active')->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('editorial_content_sources', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->nullOnDelete();
            $table->string('type')->index();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('url')->nullable();
            $table->text('api_base_url')->nullable();
            $table->string('auth_type')->nullable();
            $table->text('auth_config_encrypted')->nullable();
            $table->string('language', 16)->nullable()->index();
            $table->string('section')->nullable()->index();
            $table->string('status')->default('disabled')->index();
            $table->boolean('polling_enabled')->default(false);
            $table->unsignedInteger('polling_interval_minutes')->nullable();
            $table->timestamp('last_polled_at')->nullable();
            $table->timestamp('last_successful_poll_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->string('etag')->nullable();
            $table->string('last_modified_header')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('editorial_content_items', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('content_source_id')->nullable()->constrained('editorial_content_sources')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->string('source_type')->index();
            $table->string('source_id')->nullable()->index();
            $table->text('source_url');
            $table->char('source_url_hash', 64)->index();
            $table->text('title');
            $table->text('excerpt')->nullable();
            $table->longText('body_summary')->nullable();
            $table->text('image_url')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('modified_at')->nullable();
            $table->string('author')->nullable();
            $table->string('language', 16)->nullable()->index();
            $table->string('section')->nullable()->index();
            $table->string('post_type')->nullable()->index();
            $table->unsignedBigInteger('wp_post_id')->nullable()->index();
            $table->json('wp_terms_json')->nullable();
            $table->json('raw_payload_json')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['content_source_id', 'source_id'], 'editorial_items_source_id_unique');
            $table->unique(['site_id', 'source_url_hash'], 'editorial_items_site_url_hash_unique');
        });

        Schema::create('editorial_content_taxonomy_terms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_source_id')->nullable()->constrained('editorial_content_sources')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('taxonomy')->index();
            $table->unsignedBigInteger('term_id')->nullable()->index();
            $table->string('slug')->index();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('editorial_content_item_terms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('editorial_content_item_id')->constrained('editorial_content_items')->cascadeOnDelete();
            $table->foreignId('editorial_content_taxonomy_term_id')->constrained('editorial_content_taxonomy_terms', indexName: 'ecit_term_fk')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['editorial_content_item_id', 'editorial_content_taxonomy_term_id'], 'editorial_item_term_unique');
        });

        Schema::create('editorial_content_source_post_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('editorial_content_source_id')->constrained('editorial_content_sources', indexName: 'ecsp_source_fk')->cascadeOnDelete();
            $table->string('post_type')->index();
            $table->boolean('enabled')->default(true);
            $table->boolean('include_in_digest')->default(true);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('editorial_content_source_taxonomies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('editorial_content_source_id')->constrained('editorial_content_sources', indexName: 'ecst_source_fk')->cascadeOnDelete();
            $table->string('taxonomy')->index();
            $table->boolean('enabled')->default(true);
            $table->boolean('import_terms')->default(true);
            $table->boolean('map_to_audience_topics')->default(false);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('provider_type')->index();
            $table->string('status')->default('disabled')->index();
            $table->text('base_url')->nullable();
            $table->text('api_key_encrypted')->nullable();
            $table->string('organization_id')->nullable();
            $table->string('project_id')->nullable();
            $table->string('default_model')->nullable();
            $table->decimal('default_temperature', 4, 2)->nullable();
            $table->unsignedInteger('default_max_tokens')->nullable();
            $table->json('rate_limit_json')->nullable();
            $table->boolean('cost_tracking_enabled')->default(false);
            $table->json('last_test_result_json')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_model_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ai_provider_id')->constrained('ai_providers')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('model');
            $table->string('purpose')->default('generic')->index();
            $table->decimal('temperature', 4, 2)->nullable();
            $table->unsignedInteger('max_tokens')->nullable();
            $table->json('response_format_json')->nullable();
            $table->longText('system_prompt')->nullable();
            $table->string('status')->default('disabled')->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['ai_provider_id', 'code'], 'ai_model_profiles_provider_code_unique');
        });

        Schema::create('ai_prompt_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('purpose')->default('generic')->index();
            $table->longText('system_prompt')->nullable();
            $table->longText('user_prompt_template');
            $table->json('variables_json')->nullable();
            $table->string('status')->default('active')->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_generation_jobs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ai_provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->foreignId('ai_model_profile_id')->nullable()->constrained('ai_model_profiles')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('related_type')->nullable()->index();
            $table->unsignedBigInteger('related_id')->nullable()->index();
            $table->string('purpose')->index();
            $table->string('status')->default('draft')->index();
            $table->json('input_json')->nullable();
            $table->json('output_json')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->nullable();
            $table->decimal('estimated_cost', 12, 6)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->foreignId('newsletter_list_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->foreignId('from_identity_id')->nullable()->constrained('email_sender_identities')->nullOnDelete();
            $table->string('reply_to')->nullable();
            $table->string('status')->default('draft')->index();
            $table->foreignId('template_id')->nullable()->constrained('newsletter_templates')->nullOnDelete();
            $table->string('source_type')->nullable()->index();
            $table->longText('html_body')->nullable();
            $table->longText('text_body')->nullable();
            $table->json('editor_schema_json')->nullable();
            $table->foreignId('ai_generation_id')->nullable()->constrained('ai_generation_jobs')->nullOnDelete();
            $table->unsignedBigInteger('content_collection_id')->nullable()->index();
            $table->unsignedBigInteger('digest_recipe_id')->nullable()->index();
            $table->unsignedBigInteger('digest_run_id')->nullable()->index();
            $table->json('segment_json')->nullable();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_campaign_content_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('editorial_content_item_id')->constrained('editorial_content_items', indexName: 'ncci_item_fk')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->string('role')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_campaign_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_campaign_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('html_body')->nullable();
            $table->longText('text_body')->nullable();
            $table->json('editor_schema_json')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('change_note')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['newsletter_campaign_id', 'version'], 'newsletter_campaign_versions_unique');
        });

        Schema::create('newsletter_previews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('preview_context_json')->nullable();
            $table->longText('rendered_html')->nullable();
            $table->longText('rendered_text')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('newsletter_digest_recipes', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->foreignId('newsletter_list_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('status')->default('paused')->index();
            $table->string('frequency')->default('manual')->index();
            $table->string('timezone')->nullable();
            $table->string('schedule_cron')->nullable();
            $table->string('language', 16)->nullable()->index();
            $table->string('section')->nullable()->index();
            $table->foreignId('template_id')->nullable()->constrained('newsletter_templates')->nullOnDelete();
            $table->foreignId('sender_identity_id')->nullable()->constrained('email_sender_identities')->nullOnDelete();
            $table->boolean('ai_enabled')->default(false);
            $table->boolean('require_editorial_approval')->default(true);
            $table->boolean('auto_schedule')->default(false);
            $table->boolean('auto_send')->default(false);
            $table->unsignedInteger('max_items')->default(5);
            $table->unsignedInteger('min_items')->default(1);
            $table->unsignedInteger('lookback_hours')->nullable();
            $table->json('selection_rules_json')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_digest_recipe_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_digest_recipe_id')->constrained(indexName: 'ndrs_recipe_fk')->cascadeOnDelete();
            $table->foreignId('editorial_content_source_id')->constrained('editorial_content_sources', indexName: 'ndrs_source_fk')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_digest_recipe_topics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_digest_recipe_id')->constrained(indexName: 'ndrt_recipe_fk')->cascadeOnDelete();
            $table->foreignId('audience_topic_id')->constrained('audience_topics')->cascadeOnDelete();
            $table->string('include_mode')->default('include')->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_digest_runs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('newsletter_digest_recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('queued')->index();
            $table->date('run_date')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_digest_run_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_digest_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('editorial_content_item_id')->constrained('editorial_content_items')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->string('role')->nullable();
            $table->string('selection_reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_delivery_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('newsletter_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_subscriber_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_list_id')->nullable()->constrained()->nullOnDelete();
            $table->char('email_hash', 64)->index();
            $table->string('provider')->default('ses')->index();
            $table->string('provider_message_id')->nullable()->index();
            $table->string('ses_message_id')->nullable()->index();
            $table->string('status')->default('queued')->index();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamp('complained_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('first_opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('first_clicked_at')->nullable();
            $table->timestamp('last_clicked_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['newsletter_campaign_id', 'status'], 'newsletter_delivery_campaign_status_idx');
        });

        Schema::create('newsletter_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_subscriber_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_list_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_campaign_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_delivery_log_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->char('token_hash', 64)->unique();
            $table->string('status')->default('active')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_engagement_events', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('newsletter_campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('newsletter_delivery_log_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('newsletter_subscriber_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type')->index();
            $table->text('url')->nullable();
            $table->char('url_hash', 64)->nullable()->index();
            $table->char('email_hash', 64)->nullable()->index();
            $table->char('ip_hash', 64)->nullable()->index();
            $table->char('user_agent_hash', 64)->nullable()->index();
            $table->timestamp('occurred_at')->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('sns_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('sns_message_id')->nullable()->index();
            $table->string('sns_type')->nullable()->index();
            $table->string('topic_arn')->nullable()->index();
            $table->string('signature_version')->nullable();
            $table->text('signing_cert_url')->nullable();
            $table->char('signature_hash', 64)->nullable()->index();
            $table->json('raw_payload_json')->nullable();
            $table->string('status')->default('received')->index();
            $table->timestamp('received_at')->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sns_webhook_events');
        Schema::dropIfExists('newsletter_engagement_events');
        Schema::dropIfExists('newsletter_tokens');
        Schema::dropIfExists('newsletter_delivery_logs');
        Schema::dropIfExists('newsletter_digest_run_items');
        Schema::dropIfExists('newsletter_digest_runs');
        Schema::dropIfExists('newsletter_digest_recipe_topics');
        Schema::dropIfExists('newsletter_digest_recipe_sources');
        Schema::dropIfExists('newsletter_digest_recipes');
        Schema::dropIfExists('newsletter_previews');
        Schema::dropIfExists('newsletter_campaign_versions');
        Schema::dropIfExists('newsletter_campaign_content_items');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('ai_generation_jobs');
        Schema::dropIfExists('ai_prompt_templates');
        Schema::dropIfExists('ai_model_profiles');
        Schema::dropIfExists('ai_providers');
        Schema::dropIfExists('editorial_content_source_taxonomies');
        Schema::dropIfExists('editorial_content_source_post_types');
        Schema::dropIfExists('editorial_content_item_terms');
        Schema::dropIfExists('editorial_content_taxonomy_terms');
        Schema::dropIfExists('editorial_content_items');
        Schema::dropIfExists('editorial_content_sources');
        Schema::dropIfExists('newsletter_templates');
        Schema::dropIfExists('newsletter_import_rows');
        Schema::dropIfExists('newsletter_import_batches');
        Schema::dropIfExists('newsletter_suppressions');
        Schema::dropIfExists('audience_topic_taxonomy_mappings');
        Schema::dropIfExists('audience_preference_form_topics');
        Schema::dropIfExists('audience_preference_forms');
        Schema::dropIfExists('audience_preference_events');
        Schema::dropIfExists('audience_topic_mappings');
        Schema::dropIfExists('push_subscription_topic_preferences');
        Schema::dropIfExists('newsletter_subscriber_topic_preferences');
        Schema::dropIfExists('audience_topic_channel_settings');
        Schema::dropIfExists('audience_topics');
        Schema::dropIfExists('newsletter_settings');
        Schema::dropIfExists('email_sender_identities');

        Schema::table('newsletter_list_subscriber', function (Blueprint $table): void {
            $table->dropColumn(['source_url', 'metadata_json']);
        });

        Schema::table('newsletter_lists', function (Blueprint $table): void {
            $table->dropUnique('newsletter_lists_code_unique');
            $table->dropIndex(['push_group_id', 'status']);
            $table->dropColumn(['uuid', 'code', 'description', 'language', 'default_from_identity_id', 'double_opt_in']);
            $table->dropConstrainedForeignId('push_group_id');
        });

        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            $table->dropIndex(['push_group_id', 'status']);
            $table->dropIndex('newsletter_subscribers_installation_status_idx');
            $table->dropConstrainedForeignId('push_group_id');
            $table->dropConstrainedForeignId('bridge_installation_id');
            $table->dropColumn([
                'uuid',
                'normalized_email_hash',
                'source_title',
                'source_type',
                'consent_version',
                'consent_ip_hash',
                'consent_user_agent_hash',
                'consented_at',
                'confirmed_at',
                'bounced_at',
                'complained_at',
                'last_sent_at',
            ]);
        });
    }
};
