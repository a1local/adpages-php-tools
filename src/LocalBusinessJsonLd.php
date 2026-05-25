<?php

declare(strict_types=1);

namespace AdPages\Tools;

use InvalidArgumentException;

final class LocalBusinessJsonLd
{
    /**
     * @param array<string, mixed> $business
     */
    public static function generate(array $business, bool $pretty = true): string
    {
        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode(self::toArray($business), $flags) ?: '{}';
    }

    /**
     * @param array<string, mixed> $business
     * @return array<string, mixed>
     */
    public static function toArray(array $business): array
    {
        $name = self::stringValue($business['name'] ?? null);
        $url = self::stringValue($business['url'] ?? null);

        if ($name === null) {
            throw new InvalidArgumentException('Business name is required.');
        }

        if ($url === null) {
            throw new InvalidArgumentException('Business URL is required.');
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => self::stringValue($business['type'] ?? null) ?? 'LocalBusiness',
            'name' => $name,
            'url' => $url,
            'telephone' => self::stringValue($business['telephone'] ?? $business['phone'] ?? null),
            'image' => self::stringValue($business['image'] ?? null),
            'priceRange' => self::stringValue($business['priceRange'] ?? null),
            'description' => self::stringValue($business['description'] ?? null),
            'address' => self::address($business['address'] ?? []),
            'geo' => self::geo($business['geo'] ?? []),
            'openingHours' => self::stringList($business['openingHours'] ?? []),
            'sameAs' => self::stringList($business['sameAs'] ?? []),
            'areaServed' => self::areaServed($business['areaServed'] ?? []),
        ];

        return self::removeEmpty($schema);
    }

    /**
     * @param mixed $address
     * @return array<string, string>|null
     */
    private static function address(mixed $address): ?array
    {
        if (!is_array($address)) {
            return null;
        }

        return self::removeEmpty([
            '@type' => 'PostalAddress',
            'streetAddress' => self::stringValue($address['streetAddress'] ?? null),
            'addressLocality' => self::stringValue($address['addressLocality'] ?? $address['locality'] ?? null),
            'addressRegion' => self::stringValue($address['addressRegion'] ?? $address['region'] ?? null),
            'postalCode' => self::stringValue($address['postalCode'] ?? null),
            'addressCountry' => self::stringValue($address['addressCountry'] ?? $address['country'] ?? null),
        ]);
    }

    /**
     * @param mixed $geo
     * @return array<string, mixed>|null
     */
    private static function geo(mixed $geo): ?array
    {
        if (!is_array($geo)) {
            return null;
        }

        return self::removeEmpty([
            '@type' => 'GeoCoordinates',
            'latitude' => self::stringValue($geo['latitude'] ?? null),
            'longitude' => self::stringValue($geo['longitude'] ?? null),
        ]);
    }

    /**
     * @param mixed $areas
     * @return list<array<string, string>>
     */
    private static function areaServed(mixed $areas): array
    {
        $names = self::stringList($areas);
        return array_map(
            static fn (string $name): array => ['@type' => 'Place', 'name' => $name],
            $names
        );
    }

    /**
     * @param mixed $value
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];
        $strings = [];
        foreach ($values as $item) {
            $string = self::stringValue($item);
            if ($string !== null) {
                $strings[] = $string;
            }
        }

        return array_values(array_unique($strings));
    }

    private static function stringValue(mixed $value): ?string
    {
        if ($value === null || $value === false) {
            return null;
        }

        $string = trim((string) $value);
        return $string === '' ? null : $string;
    }

    /**
     * @param array<string, mixed> $value
     * @return array<string, mixed>
     */
    private static function removeEmpty(array $value): array
    {
        $result = [];
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $item = self::removeEmpty($item);
            }

            if ($item === null || $item === '' || $item === []) {
                continue;
            }

            $result[$key] = $item;
        }

        return $result;
    }
}
