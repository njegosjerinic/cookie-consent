<?php

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

$test_categories = [];

function add_filter(string $hook, callable $callback, int $priority, int $accepted_args): void
{
}

function apply_filters(string $hook, mixed $value): mixed
{
    global $test_categories;

    return $hook === 'cookie_consenter_script_categories' ? $test_categories : $value;
}

function is_admin(): bool
{
    return false;
}

function sanitize_key(string $value): string
{
    return strtolower((string) preg_replace('/[^a-z0-9_\-]/', '', $value));
}

function esc_url(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function esc_attr(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

require_once dirname(__DIR__) . '/includes/class-consent.php';

function assert_contains(string $needle, string $haystack, string $message): void
{
    if (!str_contains($haystack, $needle)) {
        throw new RuntimeException($message . "\nActual: " . $haystack);
    }
}

$consent = new \CookieConsenter\Consent();
$normal_tag = '<script src="https://example.com/app.js" id="app-js"></script>';

if ($consent->filter_script_tag($normal_tag, 'app', 'https://example.com/app.js') !== $normal_tag) {
    throw new RuntimeException('Unregistered scripts must pass through unchanged.');
}

$test_categories = ['analytics' => 'analytics'];
$blocked_tag = $consent->filter_script_tag(
    '<script src="https://example.com/analytics.js" id="analytics-js" defer></script>',
    'analytics',
    'https://example.com/analytics.js'
);

assert_contains('type="text/plain"', $blocked_tag, 'Registered scripts must be inert.');
assert_contains('data-src="https://example.com/analytics.js"', $blocked_tag, 'The source must move to data-src.');
assert_contains('data-cookie-consenter-category="analytics"', $blocked_tag, 'The category must be present.');
assert_contains('id="analytics-js"', $blocked_tag, 'Existing attributes must be preserved.');
assert_contains(' defer', $blocked_tag, 'Loading strategy attributes must be preserved.');

$test_categories = ['broken' => 'not-a-category'];
$invalid_tag = $consent->filter_script_tag(
    '<script src="https://example.com/broken.js"></script>',
    'broken',
    'https://example.com/broken.js'
);

assert_contains('type="text/plain"', $invalid_tag, 'Invalid registered categories must fail closed.');
assert_contains('data-cookie-consenter-category="invalid"', $invalid_tag, 'Invalid categories must be identifiable.');

echo "Consent PHP tests passed.\n";
