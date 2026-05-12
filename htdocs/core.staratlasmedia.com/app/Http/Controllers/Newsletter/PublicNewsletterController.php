<?php

namespace App\Http\Controllers\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Models\NewsletterToken;
use App\Models\Site;
use App\Services\Audience\AudiencePreferenceService;
use App\Services\Audience\AudienceTopicResolver;
use App\Services\Newsletter\NewsletterSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicNewsletterController extends Controller
{
    public function subscribe(Request $request, NewsletterSubscriptionService $service): JsonResponse
    {
        $data = $request->validate([
            'site_code' => ['required', 'string'],
            'push_group' => ['nullable', 'string'],
            'email' => ['required', 'email'],
            'list_code' => ['nullable', 'string'],
            'language' => ['nullable', 'string', 'max:16'],
            'source_url' => ['nullable', 'url'],
            'consent_version' => ['nullable', 'string'],
            'topic_ids' => ['nullable', 'array'],
            'topic_ids.*' => ['integer'],
        ]);

        $data['ip'] = $request->ip();
        $data['user_agent'] = $request->userAgent();

        return response()->json($service->subscribe($data));
    }

    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string']]);
        $token = NewsletterToken::query()
            ->where('token_hash', hash('sha256', $data['token']))
            ->where('type', 'confirm')
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (! $token instanceof NewsletterToken) {
            return response()->json(['status' => 'invalid_token'], 404);
        }

        $subscriber = NewsletterSubscriber::query()->find($token->newsletter_subscriber_id);
        $subscriber?->update(['status' => 'subscribed', 'confirmed_at' => now(), 'subscribed_at' => now()]);
        $token->update(['status' => 'consumed', 'consumed_at' => now()]);

        return response()->json(['status' => 'confirmed']);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string']]);
        $token = NewsletterToken::query()
            ->where('token_hash', hash('sha256', $data['token']))
            ->where('type', 'unsubscribe')
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
        $subscriber = $token ? NewsletterSubscriber::query()->find($token->newsletter_subscriber_id) : null;

        if (! $subscriber instanceof NewsletterSubscriber) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $subscriber->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
        foreach ($subscriber->lists()->pluck('newsletter_lists.id') as $listId) {
            $subscriber->lists()->updateExistingPivot($listId, ['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
        }
        $token?->update(['status' => 'consumed', 'consumed_at' => now()]);

        return response()->json(['status' => 'unsubscribed']);
    }

    public function preferences(Request $request, AudienceTopicResolver $topics, AudiencePreferenceService $preferences): JsonResponse
    {
        $data = $request->validate([
            'site_code' => ['required', 'string'],
            'subscriber_uuid' => ['nullable', 'string'],
            'topic_ids' => ['nullable', 'array'],
            'topic_ids.*' => ['integer'],
            'source_url' => ['nullable', 'url'],
        ]);
        $site = Site::query()->where('code', $data['site_code'])->firstOrFail();
        $available = $topics->forChannel($site->id, $site->push_group_id, 'newsletter', true);

        if ($request->isMethod('post') && ! empty($data['subscriber_uuid'])) {
            $subscriber = NewsletterSubscriber::query()
                ->where('site_id', $site->id)
                ->where('uuid', $data['subscriber_uuid'])
                ->first();

            if ($subscriber instanceof NewsletterSubscriber) {
                $allowed = $available->pluck('id')->map(fn ($id) => (int) $id)->all();
                $topicIds = array_values(array_intersect(array_map('intval', $data['topic_ids'] ?? []), $allowed));
                $preferences->saveNewsletterPreferences($subscriber, $topicIds, 'preferences_api', $data['source_url'] ?? null);
            }
        }

        return response()->json([
            'status' => 'ok',
            'topics' => $available->map(fn ($topic) => [
                'id' => $topic->id,
                'slug' => $topic->slug,
                'label' => $topic->label,
                'type' => $topic->type,
            ])->values(),
        ]);
    }
}
