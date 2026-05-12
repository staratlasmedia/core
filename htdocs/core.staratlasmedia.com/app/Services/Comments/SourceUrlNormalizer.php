<?php

namespace App\Services\Comments;

class SourceUrlNormalizer
{
    public function normalize(string $url): string
    {
        $url = trim($url);
        $parts = parse_url($url);

        if (! is_array($parts) || empty($parts['host'])) {
            return $url;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $host = strtolower((string) $parts['host']);
        $path = '/'.ltrim((string) ($parts['path'] ?? '/'), '/');
        $path = $path === '/' ? '/' : rtrim($path, '/').'/';
        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        $portPart = $port !== null && ! in_array([$scheme, $port], [['http', 80], ['https', 443]], true)
            ? ':'.$port
            : '';

        $query = '';

        if (! empty($parts['query'])) {
            parse_str((string) $parts['query'], $params);
            ksort($params);
            $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
            $query = $queryString !== '' ? '?'.$queryString : '';
        }

        return $scheme.'://'.$host.$portPart.$path.$query;
    }

    public function hash(string $url): string
    {
        return hash('sha256', $this->normalize($url));
    }
}
