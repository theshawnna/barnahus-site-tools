Hi Thord. I built these things because buy me a coffee has a bit annoying behaviour and color, and I wanted a cleaner in-site page embed. I broke the page briefly by having an extra PHP marker at the top of one of the files. It's all OK now. Cheers, s

# Barnahus Site Tools

Version: 1.0.0

A custom WordPress plugin containing functionality specific to the Barnahus Network website (https://barnahus.eu).

The purpose of this plugin is to keep Barnahus-specific functionality separate from the active theme, making future theme updates and redesigns simpler.

---

## Current features

### Featured Post shortcode

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

- Restrict widget to selected pages
- Custom button colour
- PT Serif typography
- Smaller popup text
- Hide widget on all other pages

---

## Folder structure

```
barnahus-site-tools/

├── barnahus-site-tools.php
├── README.md
├── CHANGELOG.md

├── css/
│   └── featured-post.css

└── includes/
    ├── init.php
    ├── featured-post.php
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