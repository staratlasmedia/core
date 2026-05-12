<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterImportBatch;
use App\Models\NewsletterImportRow;
use App\Models\NewsletterSubscriber;
use App\Services\Audience\AudiencePreferenceService;
use App\Services\Audience\AudienceTopicResolver;
use App\Services\Newsletter\Exceptions\NewsletterOperationBlocked;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsletterCsvImportService
{
    public function __construct(
        private readonly EmailAddressHasher $hasher,
        private readonly SuppressionService $suppression,
        private readonly NewsletterOperationalGate $gate,
        private readonly AudiencePreferenceService $preferences,
        private readonly AudienceTopicResolver $topics,
    ) {}

    /**
     * @param array<string, string> $mapping
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function dryRun(NewsletterImportBatch $batch, string $csvContents, array $mapping, array $options = []): array
    {
        $parsed = $this->parseCsv($csvContents);
        $rows = $parsed['rows'];
        $headers = $parsed['headers'];
        $seen = [];
        $report = [
            'total_rows' => count($rows),
            'valid_rows' => 0,
            'invalid_rows' => 0,
            'duplicate_rows' => 0,
            'existing_rows' => 0,
            'suppressed_rows' => 0,
            'skipped_rows' => 0,
            'headers' => $headers,
            'delimiter' => $parsed['delimiter'],
            'preview_rows' => array_slice($rows, 0, 5),
        ];

        $batch->rows()->delete();

        foreach ($rows as $index => $row) {
            $assoc = $this->combineRow($headers, $row);
            $email = trim((string) ($assoc[$mapping['email'] ?? 'email'] ?? ''));
            $errors = [];
            $status = 'valid';
            $hash = null;

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'invalid_email';
                $status = 'invalid';
                $report['invalid_rows']++;
            } else {
                $hash = $this->hasher->hash($email);

                if (isset($seen[$hash])) {
                    $status = 'duplicate';
                    $report['duplicate_rows']++;
                } elseif (NewsletterSubscriber::query()
                    ->where('site_id', $batch->site_id)
                    ->where('normalized_email_hash', $hash)
                    ->exists()) {
                    $status = 'existing';
                    $report['existing_rows']++;
                } elseif (($options['respect_suppression'] ?? true) && $this->suppression->isSuppressed($hash, $batch->site_id, $batch->newsletter_list_id)) {
                    $status = 'suppressed';
                    $report['suppressed_rows']++;
                } else {
                    $report['valid_rows']++;
                }

                $seen[$hash] = true;
            }

            NewsletterImportRow::query()->create([
                'newsletter_import_batch_id' => $batch->id,
                'row_number' => $index + 2,
                'raw_row_json' => $assoc,
                'normalized_email_hash' => $hash,
                'email_encrypted' => $email !== '' ? $email : null,
                'status' => $status,
                'validation_errors_json' => $errors,
                'metadata_json' => [
                    'topic_slugs' => $this->topicSlugsFromRow($assoc, $mapping),
                ],
            ]);
        }

        $batch->update([
            'status' => 'dry_run_completed',
            'mapping_json' => $mapping,
            'options_json' => $options,
            'dry_run_report_json' => $report,
            'total_rows' => $report['total_rows'],
            'valid_rows' => $report['valid_rows'],
            'invalid_rows' => $report['invalid_rows'],
            'duplicate_rows' => $report['duplicate_rows'],
            'suppressed_rows' => $report['suppressed_rows'],
            'dry_run_completed_at' => now(),
            'metadata_json' => array_merge($batch->metadata_json ?? [], [
                'detected_delimiter' => $parsed['delimiter'],
                'detected_headers' => $headers,
            ]),
        ]);

        return $report;
    }

    /**
     * @return array<string, int>
     */
    public function commit(NewsletterImportBatch $batch, ?int $committedBy = null): array
    {
        $this->gate->ensureImportCommitAllowed($batch);

        if ($batch->status === 'completed' || $batch->committed_at !== null) {
            throw NewsletterOperationBlocked::forReason('newsletter_import_already_committed');
        }

        if ($batch->dry_run_completed_at === null || $batch->rows()->count() === 0) {
            throw NewsletterOperationBlocked::forReason('newsletter_import_requires_dry_run');
        }

        $options = $batch->options_json ?? [];
        $mapping = $batch->mapping_json ?? [];
        $stats = ['imported_rows' => 0, 'updated_rows' => 0, 'skipped_rows' => 0, 'topic_preferences' => 0];

        DB::transaction(function () use ($batch, $options, $mapping, $committedBy, &$stats): void {
            $batch->update(['status' => 'importing', 'started_at' => now()]);

            $allowedTopics = $this->topics->forChannel($batch->site_id, $batch->push_group_id, 'newsletter', false)
                ->keyBy('slug');

            $batch->rows()->whereIn('status', ['valid', 'existing'])->orderBy('id')->each(function (NewsletterImportRow $row) use ($batch, $options, $mapping, $allowedTopics, &$stats): void {
                $email = (string) $row->email_encrypted;
                $hash = $row->normalized_email_hash ?: $this->hasher->hash($email);

                if (($options['respect_suppression'] ?? true) && $this->suppression->isSuppressed($hash, $batch->site_id, $batch->newsletter_list_id)) {
                    $row->update(['status' => 'suppressed']);
                    $stats['skipped_rows']++;

                    return;
                }

                $raw = $row->raw_row_json ?? [];
                $subscriber = NewsletterSubscriber::query()
                    ->where('site_id', $batch->site_id)
                    ->where('normalized_email_hash', $hash)
                    ->first();

                if ($subscriber instanceof NewsletterSubscriber && in_array($subscriber->status, ['suppressed', 'unsubscribed', 'complained', 'bounced'], true)) {
                    $row->update(['status' => 'skipped_suppressed', 'newsletter_subscriber_id' => $subscriber->id]);
                    $stats['skipped_rows']++;

                    return;
                }

                $attributes = [
                        'uuid' => (string) Str::uuid(),
                        'push_group_id' => $batch->push_group_id,
                        'bridge_installation_id' => $batch->bridge_installation_id,
                        'user_id' => null,
                        'email_hash' => $hash,
                        'email_encrypted' => $email,
                        'status' => $options['default_status'] ?? 'subscribed',
                        'language' => $raw[$mapping['language'] ?? 'language'] ?? null,
                        'source_url' => $raw[$mapping['source_url'] ?? 'source_url'] ?? null,
                        'source_url_hash' => ! empty($raw[$mapping['source_url'] ?? 'source_url'] ?? null) ? hash('sha256', (string) $raw[$mapping['source_url'] ?? 'source_url']) : null,
                        'source_title' => $raw[$mapping['source_title'] ?? 'source_title'] ?? null,
                        'source_type' => 'import',
                        'consented_at' => ! empty($raw[$mapping['consented_at'] ?? 'consented_at'] ?? null) ? $raw[$mapping['consented_at']] : now(),
                        'subscribed_at' => now(),
                ];

                if ($subscriber instanceof NewsletterSubscriber) {
                    unset($attributes['uuid']);
                    $subscriber->fill($attributes)->save();
                    $stats['updated_rows']++;
                } else {
                    $subscriber = NewsletterSubscriber::query()->create([
                        'site_id' => $batch->site_id,
                        'normalized_email_hash' => $hash,
                    ] + $attributes);
                    $stats['imported_rows']++;
                }

                if ($batch->newsletter_list_id !== null && ($options['attach_to_list'] ?? true)) {
                    $subscriber->lists()->syncWithoutDetaching([
                        $batch->newsletter_list_id => [
                            'status' => 'subscribed',
                            'subscribed_at' => now(),
                            'source_url' => $subscriber->source_url,
                        ],
                    ]);
                }

                $topicIds = [];
                foreach (($row->metadata_json['topic_slugs'] ?? []) as $slug) {
                    if ($allowedTopics->has($slug)) {
                        $topicIds[] = (int) $allowedTopics->get($slug)->id;
                    }
                }

                if ($topicIds !== []) {
                    $this->preferences->saveNewsletterPreferences($subscriber, $topicIds, 'import', $subscriber->source_url);
                    $stats['topic_preferences'] += count(array_unique($topicIds));
                }

                $row->update(['status' => 'imported', 'newsletter_subscriber_id' => $subscriber->id]);
            });

            $batch->update([
                'status' => 'completed',
                'imported_rows' => $stats['imported_rows'],
                'skipped_rows' => $stats['skipped_rows'],
                'finished_at' => now(),
                'committed_at' => now(),
                'committed_by' => $committedBy,
                'commit_report_json' => $stats,
            ]);
        });

        return $stats;
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, string|null>>, delimiter: string}
     */
    private function parseCsv(string $contents): array
    {
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents) ?? $contents;
        $firstLine = strtok($contents, "\n") ?: '';
        $counts = [
            ',' => substr_count($firstLine, ','),
            ';' => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
        ];
        arsort($counts);
        $delimiter = (string) array_key_first($counts);

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $contents);
        rewind($handle);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        $headers = array_map(fn ($header) => trim((string) $header), array_shift($rows) ?? []);

        return [
            'headers' => $headers,
            'rows' => $rows,
            'delimiter' => $delimiter === "\t" ? 'tab' : $delimiter,
        ];
    }

    /**
     * @param array<int, string|null> $headers
     * @param array<int, string|null> $row
     * @return array<string, string|null>
     */
    private function combineRow(array $headers, array $row): array
    {
        $assoc = [];
        foreach ($headers as $index => $header) {
            $assoc[trim((string) $header)] = $row[$index] ?? null;
        }

        return $assoc;
    }

    /**
     * @param array<string, string|null> $row
     * @param array<string, string> $mapping
     * @return array<int, string>
     */
    private function topicSlugsFromRow(array $row, array $mapping): array
    {
        $column = $mapping['topic_slugs'] ?? $mapping['topics'] ?? null;

        if ($column === null || empty($row[$column])) {
            return [];
        }

        return collect(preg_split('/[|,;]/', (string) $row[$column]) ?: [])
            ->map(fn (string $slug) => Str::slug(trim($slug)))
            ->filter()
            ->values()
            ->all();
    }
}
