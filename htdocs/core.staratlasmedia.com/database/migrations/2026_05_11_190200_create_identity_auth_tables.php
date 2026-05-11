<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->index();
            $table->string('provider_id');
            $table->string('email')->nullable()->index();
            $table->text('avatar_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_id'], 'social_provider_id_unique');
        });

        Schema::create('publisher_provided_ids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('ppid');
            $table->string('scope')->index();
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('rotated_at')->nullable();
            $table->timestamps();

            $table->unique(['scope', 'site_id', 'ppid'], 'ppids_scope_site_ppid_unique');
            $table->index(['user_id', 'scope']);
            $table->index('site_id');
        });

        Schema::create('auth_authorization_codes', function (Blueprint $table) {
            $table->id();
            $table->char('code_hash', 64)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_origin_id')->nullable()->constrained()->nullOnDelete();
            $table->string('origin');
            $table->text('redirect_url');
            $table->char('state_hash', 64);
            $table->char('nonce_hash', 64);
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'expires_at']);
        });

        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('site_origin_id')->nullable()->constrained()->nullOnDelete();
            $table->string('origin')->nullable();
            $table->char('session_hash', 64)->unique();
            $table->char('user_agent_hash', 64)->nullable()->index();
            $table->char('ip_hash', 64)->nullable()->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();

            $table->index(['site_id', 'expires_at']);
        });

        Schema::create('login_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('site_origin_id')->nullable()->constrained()->nullOnDelete();
            $table->string('origin')->nullable();
            $table->string('event_type')->index();
            $table->string('provider')->nullable()->index();
            $table->boolean('success')->default(false)->index();
            $table->char('ip_hash', 64)->nullable()->index();
            $table->char('user_agent_hash', 64)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['site_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_events');
        Schema::dropIfExists('auth_sessions');
        Schema::dropIfExists('auth_authorization_codes');
        Schema::dropIfExists('publisher_provided_ids');
        Schema::dropIfExists('social_identities');
    }
};
