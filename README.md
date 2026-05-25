# AdPages Tools for PHP

Dependency-free PHP utilities for small landing-page and local-service marketing workflows.

This package is intended as a practical Composer/Packagist surface for AdPages later. It is useful on its own, close to local-service landing-page work, and small enough to maintain without a framework.

## What it includes

- UTM URL builder for tagged campaign links.
- Google Ads responsive search ad copy length validator.
- LocalBusiness JSON-LD generator for local landing pages.
- Landing-page checklist generator for lightweight QA handoffs.

The Google Ads copy checker uses common responsive search ad limits: headlines up to 30 characters, descriptions up to 90 characters, and display URL paths up to 15 characters each. Recheck the official Google Ads docs before publishing a public release.

## Install

From this folder during development, use Composer path loading or require the source files directly in examples.

After a future Packagist release:

```bash
composer require adpages/tools
```

No Composer install is required for the local checks in this scaffold.

## PHP Usage

Build a tagged campaign URL:

```php
use AdPages\Tools\UtmUrlBuilder;

$url = UtmUrlBuilder::build('https://example.com/plumber-perth', [
    'source' => 'google',
    'medium' => 'cpc',
    'campaign' => 'emergency-plumber',
    'content' => 'rsa-a',
]);
```

Validate Google Ads copy:

```php
use AdPages\Tools\GoogleAdsCopyValidator;

$report = GoogleAdsCopyValidator::validate(
    headlines: [
        'Emergency Plumber Perth',
        'Same-Day Hot Water Repairs',
        'Book a Local Plumber',
    ],
    descriptions: [
        'Licensed Perth plumbers for urgent repairs, leaks, blocked drains and hot water.',
        'Call now for fast help from a local team with clear pricing.',
    ],
    path1: 'plumber',
    path2: 'perth'
);
```

Create LocalBusiness JSON-LD:

```php
use AdPages\Tools\LocalBusinessJsonLd;

$jsonLd = LocalBusinessJsonLd::generate([
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
]);
```

Generate a landing-page QA checklist:

```php
use AdPages\Tools\LandingPageChecklist;

$checklist = LandingPageChecklist::generate([
    'url' => 'https://example.com/plumber-perth',
    'businessName' => 'Example Plumbing',
    'primaryCta' => 'Call now',
    'phone' => '+61 8 5550 1000',
    'hasClickToCall' => true,
    'hasLeadForm' => true,
    'hasLocalBusinessSchema' => true,
    'hasPrivacyLink' => true,
]);
```

See [examples/basic.php](examples/basic.php) for a dependency-free script that can be run without Composer.

## Local Checks

Run the package-local checks:

```bash
npm --prefix packages/php/adpages-tools run check
npm --prefix packages/php/adpages-tools run smoke
```

The checks:

- Parse `composer.json` and `package.json`.
- Confirm the expected PHP source, docs, examples, and scripts exist.
- Confirm no runtime dependencies beyond PHP are declared.
- Scan local source for obvious network-call and credential placeholder patterns.
- Run `php -l` and execute the example script when PHP is available.
- Skip PHP execution cleanly when PHP is not installed.

The package does not make network calls, does not collect data, does not use cookies, and does not send anything to a hosted backend.

## Publishing Position

This should be published as a small utility package for:

- Composer/Packagist discovery around UTM, JSON-LD, and landing-page QA workflows.
- PHP examples for WordPress-adjacent and local-service developers.
- Developer docs and resource pages that point back to broader AdPages QA tools.

It makes no marketplace submission claims and should not be published until the blockers are cleared.

## Publish Blockers

- Confirm Packagist vendor/package ownership for `adpages/tools`.
- Finalize public license text and support URLs.
- Add repository, changelog, issue tracker, and security policy URLs.
- Decide whether to include a Composer `autoload.files` helper layer or keep class-only usage.
- Add automated tests after the public API is frozen.
- Build and inspect a release archive from a clean checkout.
- Recheck Google Ads copy limits against official documentation before release.
