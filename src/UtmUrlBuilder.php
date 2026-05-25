<?php

declare(strict_types=1);

namespace AdPages\Tools;

use InvalidArgumentException;

final class UtmUrlBuilder
{
    private const UTM_KEY_MAP = [
        'source' => 'utm_source',
        'medium' => 'utm_medium',
        'campaign' => 'utm_campaign',
        'term' => 'utm_term',
        'content' => 'utm_content',
        'id' => 'utm_id',
        'source_platform' => 'utm_source_platform',
        'creative_format' => 'utm_creative_format',
        'marketing_tactic' => 'utm_marketing_tactic',
    ];

    /**
     * @param array<string, scalar|null> $utm
     * @param array<string, scalar|null> $extraParameters
     */
    public static function build(string $url, array $utm, array $extraParameters = []): string
    {
        $url = trim($url);
        if ($url === '') {
            throw new InvalidArgumentException('URL must not be empty.');
        }

        $parts = parse_url($url);
        if ($parts === false) {
            throw new InvalidArgumentException('URL could not be parsed.');
        }

        $query = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        foreach (self::normalizeUtm($utm) as $key => $value) {
            $query[$key] = $value;
        }

        foreach (self::normalizeParameters($extraParameters) as $key => $value) {
            $query[$key] = $value;
        }

        ksort($query);

        $rebuilt = self::rebuildWithoutQueryOrFragment($parts);
        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        if ($queryString !== '') {
            $rebuilt .= '?' . $queryString;
        }

        if (isset($parts['fragment']) && $parts['fragment'] !== '') {
            $rebuilt .= '#' . $parts['fragment'];
        }

        return $rebuilt;
    }

    /**
     * @param array<string, scalar|null> $utm
     * @return array<string, string>
     */
    private static function normalizeUtm(array $utm): array
    {
        $normalized = [];
        foreach ($utm as $key => $value) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            $targetKey = str_starts_with($key, 'utm_') ? $key : (self::UTM_KEY_MAP[$key] ?? null);
            if ($targetKey === null) {
                continue;
            }

            $stringValue = self::stringValue($value);
            if ($stringValue === null) {
                continue;
            }

            $normalized[$targetKey] = $stringValue;
        }

        return $normalized;
    }

    /**
     * @param array<string, scalar|null> $parameters
     * @return array<string, string>
     */
    private static function normalizeParameters(array $parameters): array
    {
        $normalized = [];
        foreach ($parameters as $key => $value) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            $stringValue = self::stringValue($value);
            if ($stringValue === null) {
                continue;
            }

            $normalized[$key] = $stringValue;
        }

        return $normalized;
    }

    private static function stringValue(mixed $value): ?string
    {
        if ($value === null || $value === false) {
            return null;
        }

        $stringValue = trim((string) $value);
        return $stringValue === '' ? null : $stringValue;
    }

    /**
     * @param array<string, mixed> $parts
     */
    private static function rebuildWithoutQueryOrFragment(array $parts): string
    {
        $url = '';
        if (isset($parts['scheme'])) {
            $url .= $parts['scheme'] . ':';
        }

        if (isset($parts['host'])) {
            $url .= '//';
            if (isset($parts['user'])) {
                $url .= $parts['user'];
                if (isset($parts['pass'])) {
                    $url .= ':' . $parts['pass'];
                }
                $url .= '@';
            }

            $url .= $parts['host'];
            if (isset($parts['port'])) {
                $url .= ':' . $parts['port'];
            }
        }

        $url .= $parts['path'] ?? '';

        return $url;
    }
}
