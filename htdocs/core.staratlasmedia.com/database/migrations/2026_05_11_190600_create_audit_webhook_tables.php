<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_type')->nullable()->index();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('event_type')->index();
            $table->string('auditable_type')->nullable()->index();
            $table->unsignedBigInteger('auditable_id')->nullable()->index();
            $table->char('ip_hash', 64)->nullable()->index();
            $table->char('user_agent_hash', 64)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['site_id', 'created_at']);
        });

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('api_client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->index();
            $table->string('event_type')->index();
            $table->string('event_id')->nullable();
            $table->char('signature_hash', 64)->nullable()->index();
            $table->char('payload_hash', 64)->nullable()->index();
            $table->json('payload')->nullable();
            $table->string('status')->default('received')->index();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['provider', 'event_id'], 'webhook_provider_event_unique');
            $table->index(['site_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('audit_logs');
    }
};
