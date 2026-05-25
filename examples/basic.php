<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/UtmUrlBuilder.php';
require_once __DIR__ . '/../src/GoogleAdsCopyValidator.php';
require_once __DIR__ . '/../src/LocalBusinessJsonLd.php';
require_once __DIR__ . '/../src/LandingPageChecklist.php';
require_once __DIR__ . '/../src/AdPagesTools.php';

use AdPages\Tools\AdPagesTools;

$utmUrl = AdPagesTools::buildUtmUrl(
    'https://example.com/plumber-perth?existing=1#quote',
    [
        'source' => 'google',
        'medium' => 'cpc',
        'campaign' => 'emergency-plumber',
        'content' => 'rsa-a',
    ],
    [
        'lead_type' => 'phone',
    ]
);

$adCopy = AdPagesTools::validateGoogleAdsCopy(
    [
        'Emergency Plumber Perth',
        'Same-Day Hot Water Repairs',
        'Book a Local Plumber',
    ],
    [
        'Licensed Perth plumbers for urgent repairs, leaks, blocked drains and hot water.',
        'Call now for fast help from a local team with clear pricing.',
    ],
    'plumber',
    'perth'
);

$schema = json_decode(AdPagesTools::localBusinessJsonLd([
    'name' => 'Example Plumbing',
    'url' => 'https://example.com',
    'telephone' => '+61 8 5550 1000',
    'address' => [
        'streetAddress' => '10 Hay Street',
        'addressLocality' => 'Perth',
        'addressRegion' => 'WA',
        'postalCode' => '6000',
        'addressCountry' => 'AU',
    ],
    'areaServed' => ['Perth', 'Fremantle'],
]), true);

$checklist = AdPagesTools::landingPageChecklist([
    'url' => 'https://example.com/plumber-perth',
    'businessName' => 'Example Plumbing',
    'primaryCta' => 'Call now',
    'phone' => '+61 8 5550 1000',
    'hasClickToCall' => true,
    'hasLeadForm' => true,
    'hasLocalBusinessSchema' => true,
    'hasTrustSignals' => true,
    'hasPrivacyLink' => true,
    'hasUtmLinks' => true,
    'hasConversionTrackingPlan' => false,
]);

echo json_encode([
    'utmUrl' => $utmUrl,
    'adCopyValid' => $adCopy['valid'],
    'adCopyViolations' => $adCopy['summary']['violationCount'],
    'schemaType' => $schema['@type'] ?? null,
    'schemaAreas' => array_map(static fn (array $area): string => $area['name'], $schema['areaServed'] ?? []),
    'checklistScore' => $checklist['summary']['score'],
    'highPriorityFailures' => $checklist['summary']['highPriorityFailures'],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
