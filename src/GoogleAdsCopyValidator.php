<?php

declare(strict_types=1);

namespace AdPages\Tools;

final class GoogleAdsCopyValidator
{
    public const HEADLINE_LIMIT = 30;
    public const DESCRIPTION_LIMIT = 90;
    public const PATH_LIMIT = 15;
    public const MIN_HEADLINES = 3;
    public const MAX_HEADLINES = 15;
    public const MIN_DESCRIPTIONS = 2;
    public const MAX_DESCRIPTIONS = 4;

    /**
     * @param list<string> $headlines
     * @param list<string> $descriptions
     * @return array<string, mixed>
     */
    public static function validate(
        array $headlines,
        array $descriptions,
        ?string $path1 = null,
        ?string $path2 = null
    ): array {
        $headlineItems = self::validateList('headline', $headlines, self::HEADLINE_LIMIT);
        $descriptionItems = self::validateList('description', $descriptions, self::DESCRIPTION_LIMIT);
        $pathItems = self::validateList('path', array_values(array_filter([$path1, $path2], static fn ($value) => $value !== null)), self::PATH_LIMIT);

        $violations = [
            ...self::limitViolations($headlineItems),
            ...self::limitViolations($descriptionItems),
            ...self::limitViolations($pathItems),
            ...self::countViolations('headline', count($headlines), self::MIN_HEADLINES, self::MAX_HEADLINES),
            ...self::countViolations('description', count($descriptions), self::MIN_DESCRIPTIONS, self::MAX_DESCRIPTIONS),
        ];

        return [
            'valid' => count($violations) === 0,
            'limits' => [
                'headline' => self::HEADLINE_LIMIT,
                'description' => self::DESCRIPTION_LIMIT,
                'path' => self::PATH_LIMIT,
                'minHeadlines' => self::MIN_HEADLINES,
                'maxHeadlines' => self::MAX_HEADLINES,
                'minDescriptions' => self::MIN_DESCRIPTIONS,
                'maxDescriptions' => self::MAX_DESCRIPTIONS,
            ],
            'summary' => [
                'headlineCount' => count($headlines),
                'descriptionCount' => count($descriptions),
                'pathCount' => count($pathItems),
                'violationCount' => count($violations),
            ],
            'items' => [
                'headlines' => $headlineItems,
                'descriptions' => $descriptionItems,
                'paths' => $pathItems,
            ],
            'violations' => $violations,
        ];
    }

    /**
     * @param list<string> $values
     * @return list<array<string, mixed>>
     */
    private static function validateList(string $type, array $values, int $limit): array
    {
        $items = [];
        foreach (array_values($values) as $index => $value) {
            $text = trim((string) $value);
            $length = self::length($text);
            $items[] = [
                'type' => $type,
                'index' => $index + 1,
                'text' => $text,
                'characters' => $length,
                'limit' => $limit,
                'remaining' => $limit - $length,
                'valid' => $text !== '' && $length <= $limit,
            ];
        }

        return $items;
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    private static function limitViolations(array $items): array
    {
        $violations = [];
        foreach ($items as $item) {
            if ($item['text'] === '') {
                $violations[] = [
                    'type' => $item['type'],
                    'index' => $item['index'],
                    'code' => 'empty_text',
                    'message' => ucfirst((string) $item['type']) . ' ' . $item['index'] . ' is empty.',
                ];
                continue;
            }

            if ($item['characters'] > $item['limit']) {
                $violations[] = [
                    'type' => $item['type'],
                    'index' => $item['index'],
                    'code' => 'too_long',
                    'message' => ucfirst((string) $item['type']) . ' ' . $item['index'] . ' is ' . $item['characters'] . ' characters; limit is ' . $item['limit'] . '.',
                ];
            }
        }

        return $violations;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function countViolations(string $type, int $count, int $min, int $max): array
    {
        if ($count < $min) {
            return [[
                'type' => $type,
                'code' => 'too_few',
                'message' => ucfirst($type) . ' count is ' . $count . '; minimum is ' . $min . '.',
            ]];
        }

        if ($count > $max) {
            return [[
                'type' => $type,
                'code' => 'too_many',
                'message' => ucfirst($type) . ' count is ' . $count . '; maximum is ' . $max . '.',
            ]];
        }

        return [];
    }

    private static function length(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }

        return strlen($value);
    }
}
