# Changelog

## Unreleased

## 1.3.0 - 2026-07-11

- Add the production Forum programme and participant routes with notebook and pathway assets.
- Require separate route and content-approval controls under Settings before Forum content is public.
- Prevent direct access to Forum templates, and add no-index and content-type protections to routed content.
- Restrict preview-state controls to signed-in editors and keep public shared links independent of preview state.
- Lazy-load Stripe only after a participant opens the notebook payment controls.
- Improve keyboard focus, focus trapping, body scroll locking, and accessible labels in the mobile session sheet and pathway viewer.
- Restore a proper accessible `h1` to the `/events` page without duplicating its visible theme title.
- Preserve unrelated editorial tags and categories when event series or event categories are updated, and include taxonomy data in event snapshots.
- Load featured-post styles only when the shortcode is rendered, remove both queued and raw Buy Me a Coffee embeds from disallowed pages, and stop indefinite polling.
- Build deterministic release archives directly from committed source.
- Keep Barnahus Quality Standards publishing source in the separate `barnahus-publishing` project and out of this plug-in.
- Back up the live plug-in before exact FTP mirroring so obsolete public files are removed without losing rollback coverage.
- Add regression checks for taxonomy preservation, preview gating, direct-template safety, local path leaks, PHP, and shell syntax.

## 1.2.4

- Add manual Barnahus Events admin area and `[barnahus_events]` shortcode.
- Add public Barnahus event detail pages with optional Luma embed support.
- Update event cards to use "Read more" links, translucent card styling, and double-width featured cards.
- Add an Event Dashboard for managing featured, pinned, and hidden state, Event Series tags, and registration URLs across events.
- Add a `featured_order` option so pinned events can be kept at the top or shown chronologically.
- Add card intro/excerpt editing to the Event Dashboard.
- Add registration status tags for events, including registration open, closed, waitlist, coming soon, and member only.
- Add per-event card link destinations for generated event pages, registration URLs, or manual card URLs.
- Align Event Dashboard link labels so the dropdown options match the URL fields.
- Simplify the Event Dashboard by using one registration URL and keeping embedded registration URL overrides in the full event editor.
- Allow event pages to embed supported registration URLs automatically when no embedded registration override is set.
- Streamline the WordPress admin menu so Barnahus Events opens directly to the Event Dashboard and hides the separate Add New and Event Series screens.
- Add a manual "Refresh from Luma" action that imports public Luma calendar events into regular WordPress posts tagged event.
- Add a conversion action for older Barnahus event records so they become normal WordPress posts.
- Add `time="past"` event grid support so expired events can move to a quieter past-events section instead of being hidden forever.
- Refine event card grid spacing and tag display so the default layout favours roomier three-column cards on wide pages.
- Add a dashboard quick-create form for planned event cards that do not need a full event page yet.
- Add a "No card link" option for forthcoming events that should appear as announcement cards only.
- Allow undated forthcoming events to appear in event grids.
- Replace the Event Dashboard table with responsive event panels for easier editing.
- Add Event Series tags, featured ordering, hidden events, and event card styling.
- Add responsive event grids, visual variants, compact mode, single-card shortcode support, and event-date admin sorting.
- Add newsletter tracking URL helper and local newsletter link generator.

## 1.0.0

- Initial Barnahus Site Tools plugin.
