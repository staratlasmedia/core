<?php

namespace App\Services\Newsletter;

use App\Models\AiProvider;
use App\Models\NewsletterCampaign;
use App\Services\Ai\AiGenerationService;

class NewsletterAiDraftService
{
    public function __construct(
        private readonly AiGenerationService $ai,
        private readonly NewsletterOperationalGate $gate,
    ) {}

    public function createPlaceholderDraft(NewsletterCampaign $campaign, ?AiProvider $provider = null): NewsletterCampaign
    {
        $this->gate->ensureAiDraftAllowed($campaign);

        $job = $this->ai->createPlaceholderJob('newsletter_body', [
            'campaign_uuid' => $campaign->uuid,
            'subject' => $campaign->subject,
        ], $provider, $campaign->id, NewsletterCampaign::class);

        $campaign->update([
            'status' => 'ai_draft',
            'ai_generation_id' => $job->id,
            'preheader' => $campaign->preheader ?: 'AI placeholder draft - review required.',
            'html_body' => $campaign->html_body ?: '<p>Placeholder AI newsletter draft. Human review is required before scheduling.</p>',
            'text_body' => $campaign->text_body ?: 'Placeholder AI newsletter draft. Human review is required before scheduling.',
        ]);

        return $campaign->fresh();
    }
}
