<?php

namespace App\Services\Newsletter;

use App\Models\EditorialContentItem;
use App\Models\EditorialContentSource;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDigestRecipe;
use App\Models\NewsletterDigestRun;
use App\Services\Editorial\EditorialContentIngestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class NewsletterDigestService
{
    public function __construct(
        private readonly EditorialContentIngestService $ingest,
        private readonly NewsletterOperationalGate $gate,
    ) {}

    public function createDraftRun(NewsletterDigestRecipe $recipe): NewsletterDigestRun
    {
        $this->gate->ensureDigestDraftAllowed($recipe);

        $run = NewsletterDigestRun::query()->create([
            'uuid' => (string) Str::uuid(),
            'newsletter_digest_recipe_id' => $recipe->id,
            'status' => 'collecting',
            'run_date' => now()->toDateString(),
            'started_at' => now(),
        ]);

        [$sourceIds, $sourceFetches] = $this->refreshRecipeSources($recipe);

        $items = EditorialContentItem::query()
            ->when($recipe->site_id, fn ($query) => $query->where('site_id', $recipe->site_id))
            ->when($recipe->push_group_id, fn ($query) => $query->where('push_group_id', $recipe->push_group_id))
            ->when($recipe->language, fn ($query) => $query->where('language', $recipe->language))
            ->when($recipe->section, fn ($query) => $query->where('section', $recipe->section))
            ->when($sourceIds !== [], fn ($query) => $query->whereIn('content_source_id', $sourceIds))
            ->orderByDesc('published_at')
            ->limit($recipe->max_items ?: 5)
            ->get();

        $campaign = NewsletterCampaign::query()->create([
            'uuid' => (string) Str::uuid(),
            'site_id' => $recipe->site_id,
            'push_group_id' => $recipe->push_group_id,
            'newsletter_list_id' => $recipe->newsletter_list_id,
            'name' => $recipe->name.' draft '.now()->format('Y-m-d'),
            'subject' => $recipe->name.' - '.now()->format('Y-m-d'),
            'status' => 'editorial_review',
            'template_id' => $recipe->template_id,
            'from_identity_id' => $recipe->sender_identity_id,
            'source_type' => 'digest',
            'html_body' => $items->map(fn ($item) => '<h2>'.e($item->title).'</h2><p>'.e($item->excerpt ?? '').'</p>')->implode("\n"),
            'text_body' => $items->pluck('title')->implode("\n"),
            'digest_recipe_id' => $recipe->id,
            'digest_run_id' => $run->id,
            'metadata_json' => [
                'auto_send' => false,
                'auto_schedule' => false,
                'require_editorial_approval' => true,
            ],
        ]);

        foreach ($items as $index => $item) {
            $run->getConnection()->table('newsletter_digest_run_items')->insert([
                'newsletter_digest_run_id' => $run->id,
                'editorial_content_item_id' => $item->id,
                'sort_order' => $index,
                'role' => $index === 0 ? 'hero' : 'list',
                'selection_reason' => 'latest_bounded_digest',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $run->update([
            'newsletter_campaign_id' => $campaign->id,
            'status' => 'editorial_review',
            'finished_at' => now(),
            'metadata_json' => array_merge($run->metadata_json ?? [], [
                'content_source_fetches' => $sourceFetches,
            ]),
        ]);

        return $run->fresh();
    }

    /**
     * Refreshes only the explicitly attached active sources just before a digest draft is generated.
     *
     * @return array{0: array<int>, 1: array<int, array<string, mixed>>}
     */
    private function refreshRecipeSources(NewsletterDigestRecipe $recipe): array
    {
        $sources = EditorialContentSource::query()
            ->join('newsletter_digest_recipe_sources', 'newsletter_digest_recipe_sources.editorial_content_source_id', '=', 'editorial_content_sources.id')
            ->where('newsletter_digest_recipe_sources.newsletter_digest_recipe_id', $recipe->id)
            ->where('newsletter_digest_recipe_sources.enabled', true)
            ->where('editorial_content_sources.status', 'active')
            ->orderBy('newsletter_digest_recipe_sources.sort_order')
            ->limit(10)
            ->select('editorial_content_sources.*')
            ->get();

        if ($sources->isEmpty()) {
            return [[], []];
        }

        $limit = max(1, min((int) ($recipe->max_items ?: config('core.newsletter.test_fetch_limit', 10)), (int) config('core.newsletter.test_fetch_limit', 10), 20));
        $fetches = [];

        foreach ($sources as $source) {
            try {
                $result = $this->ingest->testFetch($source, $limit, true);
                $fetches[] = [
                    'source_id' => $source->id,
                    'code' => $source->code,
                    'type' => $source->type,
                    'status' => $result['status'] ?? 'unknown',
                    'item_count' => count($result['items'] ?? []),
                ];
            } catch (Throwable $exception) {
                DB::table('editorial_content_sources')
                    ->where('id', $source->id)
                    ->update([
                        'last_error_at' => now(),
                        'last_error_message' => Str::limit($exception->getMessage(), 255),
                        'updated_at' => now(),
                    ]);

                $fetches[] = [
                    'source_id' => $source->id,
                    'code' => $source->code,
                    'type' => $source->type,
                    'status' => 'failed',
                    'item_count' => 0,
                    'error' => Str::limit($exception->getMessage(), 120),
                ];
            }
        }

        return [$sources->pluck('id')->map(fn ($id) => (int) $id)->all(), $fetches];
    }
}
