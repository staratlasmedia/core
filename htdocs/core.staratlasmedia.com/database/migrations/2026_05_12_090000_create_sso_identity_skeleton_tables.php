<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->index();
            $table->string('status')->default('disabled')->index();
            $table->unsignedInteger('sort_order')->default(100)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_public')->default(false)->index();
            $table->json('config_json')->nullable();
            $table->text('encrypted_config_json')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('auth_provider_site_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('auth_provider_id')->constrained('auth_providers')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('push_group_id')->nullable()->constrained('push_groups')->cascadeOnDelete();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->cascadeOnDelete();
            $table->string('status')->default('inherited')->index();
            $table->json('config_json')->nullable();
            $table->text('encrypted_config_json')->nullable();
            $table->timestamps();

            $table->index(['auth_provider_id', 'status']);
            $table->index(['site_id', 'status']);
            $table->index(['push_group_id', 'status']);
            $table->index(['bridge_installation_id', 'status']);
        });

        Schema::create('webauthn_credentials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->char('credential_id_hash', 64)->unique();
            $table->text('credential_id_encrypted')->nullable();
            $table->text('public_key');
            $table->unsignedBigInteger('sign_count')->default(0);
            $table->json('transports_json')->nullable();
            $table->string('attestation_type')->nullable();
            $table->string('aaguid')->nullable()->index();
            $table->string('name')->nullable();
            $table->timestamp('last_used_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'last_used_at']);
        });

        Schema::create('webauthn_challenges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->char('challenge_hash', 64)->unique();
            $table->string('type')->index();
            $table->string('rp_id');
            $table->string('origin');
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable()->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['type', 'expires_at']);
            $table->index(['user_id', 'type']);
        });

        Schema::create('magic_link_tokens', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->index();
            $table->char('token_hash', 64)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bridge_installation_id')->nullable()->constrained('bridge_installations')->nullOnDelete();
            $table->string('status')->default('active')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable()->index();
            $table->char('ip_hash', 64)->nullable()->index();
            $table->char('user_agent_hash', 64)->nullable()->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['email', 'status']);
            $table->index(['site_id', 'status']);
            $table->index(['bridge_installation_id', 'status']);
        });

        Schema::table('auth_authorization_codes', function (Blueprint $table): void {
            $table->foreignId('bridge_installation_id')->nullable()->after('site_origin_id')->constrained('bridge_installations')->nullOnDelete();
            $table->text('redirect_uri')->nullable()->after('redirect_url');
            $table->string('status')->default('active')->after('nonce_hash')->index();
        });

        DB::table('auth_authorization_codes')
            ->whereNull('redirect_uri')
            ->update(['redirect_uri' => DB::raw('redirect_url')]);

        Schema::table('social_identities', function (Blueprint $table): void {
            $table->string('provider_user_id')->nullable()->after('provider_id');
            $table->string('name')->nullable()->after('email');
            $table->text('access_token_encrypted')->nullable()->after('avatar_url');
            $table->text('refresh_token_encrypted')->nullable()->after('access_token_encrypted');
            $table->timestamp('token_expires_at')->nullable()->after('refresh_token_encrypted');
        });

        DB::table('social_identities')
            ->whereNull('provider_user_id')
            ->update(['provider_user_id' => DB::raw('provider_id')]);

        Schema::table('publisher_provided_ids', function (Blueprint $table): void {
            $table->unique('ppid', 'publisher_provided_ids_ppid_unique');
            $table->unique(['scope', 'site_id', 'user_id'], 'ppids_scope_site_user_unique');
        });

        Schema::table('auth_sessions', function (Blueprint $table): void {
            $table->uuid('session_uuid')->nullable()->after('id')->unique();
            $table->string('status')->default('active')->after('session_hash')->index();
            $table->timestamp('last_seen_at')->nullable()->after('ip_hash')->index();
            $table->json('metadata_json')->nullable()->after('revoked_at');
        });

        Schema::table('login_events', function (Blueprint $table): void {
            $table->foreignId('bridge_installation_id')->nullable()->after('site_origin_id')->constrained('bridge_installations')->nullOnDelete();
            $table->string('result')->nullable()->after('success')->index();
            $table->json('metadata_json')->nullable()->after('metadata');
        });

        $this->seedDisabledProviders();
    }

    public function down(): void
    {
        Schema::table('login_events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('bridge_installation_id');
            $table->dropColumn(['result', 'metadata_json']);
        });

        Schema::table('auth_sessions', function (Blueprint $table): void {
            $table->dropUnique(['session_uuid']);
            $table->dropColumn(['session_uuid', 'status', 'last_seen_at', 'metadata_json']);
        });

        Schema::table('publisher_provided_ids', function (Blueprint $table): void {
            $table->dropUnique('publisher_provided_ids_ppid_unique');
            $table->dropUnique('ppids_scope_site_user_unique');
        });

        Schema::table('social_identities', function (Blueprint $table): void {
            $table->dropColumn([
                'provider_user_id',
                'name',
                'access_token_encrypted',
                'refresh_token_encrypted',
                'token_expires_at',
            ]);
        });

        Schema::table('auth_authorization_codes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('bridge_installation_id');
            $table->dropColumn(['redirect_uri', 'status']);
        });

        Schema::dropIfExists('magic_link_tokens');
        Schema::dropIfExists('webauthn_challenges');
        Schema::dropIfExists('webauthn_credentials');
        Schema::dropIfExists('auth_provider_site_settings');
        Schema::dropIfExists('auth_providers');
    }

    private function seedDisabledProviders(): void
    {
        $providers = [
            ['code' => 'passkey', 'name' => 'Passkey', 'type' => 'passkey', 'sort_order' => 10, 'is_default' => true],
            ['code' => 'google', 'name' => 'Google', 'type' => 'oauth', 'sort_order' => 20, 'is_default' => false],
            ['code' => 'apple', 'name' => 'Apple', 'type' => 'oauth', 'sort_order' => 30, 'is_default' => false],
            ['code' => 'magic_link', 'name' => 'Email Magic Link', 'type' => 'magic_link', 'sort_order' => 40, 'is_default' => false],
            ['code' => 'password', 'name' => 'Password', 'type' => 'password', 'sort_order' => 50, 'is_default' => false],
            ['code' => 'facebook', 'name' => 'Facebook', 'type' => 'oauth', 'sort_order' => 60, 'is_default' => false],
        ];

        foreach ($providers as $provider) {
            DB::table('auth_providers')->updateOrInsert(
                ['code' => $provider['code']],
                [
                    'name' => $provider['name'],
                    'type' => $provider['type'],
                    'status' => 'disabled',
                    'sort_order' => $provider['sort_order'],
                    'is_default' => $provider['is_default'],
                    'is_public' => false,
                    'config_json' => json_encode($this->defaultConfigFor($provider['code']), JSON_THROW_ON_ERROR),
                    'encrypted_config_json' => null,
                    'metadata_json' => json_encode([
                        'phase' => 'phase_7_skeleton',
                        'production_ready' => false,
                    ], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultConfigFor(string $code): array
    {
        return match ($code) {
            'passkey' => [
                'rp_id' => 'core.staratlasmedia.com',
                'origin' => 'https://core.staratlasmedia.com',
                'display_name' => 'Star Atlas Media Core',
                'user_verification' => 'preferred',
                'resident_key' => 'preferred',
                'attestation' => 'none',
                'timeout_ms' => 60000,
            ],
            'google', 'facebook' => [
                'client_id' => null,
                'redirect_uri' => null,
                'scopes' => [],
                'button_label' => ucfirst($code),
            ],
            'apple' => [
                'client_id' => null,
                'team_id' => null,
                'key_id' => null,
                'redirect_uri' => null,
                'scopes' => [],
                'button_label' => 'Apple',
            ],
            'magic_link' => [
                'token_ttl_minutes' => 15,
                'max_attempts' => 3,
                'rate_limit' => '5,1',
                'email_template_key' => null,
                'sender_identity' => null,
                'allow_new_user_registration' => false,
            ],
            'password' => [
                'allow_registration' => false,
                'require_email_verification' => true,
                'min_password_length' => 12,
                'password_policy_json' => [],
                'rate_limit' => '5,1',
                'reset_password_enabled' => false,
            ],
            default => [],
        };
    }
};
