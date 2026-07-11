# Barnahus site tools

Version: 1.3.0

A custom WordPress plugin containing functionality specific to the Barnahus Network website (https://barnahus.eu).

The purpose of this plugin is to keep Barnahus-specific functionality separate from the active theme, making future theme updates and redesigns simpler.

---

## Current features

### Featured post shortcode

Displays a selected post or page as a featured content card.

Shortcode:

```text
[featured_post id="7751"]
```

Optional heading level:

```text
[featured_post id="7751" heading="h2"]
```

Supported heading levels:

- h1
- h2
- h3 (default)
- h4
- h5
- h6

---

### Buy Me a Coffee

Customisations include:

- Restrict the widget to selected pages
- Use the Barnahus button colour and typography
- Hide and, where WordPress registered it, unload the widget on all other pages
- Stop the fallback observer after ten seconds instead of polling indefinitely

---

### Barnahus events

Adds a Barnahus Events admin area for curated public event cards. Event pages are regular WordPress posts tagged `event`, so they can be found and edited through the normal Posts workflow.

The **Event Dashboard** screen under Barnahus Events lets editors manage public display settings for several events at once:

- Refresh public events from the Barnahus Luma calendar
- Convert older hidden event records into regular WordPress posts
- Add planned event cards without opening the full editor
- Featured
- Pinned to top
- Hidden
- Event Series tags
- Registration status tag
- Card intro/excerpt
- Card link destination
- Registration URL
- Manual card URL

Planned cards can be published with no date and no card link, which is useful for forthcoming events where the details are not ready yet. New planned cards and Luma imports are created as normal posts with the `event` tag. Event series are stored as regular post tags alongside `event`. Event updates preserve unrelated editorial tags and categories.

Shortcode:

```text
[barnahus_events]
```

Responsive grid with a fixed maximum number of columns:

```text
[barnahus_events columns="3"]
```

Automatic responsive grid with a custom minimum card width:

```text
[barnahus_events min_width="360"]
```

Compact cards:

```text
[barnahus_events compact="true"]
```

Visual variants. Quiet is the default:

```text
[barnahus_events variant="quiet"]
[barnahus_events variant="plain"]
[barnahus_events variant="standard"]
```

Shorter or longer summaries:

```text
[barnahus_events description_words="18"]
[barnahus_events show_description="false"]
```

Optional limit:

```text
[barnahus_events limit="6"]
```

Optional past-event display:

```text
[barnahus_events show_past="true"]
[barnahus_events time="past"]
```

`show_past="true"` shows all events. `time="past"` shows only expired events, newest first, which is useful for a separate past-events section.

Only show one event series:

```text
[barnahus_events series="webinars"]
```

Featured and pinned event controls:

```text
[barnahus_events featured="first"]
[barnahus_events featured="only"]
[barnahus_events featured="exclude"]
[barnahus_events featured_order="pinned"]
[barnahus_events featured_order="chronological"]
```

Featured events keep their larger visual styling in either mode. `pinned` keeps events marked "Pinned to top" before the chronological list; `chronological` keeps all cards in date order.

Single event card, useful inside WordPress Grid blocks:

```text
[barnahus_event_card id="123"]
[barnahus_event_card id="123" compact="true" variant="plain"]
```

Event fields include:

- Date
- Start time
- End time
- Location or platform
- Registration URL
- Embedded registration URL override
- Button label
- Featured toggle
- Pinned to top toggle
- Hidden toggle
- Event Series tags
- Registration status
- Card link destination
- Manual card URL
- Excerpt for the card intro

Cards can link to the generated event page on the Barnahus site, directly to the registration page, or to a manually entered URL. If an event has no registration or manual URL, the card falls back to the generated Barnahus event page. The event page can include the registration link, an embedded registration panel for supported registration URLs, and any extra event information added in the editor. The full event editor also includes an embedded registration URL override for rare cases where a registration service gives you a separate iframe URL.

Featured events appear first, then all other upcoming events. Events are sorted by event date within each group. In the responsive grid, featured events are visually stronger and span two columns when there is enough room.

---

### Forum programme and participants

Provides the production Forum companion pages at:

```text
/forum/programme
/forum/participants
```

The programme includes personal session saving, shareable links, calendar export, print support, practical information, and an on-demand Stripe notebook payment button. The participant page includes searchable highlights and an accessible pathway viewer.

The routes and their allow-listed assets are unavailable until an administrator selects both controls under **Settings > Barnahus site tools**:

- Publish the programme and participant routes
- Confirm that the current programme and participant information is approved for publication

Editors can add `?preview=1` to the programme URL to reveal the before, Forum day, and after-state preview controls. Public shared links cannot force a preview state.

---

### Newsletter link tracking

Adds a helper for creating Matomo-friendly newsletter links before sending through Brevo.

Convention:

```text
utm_source=newsletter
utm_medium=email
utm_campaign=YYYY-MM or YYYY-MM-topic
utm_content=section-or-link-name
```

PHP helper:

```php
echo esc_url(barnahus_newsletter_tracked_url(
    'https://barnahus.eu/forum/',
    '2026-07',
    'forum'
));
```

The helper preserves existing non-UTM query parameters and anchors, removes existing `utm_*` parameters, then adds the Barnahus newsletter values.

For newsletter preparation outside WordPress, open the local generator in the outer repository checkout:

```text
../tools/newsletter-link-tracker.html
```

Suggested content labels:

- forum
- lunch-learn
- library
- policy
- member-spotlight
- coming-up
- evaluation
- elpis
- safeguarding
- evivi
- quality-standards

Example links:

```text
Forum:
https://barnahus.eu/forum/?utm_source=newsletter&utm_medium=email&utm_campaign=2026-07&utm_content=forum

Library / ELPIS:
https://barnahus.eu/library/?utm_source=newsletter&utm_medium=email&utm_campaign=2026-07&utm_content=elpis

Policy Watch:
https://barnahus.eu/policy/?utm_source=newsletter&utm_medium=email&utm_campaign=2026-07&utm_content=policy

Member Spotlight:
https://barnahus.eu/members/?utm_source=newsletter&utm_medium=email&utm_campaign=2026-07&utm_content=member-spotlight
```

---

### Barnahus Quality Standards publishing

The publishing application and its generated review files belong in the sibling `barnahus-publishing` project. They are not copied into or deployed with this plug-in.

---

## Folder structure

```
barnahus-site-tools/

├── barnahus-site-tools.php
├── README.md
├── CHANGELOG.md

├── forum-preview/
│   ├── forum-programme-template.php
│   ├── forum-participants-template.php
│   ├── notebook-assets/
│   └── pathway-assets/

├── css/
│   ├── events.css
│   └── featured-post.css

└── includes/
    ├── events.php
    ├── forum-preview.php
    ├── init.php
    ├── helpers.php
    ├── featured-post.php
    ├── settings.php
    └── bmc.php
```

---

## Design principles

- Theme controls appearance.
- Plugin controls functionality.
- Keep features modular.
- Do not modify third-party plugins directly where possible.
- Prefer WordPress hooks and filters over editing plugin code.

---

## Future ideas

- Resource Card shortcode
- Publication Card shortcode
- Webinar helpers
- Membership utilities
- Library helpers
- Barnahus-specific Gutenberg blocks

---

Maintainer

Barnahus Europe
