<?php

namespace App\Services\Editorial;

use App\Models\EditorialContentItem;
use App\Models\EditorialContentSource;
use App\Models\EditorialContentSourcePostType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WordPressContentFetcher
{
    /**
     * @return array<string, mixed>
     */
    public function testFetch(EditorialContentSource $source, int $limit = 10, bool $persist = false): array
    {
        $limit = max(1, min($limit, 20));
        $postTypes = EditorialContentSourcePostType::query()
            ->where('editorial_content_source_id', $source->id)
            ->where('enabled', true)
            ->pluck('post_type')
            ->all() ?: ['posts'];

        $items = [];

        foreach ($postTypes as $postType) {
            if (count($items) >= $limit) {
                break;
            }

            $endpoint = rtrim((string) $source->api_base_url, '/').'/wp-json/wp/v2/'.trim($postType, '/');
            $response = Http::timeout(10)
                ->withHeaders(array_filter([
                    'If-None-Match' => $source->etag,
                    'If-Modified-Since' => $source->last_modified_header,
                ]))
                ->get($endpoint, [
                    'per_page' => min(10, $limit - count($items)),
                    'page' => 1,
                    '_embed' => 1,
                    'orderby' => 'date',
                    'order' => 'desc',
                ]);

            if ($response->status() === 304) {
                continue;
            }

            if (! $response->successful()) {
                if ($persist) {
                    $source->update(['last_error_at' => now(), 'last_error_message' => 'WordPress fetch failed: '.$response->status()]);
                }

                return ['status' => 'failed', 'http_status' => $response->status(), 'items' => $items];
            }

            if ($persist) {
                $source->update([
                    'etag' => $response->header('ETag'),
                    'last_modified_header' => $response->header('Last-Modified'),
                ]);
            }

            foreach ($response->json() ?? [] as $post) {
                $items[] = $persist ? $this->storePost($source, $postType, $post) : $this->previewPost($postType, $post);
            }
        }

        if ($persist) {
            $source->update(['last_polled_at' => now(), 'last_successful_poll_at' => now(), 'last_error_at' => null, 'last_error_message' => null]);
        }

        return ['status' => 'ok', 'persisted' => $persist, 'items' => array_slice($items, 0, $limit)];
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function storePost(EditorialContentSource $source, string $postType, array $post): array
    {
        $url = (string) ($post['link'] ?? '');
        $urlHash = hash('sha256', $url);
        $title = trim(strip_tags((string) ($post['title']['rendered'] ?? 'Untitled')));
        $excerpt = trim(strip_tags((string) ($post['excerpt']['rendered'] ?? ''))) ?: null;
        $image = $post['_embedded']['wp:featuredmedia'][0]['source_url'] ?? null;

        $item = EditorialContentItem::query()->updateOrCreate(
            ['site_id' => $source->site_id, 'source_url_hash' => $urlHash],
            [
                'uuid' => (string) Str::uuid(),
                'content_source_id' => $source->id,
                'push_group_id' => $source->push_group_id,
                'source_type' => 'wordpress_rest',
                'source_id' => (string) ($post['id'] ?? $url),
                'source_url' => $url,
                'source_url_hash' => $urlHash,
                'title' => $title,
                'excerpt' => $excerpt,
                'image_url' => $image,
                'published_at' => $post['date_gmt'] ?? $post['date'] ?? null,
                'modified_at' => $post['modified_gmt'] ?? $post['modified'] ?? null,
                'language' => $source->language,
                'section' => $source->section,
                'post_type' => $postType,
                'wp_post_id' => $post['id'] ?? null,
                'wp_terms_json' => [
                    'categories' => $post['categories'] ?? [],
                    'tags' => $post['tags'] ?? [],
                ],
                'raw_payload_json' => $post,
            ],
        );

        return ['uuid' => $item->uuid, 'title' => $item->title, 'source_url' => $item->source_url];
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function previewPost(string $postType, array $post): array
    {
        return [
            'uuid' => null,
            'title' => trim(strip_tags((string) ($post['title']['rendered'] ?? 'Untitled'))),
            'source_url' => (string) ($post['link'] ?? ''),
            'source_id' => (string) ($post['id'] ?? ''),
            'post_type' => $postType,
        ];
    }
}
