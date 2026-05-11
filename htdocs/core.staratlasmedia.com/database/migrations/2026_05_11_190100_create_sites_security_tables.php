<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('canonical_origin');
            $table->string('language', 16)->nullable()->index();
            $table->string('push_group')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('site_origins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('origin');
            $table->string('path_prefix')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->unique(['origin', 'path_prefix'], 'site_origins_origin_path_unique');
            $table->index(['site_id', 'status']);
        });

        Schema::create('allowed_origins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('origin');
            $table->string('purpose')->index();
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->unique(['origin', 'purpose'], 'allowed_origins_origin_purpose_unique');
            $table->index(['site_id', 'purpose', 'status']);
        });

        Schema::create('api_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('key_id')->unique();
            $table->char('secret_hash', 64)->nullable()->index();
            $table->text('secret_encrypted')->nullable();
            $table->json('allowed_origins')->nullable();
            $table->json('permissions')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->index(['site_id', 'status']);
        });

        Schema::create('sdk_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->char('token_hash', 64)->unique();
            $table->text('token_encrypted')->nullable();
            $table->json('abilities')->nullable();
            $table->json('allowed_origins')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->index(['site_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sdk_tokens');
        Schema::dropIfExists('api_clients');
        Schema::dropIfExists('allowed_origins');
        Schema::dropIfExists('site_origins');
        Schema::dropIfExists('sites');
    }
};
