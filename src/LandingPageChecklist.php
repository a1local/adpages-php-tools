<?php

declare(strict_types=1);

namespace AdPages\Tools;

final class LandingPageChecklist
{
    /**
     * @param array<string, mixed> $page
     * @return array<string, mixed>
     */
    public static function generate(array $page): array
    {
        $items = [
            self::item(
                'primary-cta',
                'Primary CTA is visible and specific',
                self::hasText($page['primaryCta'] ?? null),
                'high',
                'Use a clear action such as call, book, quote, or request help.'
            ),
            self::item(
                'click-to-call',
                'Phone number uses a click-to-call path',
                self::truthy($page['hasClickToCall'] ?? null) || self::looksLikePhone($page['phone'] ?? null),
                'high',
                'Add a tel: link for mobile visitors and call-focused ads.'
            ),
            self::item(
                'lead-form',
                'Lead form is present and testable',
                self::truthy($page['hasLeadForm'] ?? null),
                'high',
                'Include a short form with a clear success state and spam handling.'
            ),
            self::item(
                'localbusiness-schema',
                'LocalBusiness schema is planned or present',
                self::truthy($page['hasLocalBusinessSchema'] ?? null),
                'medium',
                'Add LocalBusiness JSON-LD with business name, URL, phone, and service area.'
            ),
            self::item(
                'trust-signals',
                'Trust signals support conversion',
                self::truthy($page['hasTrustSignals'] ?? null) || self::truthy($page['hasReviews'] ?? null),
                'medium',
                'Add relevant reviews, credentials, guarantees, or proof points.'
            ),
            self::item(
                'privacy-link',
                'Privacy link is available near forms or footer',
                self::truthy($page['hasPrivacyLink'] ?? null),
                'medium',
                'Link to a privacy policy before collecting enquiries.'
            ),
            self::item(
                'utm-plan',
                'Campaign URLs have a UTM naming plan',
                self::truthy($page['hasUtmLinks'] ?? null) || self::hasText($page['utmCampaign'] ?? null),
                'low',
                'Use consistent source, medium, campaign, and content names before launch.'
            ),
            self::item(
                'measurement-plan',
                'Conversion measurement has an owner',
                self::truthy($page['hasConversionTrackingPlan'] ?? null),
                'low',
                'Document what counts as a lead and how calls/forms will be checked.'
            ),
        ];

        $passed = count(array_filter($items, static fn (array $item): bool => $item['status'] === 'pass'));
        $failed = count($items) - $passed;

        return [
            'page' => [
                'url' => self::stringValue($page['url'] ?? null),
                'businessName' => self::stringValue($page['businessName'] ?? null),
            ],
            'summary' => [
                'score' => (int) round(($passed / count($items)) * 100),
                'passed' => $passed,
                'failed' => $failed,
                'total' => count($items),
                'highPriorityFailures' => count(array_filter($items, static fn (array $item): bool => $item['status'] === 'fail' && $item['priority'] === 'high')),
            ],
            'items' => $items,
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function item(string $id, string $label, bool $passed, string $priority, string $recommendation): array
    {
        return [
            'id' => $id,
            'label' => $label,
            'status' => $passed ? 'pass' : 'fail',
            'priority' => $priority,
            'recommendation' => $recommendation,
        ];
    }

    private static function truthy(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'yes';
    }

    private static function hasText(mixed $value): bool
    {
        return self::stringValue($value) !== null;
    }

    private static function looksLikePhone(mixed $value): bool
    {
        $phone = self::stringValue($value);
        if ($phone === null) {
            return false;
        }

        return preg_match('/^\+?[0-9 ()-]{7,}$/', $phone) === 1;
    }

    private static function stringValue(mixed $value): ?string
    {
        if ($value === null || $value === false) {
            return null;
        }

        $string = trim((string) $value);
        return $string === '' ? null : $string;
    }
}
