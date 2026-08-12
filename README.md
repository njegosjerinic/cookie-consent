# Cookie Consenter

Cookie Consenter is a developer-focused WordPress consent manager. It stores a
visitor's category choices in `localStorage`, exposes a small browser API, and
keeps registered optional scripts inert until their category is allowed.

## Categories

- `necessary` is always available and should be loaded normally.
- `preferences`, `analytics`, and `marketing` require consent.

## Block a WordPress script handle

Enqueue the script normally, then assign its handle to a consent category:

```php
wp_enqueue_script(
    'example-analytics',
    'https://example.com/analytics.js',
    [],
    null,
    true
);

add_filter(
    'cookie_consenter_script_categories',
    static function (array $scripts): array {
        $scripts['example-analytics'] = 'analytics';

        return $scripts;
    }
);
```

Cookie Consenter changes that handle's frontend `<script>` tag into a
`type="text/plain"` placeholder. The browser does not load it until valid
analytics consent exists. Handles that are not registered pass through
unchanged. Invalid registered categories remain blocked.

Register a script's dependencies normally. Every optional dependency that can
track or store optional data must also be assigned an appropriate category.

## Block manually rendered scripts

External script:

```html
<script
  type="text/plain"
  data-cookie-consenter-script
  data-cookie-consenter-category="analytics"
  data-cookie-consenter-state="blocked"
  data-src="https://example.com/analytics.js"
></script>
```

Inline script:

```html
<script
  type="text/plain"
  data-cookie-consenter-script
  data-cookie-consenter-category="marketing"
  data-cookie-consenter-state="blocked"
>
  console.log("Runs only after marketing consent");
</script>
```

For an original non-default type, add it as
`data-cookie-consenter-type="module"`.

## Browser API

The API is available after the `cookie-consenter:ready` event:

```js
window.CookieConsenter.getConsent();
window.CookieConsenter.hasConsent("analytics");
window.CookieConsenter.openSettings();
window.CookieConsenter.refresh();
```

Call `refresh()` after dynamically inserting a controlled placeholder.

Listen for initialization and changes:

```js
document.addEventListener("cookie-consenter:ready", (event) => {
  console.log(event.detail.consent);
});

document.addEventListener("cookie-consenter:change", (event) => {
  console.log(event.detail.consent, event.detail.source);
});
```

## Withdrawal behavior

When a visitor disables a previously enabled category, Cookie Consenter saves
the new choice, dispatches the change event, and reloads the page. The reload
stops already-running optional JavaScript and the denied placeholders remain
inert on the next page load.

JavaScript cannot reliably delete `HttpOnly`, third-party, or incorrectly scoped
cookies. Service-specific cookie cleanup and server-side revocation remain the
responsibility of each integration.
