<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_import_batches', function (Blueprint $table): void {
            $table->timestamp('dry_run_completed_at')->nullable()->after('dry_run_report_json');
            $table->timestamp('committed_at')->nullable()->after('dry_run_completed_at');
            $table->foreignId('committed_by')->nullable()->after('committed_at')->constrained('users')->nullOnDelete();
            $table->json('commit_report_json')->nullable()->after('committed_by');
        });

        Schema::table('newsletter_suppressions', function (Blueprint $table): void {
            $table->unique(['email_hash', 'scope', 'site_id', 'newsletter_list_id'], 'newsletter_suppressions_scope_unique');
        });

        Schema::table('newsletter_delivery_logs', function (Blueprint $table): void {
            $table->index(['provider', 'provider_message_id'], 'newsletter_delivery_provider_message_idx');
            $table->index(['provider', 'ses_message_id'], 'newsletter_delivery_provider_ses_idx');
        });

        Schema::table('sns_webhook_events', function (Blueprint $table): void {
            $table->char('payload_hash', 64)->nullable()->after('signature_hash')->index();
            $table->timestamp('verified_at')->nullable()->after('received_at')->index();
            $table->unique('sns_message_id', 'sns_webhook_events_message_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sns_webhook_events', function (Blueprint $table): void {
            $table->dropUnique('sns_webhook_events_message_unique');
            $table->dropIndex(['payload_hash']);
            $table->dropIndex(['verified_at']);
            $table->dropColumn(['payload_hash', 'verified_at']);
        });

        Schema::table('newsletter_delivery_logs', function (Blueprint $table): void {
            $table->dropIndex('newsletter_delivery_provider_message_idx');
            $table->dropIndex('newsletter_delivery_provider_ses_idx');
        });

        Schema::table('newsletter_suppressions', function (Blueprint $table): void {
            $table->dropUnique('newsletter_suppressions_scope_unique');
        });

        Schema::table('newsletter_import_batches', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('committed_by');
            $table->dropColumn(['dry_run_completed_at', 'committed_at', 'commit_report_json']);
        });
    }
};
