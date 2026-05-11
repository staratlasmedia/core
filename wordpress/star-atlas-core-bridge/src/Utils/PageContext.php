<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Utils;

final class PageContext
{
    public function __construct(
        private readonly Options $options,
        private readonly UrlResolver $resolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function current(): array
    {
        $config = $this->options->config();

        return [
            'source_url' => $this->sourceUrl(),
            'source_title' => $this->sourceTitle(),
            'language' => $config['language'] ?? get_bloginfo('language'),
            'section' => $config['section'] ?? '',
            'wp_terms_json' => $this->terms(),
            'referrer' => $this->referrer(),
            'utm_json' => $this->utm(),
        ];
    }

    private function sourceUrl(): string
    {
        $path = $this->resolver->requestPath();

        return esc_url_raw(home_url($path));
    }

    private function sourceTitle(): ?string
    {
        if (is_singular()) {
            $title = get_the_title();

            return is_string($title) && $title !== '' ? wp_strip_all_tags($title) : null;
        }

        $title = wp_get_document_title();

        return is_string($title) && $title !== '' ? wp_strip_all_tags($title) : null;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function terms(): ?array
    {
        if (! is_singular()) {
            return null;
        }

        $objectId = get_queried_object_id();
        $type = get_post_type($objectId);

        if (! is_string($type) || $type === '') {
            return null;
        }

        $taxonomies = get_object_taxonomies($type, 'names');

        if (! is_array($taxonomies) || $taxonomies === []) {
            return null;
        }

        $terms = wp_get_object_terms($objectId, $taxonomies);

        if (is_wp_error($terms) || ! is_array($terms)) {
            return null;
        }

        return array_values(array_map(static fn ($term): array => [
            'taxonomy' => $term->taxonomy,
            'slug' => $term->slug,
            'name' => $term->name,
        ], $terms));
    }

    private function referrer(): ?string
    {
        $referrer = isset($_SERVER['HTTP_REFERER']) ? (string) wp_unslash($_SERVER['HTTP_REFERER']) : '';

        return $referrer === '' ? null : esc_url_raw($referrer);
    }

    /**
     * @return array<string, string>|null
     */
    private function utm(): ?array
    {
        $utm = [];

        foreach ($_GET as $key => $value) {
            if (! is_string($key) || ! str_starts_with($key, 'utm_')) {
                continue;
            }

            $utm[sanitize_key($key)] = sanitize_text_field(is_scalar($value) ? (string) wp_unslash($value) : '');
        }

        return $utm === [] ? null : $utm;
    }
}
