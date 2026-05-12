<?php

namespace App\Http\Controllers\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\NewsletterDeliveryLog;
use App\Models\NewsletterEngagementEvent;
use App\Models\NewsletterToken;
use Illuminate\Http\Request;

class NewsletterTrackingController extends Controller
{
    public function open(string $token, Request $request)
    {
        $this->record($token, 'open', $request);

        return response(base64_decode('R0lGODlhAQABAPAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='), 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function click(string $token, Request $request)
    {
        $url = $this->record($token, 'click', $request, (string) $request->query('u', '/'));

        return redirect()->away($url ?: '/');
    }

    private function record(string $rawToken, string $type, Request $request, ?string $url = null): ?string
    {
        $token = NewsletterToken::query()
            ->where('token_hash', hash('sha256', $rawToken))
            ->where('type', $type)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (! $token instanceof NewsletterToken) {
            return null;
        }

        if ($type === 'click') {
            $allowedUrl = $token->metadata_json['url'] ?? $token->metadata_json['target_url'] ?? null;
            $url = is_string($allowedUrl) && $allowedUrl !== '' ? $allowedUrl : null;
        }

        NewsletterEngagementEvent::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'newsletter_campaign_id' => $token->newsletter_campaign_id,
            'newsletter_delivery_log_id' => $token->newsletter_delivery_log_id,
            'newsletter_subscriber_id' => $token->newsletter_subscriber_id,
            'event_type' => $type,
            'url' => $url,
            'url_hash' => $url ? hash('sha256', $url) : null,
            'ip_hash' => $request->ip() ? hash('sha256', $request->ip()) : null,
            'user_agent_hash' => $request->userAgent() ? hash('sha256', $request->userAgent()) : null,
            'occurred_at' => now(),
        ]);

        if ($token->newsletter_delivery_log_id !== null) {
            $delivery = NewsletterDeliveryLog::query()->find($token->newsletter_delivery_log_id);
            if ($delivery instanceof NewsletterDeliveryLog) {
                if ($type === 'open') {
                    $delivery->update([
                        'opened_at' => now(),
                        'first_opened_at' => $delivery->first_opened_at ?: now(),
                        'last_opened_at' => now(),
                        'open_count' => $delivery->open_count + 1,
                    ]);
                } elseif ($type === 'click') {
                    $delivery->update([
                        'clicked_at' => now(),
                        'first_clicked_at' => $delivery->first_clicked_at ?: now(),
                        'last_clicked_at' => now(),
                        'click_count' => $delivery->click_count + 1,
                    ]);
                }
            }
        }

        return $url;
    }
}
