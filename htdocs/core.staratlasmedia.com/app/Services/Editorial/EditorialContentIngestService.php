<?php

namespace App\Services\Editorial;

use App\Models\EditorialContentSource;

class EditorialContentIngestService
{
    public function __construct(
        private readonly RssFeedFetcher $rss,
        private readonly WordPressContentFetcher $wordpress,
        private readonly \App\Services\Newsletter\NewsletterOperationalGate $gate,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function testFetch(EditorialContentSource $source, int $limit = 10, bool $persist = false): array
    {
        if ($persist) {
            $this->gate->ensureContentPersistAllowed($source->site_id, $source->push_group_id, $source->bridge_installation_id, $source->type);
        }

        return match ($source->type) {
            'rss', 'atom' => $this->rss->testFetch($source, $limit, $persist),
            'wordpress_rest' => $this->wordpress->testFetch($source, $limit, $persist),
            default => ['status' => 'unsupported', 'items' => []],
        };
    }
}
