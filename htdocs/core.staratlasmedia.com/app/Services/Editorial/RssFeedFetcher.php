<?php

namespace App\Services\Editorial;

use App\Models\EditorialContentItem;
use App\Models\EditorialContentSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

class RssFeedFetcher
{
    /**
     * @return array<string, mixed>
     */
    public function testFetch(EditorialContentSource $source, int $limit = 10, bool $persist = false): array
    {
        $limit = max(1, min($limit, 20));
        $response = Http::timeout(10)
            ->withHeaders(array_filter([
                'If-None-Match' => $source->etag,
                'If-Modified-Since' => $source->last_modified_header,
            ]))
            ->get((string) $source->url);

        if ($response->status() === 304) {
            return ['status' => 'not_modified', 'items' => []];
        }

        if (! $response->successful()) {
            if ($persist) {
                $source->update(['last_error_at' => now(), 'last_error_message' => 'RSS fetch failed: '.$response->status()]);
            }

            return ['status' => 'failed', 'http_status' => $response->status(), 'items' => []];
        }

        try {
            $xml = new SimpleXMLElement($response->body());
        } catch (Throwable $exception) {
            if ($persist) {
                $source->update(['last_error_at' => now(), 'last_error_message' => Str::limit($exception->getMessage(), 255)]);
            }

            return ['status' => 'failed', 'reason' => 'invalid_xml', 'items' => []];
        }

        $items = [];
        $nodes = $xml->channel->item ?? $xml->entry ?? [];

        foreach ($nodes as $node) {
            if (count($items) >= $limit) {
                break;
            }

            $link = (string) ($node->link['href'] ?? $node->link ?? '');
            $title = trim((string) ($node->title ?? ''));

            if ($link === '' || $title === '') {
                continue;
            }

            $payload = [
                'source_type' => $source->type,
                'source_id' => (string) ($node->guid ?? $node->id ?? $link),
                'source_url' => $link,
                'title' => $title,
                'excerpt' => trim(strip_tags((string) ($node->description ?? $node->summary ?? ''))) ?: null,
                'published_at' => $this->dateOrNull((string) ($node->pubDate ?? $node->published ?? '')),
                'raw_payload_json' => json_decode(json_encode($node), true),
            ];

            $items[] = $persist ? $this->storeItem($source, $payload) : [
                'uuid' => null,
                'title' => $payload['title'],
                'source_url' => $payload['source_url'],
                'source_id' => $payload['source_id'],
            ];
        }

        if ($persist) {
            $source->update([
                'last_polled_at' => now(),
                'last_successful_poll_at' => now(),
                'last_error_at' => null,
                'last_error_message' => null,
                'etag' => $response->header('ETag'),
                'last_modified_header' => $response->header('Last-Modified'),
            ]);
        }

        return ['status' => 'ok', 'persisted' => $persist, 'items' => $items];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function storeItem(EditorialContentSource $source, array $payload): array
    {
        $urlHash = hash('sha256', $payload['source_url']);
        $item = EditorialContentItem::query()->updateOrCreate(
            ['site_id' => $source->site_id, 'source_url_hash' => $urlHash],
            [
                'uuid' => (string) Str::uuid(),
                'content_source_id' => $source->id,
                'push_group_id' => $source->push_group_id,
                'source_type' => $payload['source_type'],
                'source_id' => $payload['source_id'],
                'source_url' => $payload['source_url'],
                'source_url_hash' => $urlHash,
                'title' => $payload['title'],
                'excerpt' => $payload['excerpt'],
                'published_at' => $payload['published_at'],
                'language' => $source->language,
                'section' => $source->section,
                'raw_payload_json' => $payload['raw_payload_json'],
            ],
        );

        return ['uuid' => $item->uuid, 'title' => $item->title, 'source_url' => $item->source_url];
    }

    private function dateOrNull(string $value): ?string
    {
        return $value !== '' ? date('Y-m-d H:i:s', strtotime($value)) : null;
    }
}
