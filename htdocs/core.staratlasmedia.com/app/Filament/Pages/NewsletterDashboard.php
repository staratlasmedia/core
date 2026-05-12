<?php

namespace App\Filament\Pages;

use App\Models\AiGenerationJob;
use App\Models\EditorialContentSource;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDeliveryLog;
use App\Models\NewsletterEngagementEvent;
use App\Models\NewsletterImportBatch;
use App\Models\NewsletterSubscriber;
use App\Models\NewsletterSuppression;
use App\Models\SnsWebhookEvent;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class NewsletterDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Newsletter';

    protected static ?string $navigationLabel = 'Overview';

    protected string $view = 'filament.pages.newsletter-dashboard';

    /**
     * @return array<string, int>
     */
    public function getStats(): array
    {
        $sent = NewsletterDeliveryLog::query()->whereNotNull('sent_at')->count();
        $delivered = NewsletterDeliveryLog::query()->whereNotNull('delivered_at')->count();
        $opened = NewsletterDeliveryLog::query()->where('open_count', '>', 0)->count();
        $clicked = NewsletterDeliveryLog::query()->where('click_count', '>', 0)->count();
        $bounced = NewsletterDeliveryLog::query()->whereNotNull('bounced_at')->count();
        $complained = NewsletterDeliveryLog::query()->whereNotNull('complained_at')->count();

        return [
            'subscribers' => NewsletterSubscriber::query()->count(),
            'subscribed' => NewsletterSubscriber::query()->where('status', 'subscribed')->count(),
            'pending' => NewsletterSubscriber::query()->where('status', 'pending')->count(),
            'unsubscribed' => NewsletterSubscriber::query()->where('status', 'unsubscribed')->count(),
            'suppressed' => NewsletterSuppression::query()->count(),
            'campaign_drafts' => NewsletterCampaign::query()->whereIn('status', ['draft', 'ai_draft', 'editorial_review'])->count(),
            'imports' => NewsletterImportBatch::query()->count(),
            'sent' => $sent,
            'delivered' => $delivered,
            'opened' => $opened,
            'clicked' => $clicked,
            'bounced' => $bounced,
            'complained' => $complained,
            'delivery_rate' => $this->rate($delivered, $sent),
            'aperture_open_rate' => $this->rate($opened, max($delivered, $sent)),
            'click_rate' => $this->rate($clicked, max($delivered, $sent)),
            'click_to_open_rate' => $this->rate($clicked, $opened),
            'bounce_rate' => $this->rate($bounced, $sent),
            'complaint_rate' => $this->rate($complained, $sent),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getHealth(): array
    {
        return [
            'recent_sns_events' => SnsWebhookEvent::query()->latest('received_at')->limit(5)->get(['sns_type', 'status', 'received_at', 'processed_at']),
            'webhook_failures_24h' => SnsWebhookEvent::query()->where('created_at', '>=', now()->subDay())->where('status', 'failed')->count(),
            'content_sources_error' => EditorialContentSource::query()->where('status', 'error')->orWhereNotNull('last_error_at')->count(),
            'content_sources_active' => EditorialContentSource::query()->where('status', 'active')->count(),
            'ai_jobs_failed_24h' => AiGenerationJob::query()->where('created_at', '>=', now()->subDay())->where('status', 'failed')->count(),
            'imports_pending' => NewsletterImportBatch::query()->whereIn('status', ['uploaded', 'dry_run_completed', 'importing'])->count(),
        ];
    }

    /**
     * @return array<int, object>
     */
    public function getTopClickedLinks()
    {
        return NewsletterEngagementEvent::query()
            ->select('url_hash', DB::raw('max(url) as url'), DB::raw('count(*) as clicks'))
            ->where('event_type', 'click')
            ->groupBy('url_hash')
            ->orderByDesc('clicks')
            ->limit(5)
            ->get();
    }

    private function rate(int $count, int $total): int
    {
        return $total > 0 ? (int) round(($count / $total) * 100) : 0;
    }
}
