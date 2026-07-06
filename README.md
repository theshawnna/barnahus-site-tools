# barnahus-site-tools
Tweaks to style and functions of barnahus.eu

## Local workflow

The deployable WordPress plugin is the inner `barnahus-site-tools/` folder.

Useful commands:

```sh
./scripts/check.sh
./scripts/build-zip.sh
./scripts/bump-version.sh 1.0.1
./scripts/test-ftp.sh
./scripts/deploy-ftp.sh
```

The build command creates `dist/barnahus-site-tools.zip`, which can be uploaded in WordPress via **Plugins > Add New Plugin > Upload Plugin**.

Pushing a tag like `v1.0.1` to GitHub builds a versioned release zip through GitHub Actions.

See `WORKFLOW.md` for the full edit, review, package, and deploy workflow.

Current plugin features include featured-post cards, Buy Me a Coffee styling, newsletter tracking URL helpers, and manual Barnahus event cards via `[barnahus_events]`.

## Newsletter link tracking

Use `tools/newsletter-link-tracker.html` while preparing newsletters in Brevo. Paste the destination URL, set the campaign such as `2026-07`, choose the content label, then copy the tracked URL into the Brevo link target.

The WordPress helper is also available inside the plugin:

```php
echo esc_url(barnahus_newsletter_tracked_url(
    'https://barnahus.eu/forum/',
    '2026-07',
    'forum'
));
```

It produces links using:

```text
utm_source=newsletter
utm_medium=email
utm_campaign=YYYY-MM or YYYY-MM-topic
utm_content=section-or-link-name
```

Existing non-UTM query parameters and anchors are preserved. Existing `utm_*` parameters are replaced with the standard newsletter values.
