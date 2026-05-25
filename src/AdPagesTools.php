<?php

declare(strict_types=1);

namespace AdPages\Tools;

final class AdPagesTools
{
    /**
     * @param array<string, scalar|null> $utm
     * @param array<string, scalar|null> $extraParameters
     */
    public static function buildUtmUrl(string $url, array $utm, array $extraParameters = []): string
    {
        return UtmUrlBuilder::build($url, $utm, $extraParameters);
    }

    /**
     * @param list<string> $headlines
     * @param list<string> $descriptions
     * @return array<string, mixed>
     */
    public static function validateGoogleAdsCopy(
        array $headlines,
        array $descriptions,
        ?string $path1 = null,
        ?string $path2 = null
    ): array {
        return GoogleAdsCopyValidator::validate($headlines, $descriptions, $path1, $path2);
    }

    /**
     * @param array<string, mixed> $business
     */
    public static function localBusinessJsonLd(array $business, bool $pretty = true): string
    {
        return LocalBusinessJsonLd::generate($business, $pretty);
    }

    /**
     * @param array<string, mixed> $page
     * @return array<string, mixed>
     */
    public static function landingPageChecklist(array $page): array
    {
        return LandingPageChecklist::generate($page);
    }
}
