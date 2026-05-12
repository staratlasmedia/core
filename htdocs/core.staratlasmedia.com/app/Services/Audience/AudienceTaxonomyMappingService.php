<?php

namespace App\Services\Audience;

use App\Models\AudienceTopicTaxonomyMapping;

class AudienceTaxonomyMappingService
{
    public function findMappedTopicId(?int $siteId, ?int $bridgeInstallationId, ?string $taxonomy, ?int $termId, ?string $termSlug, ?string $postType): ?int
    {
        return AudienceTopicTaxonomyMapping::query()
            ->where('status', 'active')
            ->when($siteId, fn ($query) => $query->where(function ($scope) use ($siteId): void {
                $scope->whereNull('site_id')->orWhere('site_id', $siteId);
            }))
            ->when($bridgeInstallationId, fn ($query) => $query->where(function ($scope) use ($bridgeInstallationId): void {
                $scope->whereNull('bridge_installation_id')->orWhere('bridge_installation_id', $bridgeInstallationId);
            }))
            ->when($taxonomy, fn ($query) => $query->where('wp_taxonomy', $taxonomy))
            ->when($termId, fn ($query) => $query->where('wp_term_id', $termId))
            ->when($termSlug, fn ($query) => $query->orWhere('wp_term_slug', $termSlug))
            ->when($postType, fn ($query) => $query->orWhere('wp_post_type', $postType))
            ->value('audience_topic_id');
    }
}
