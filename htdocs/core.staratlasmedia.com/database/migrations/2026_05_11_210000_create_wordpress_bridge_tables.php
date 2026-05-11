<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bridge_setup_tokens', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->char('token_hash', 64)->unique();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->foreignId('site_origin_id')->nullable()->constrained('site_origins')->nullOnDelete();
            $table->string('intended_site_code');
            $table->string('intended_push_group_code')->nullable();
            $table->string('intended_language', 16)->nullable();
            $table->string('intended_section')->nullable();
            $table->string('intended_origin')->nullable();
            $table->string('intended_base_path')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->unsignedBigInteger('consumed_by_installation_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index(['push_group_id', 'status']);
            $table->index(['site_origin_id', 'status']);
        });

        Schema::create('bridge_installations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->foreignId('site_origin_id')->nullable()->constrained('site_origins')->nullOnDelete();
            $table->foreignId('setup_token_id')->nullable()->constrained('bridge_setup_tokens')->nullOnDelete();
            $table->string('site_code')->index();
            $table->string('push_group_code')->nullable()->index();
            $table->string('language', 16)->nullable();
            $table->string('section')->nullable();
            $table->string('origin')->index();
            $table->string('wp_home_url');
            $table->string('wp_site_url')->nullable();
            $table->string('detected_base_path');
            $table->string('plugin_version')->nullable();
            $table->string('wordpress_version')->nullable();
            $table->string('php_version')->nullable();
            $table->string('status')->default('active')->index();
            $table->text('bridge_secret_encrypted');
            $table->string('bridge_secret_fingerprint')->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('last_config_sync_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status']);
            $table->index(['push_group_id', 'status']);
            $table->index(['origin', 'detected_base_path']);
        });

        Schema::table('bridge_setup_tokens', function (Blueprint $table): void {
            $table->foreign('consumed_by_installation_id', 'bridge_setup_tokens_installation_fk')
                ->references('id')
                ->on('bridge_installations')
                ->nullOnDelete();
        });

        Schema::create('bridge_config_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->nullOnDelete();
            $table->unsignedInteger('version');
            $table->json('config_json');
            $table->char('checksum', 64)->index();
            $table->boolean('active')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['bridge_installation_id', 'active']);
            $table->index(['site_id', 'active']);
            $table->index(['push_group_id', 'active']);
        });

        Schema::create('plugin_packages', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('current_stable_version')->nullable();
            $table->string('current_beta_version')->nullable();
            $table->string('status')->default('active')->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('plugin_releases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plugin_package_id')->constrained('plugin_packages')->cascadeOnDelete();
            $table->string('version');
            $table->string('channel')->default('stable')->index();
            $table->string('status')->default('draft')->index();
            $table->string('zip_storage_path')->nullable();
            $table->char('zip_sha256', 64)->nullable()->index();
            $table->unsignedBigInteger('zip_size_bytes')->nullable();
            $table->string('requires_wp')->nullable();
            $table->string('tested_wp')->nullable();
            $table->string('requires_php')->nullable();
            $table->text('changelog')->nullable();
            $table->text('release_notes')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['plugin_package_id', 'version', 'channel'], 'plugin_releases_package_version_channel_unique');
            $table->index(['plugin_package_id', 'channel', 'status']);
        });

        Schema::create('plugin_update_downloads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plugin_release_id')->constrained('plugin_releases')->cascadeOnDelete();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->char('download_token_hash', 64)->nullable()->index();
            $table->string('status')->default('issued')->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('downloaded_at')->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->char('user_agent_hash', 64)->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['plugin_release_id', 'status']);
            $table->index(['bridge_installation_id', 'status']);
        });

        DB::table('plugin_packages')->updateOrInsert(
            ['code' => 'star-atlas-core-bridge'],
            [
                'name' => 'Star Atlas Core Bridge',
                'slug' => 'star-atlas-core-bridge',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_update_downloads');
        Schema::dropIfExists('plugin_releases');
        Schema::dropIfExists('plugin_packages');
        Schema::dropIfExists('bridge_config_versions');

        Schema::table('bridge_setup_tokens', function (Blueprint $table): void {
            $table->dropForeign('bridge_setup_tokens_installation_fk');
        });

        Schema::dropIfExists('bridge_installations');
        Schema::dropIfExists('bridge_setup_tokens');
    }
};
