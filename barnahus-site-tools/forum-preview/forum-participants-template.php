<?php
if (!defined('ABSPATH')) {
    http_response_code(404);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Barnahus Forum 2026 participants</title>
  <style>
    :root {
      --ink: #16131d;
      --muted: #625f6f;
      --paper: #fbf8f4;
      --surface: #ffffff;
      --line: #dad5ce;
      --blue: #606ca5;
      --deep-blue: #3f4b86;
      --blue-dark: var(--deep-blue);
      --soft-blue: #eef1fb;
      --blue-wash: var(--soft-blue);
      --cream: #fffdf9;
      --peach: #f6dfd8;
      --soft-peach: #fff4f1;
      --earth: #7f3b2d;
      --green: #23775e;
      --shadow: 0 16px 40px rgba(43, 39, 55, .1);
      --radius: 8px;
      --font: "Switzer", "Inter", "Helvetica Neue", Arial, sans-serif;
      --font-heading: var(--font);
      --font-body: var(--font);
      --heading: var(--ink);
      font-family: var(--font);
    }

    * { box-sizing: border-box; }

    body {
      background: var(--paper);
      color: var(--ink);
      font-family: var(--font);
      font-size: 15px;
      line-height: 1.45;
      margin: 0;
    }

    body.viewer-open {
      overflow: hidden;
    }

    a { color: inherit; }

    button, input { font: inherit; }

    .topbar {
      background: #fff;
      border-bottom: 1px solid var(--line);
      position: sticky;
      top: 0;
      z-index: 20;
    }

    .topbar-inner {
      align-items: center;
      display: grid;
      gap: 14px;
      grid-template-columns: minmax(220px, 1fr) auto;
      margin: 0 auto;
      max-width: 1280px;
      padding: 14px 18px;
    }

    .brand strong {
      display: block;
      font-size: 18px;
      line-height: 1.1;
    }

    .brand span {
      color: var(--muted);
      display: block;
      font-size: 12px;
      margin-top: 4px;
    }

    .top-actions {
      display: flex;
      gap: 8px;
      overflow-x: auto;
      scrollbar-width: none;
    }

    .top-actions::-webkit-scrollbar { display: none; }

    .pill,
    .tab {
      align-items: center;
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--ink);
      cursor: pointer;
      display: inline-flex;
      font-weight: 780;
      min-height: 38px;
      padding: 8px 14px;
      text-decoration: none;
      white-space: nowrap;
    }

    .top-actions .pill,
    .tab {
      background: transparent;
      border: 0;
      border-radius: 0;
      padding: 8px 6px;
    }

    .pill.primary,
    .tab[aria-current="page"] {
      background: var(--blue);
      border-color: var(--blue);
      color: #fff;
      font-weight: 800;
    }

    .top-actions .pill[aria-current="page"] {
      background: transparent;
      border: 0;
      box-shadow: inset 0 -3px 0 var(--deep-blue);
      color: var(--deep-blue);
      font-weight: 800;
    }

    .top-actions .pill:hover,
    .top-actions .pill:focus-visible,
    .tab:hover,
    .tab:focus-visible {
      color: var(--deep-blue);
      outline: none;
    }

    .pill:hover,
    .pill:focus-visible,
    .text-button:hover,
    .text-button:focus-visible,
    .filter-button:hover,
    .filter-button:focus-visible {
      border-color: rgba(96, 108, 165, .48);
      box-shadow: 0 0 0 3px rgba(96, 108, 165, .12);
      outline: none;
    }

    .top-actions .pill:hover,
    .top-actions .pill:focus-visible {
      border-color: transparent;
      box-shadow: none;
    }

    .top-actions .pill[aria-current="page"],
    .top-actions .pill[aria-current="page"]:hover,
    .top-actions .pill[aria-current="page"]:focus-visible {
      box-shadow: inset 0 -3px 0 var(--deep-blue);
    }

    .hero {
      background: linear-gradient(180deg, #eef1fb 0%, #fbf8f4 100%);
      border-block: 1px solid var(--line);
      margin: 0;
      padding: 52px 18px;
      scroll-margin-top: 96px;
    }

    .hero-inner {
      display: grid;
      gap: 34px;
      grid-template-columns: minmax(0, 1fr) minmax(260px, .55fr);
      margin: 0 auto;
      max-width: 1220px;
    }

    .eyebrow {
      font-size: 13px;
      font-weight: 850;
      letter-spacing: .06em;
      text-transform: uppercase;
    }

    h1 {
      font-family: var(--font);
      font-size: clamp(42px, 6vw, 72px);
      letter-spacing: 0;
      line-height: .96;
      margin: 0 0 14px;
      max-width: 760px;
    }

    .hero-meta {
      align-items: center;
      color: var(--muted);
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      font-size: 13px;
      font-weight: 750;
      margin-top: 12px;
    }

    .map-link {
      color: var(--deep-blue);
      font-weight: 850;
      text-decoration: underline;
      text-underline-offset: 3px;
    }

    .hero-side {
      align-content: start;
      display: grid;
      gap: 10px;
    }

    .hero-stat {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: var(--radius);
      display: grid;
      gap: 4px;
      padding: 12px;
    }

    .hero-stat span {
      color: var(--muted);
      font-size: 12px;
      font-weight: 850;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    .hero-stat strong {
      color: var(--ink);
      font-size: 18px;
      line-height: 1.12;
    }

    .page {
      margin: 0 auto;
      max-width: 1220px;
      padding: 18px;
    }

    .panel,
    .country-card,
    .poster-card,
    .quote-card {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
    }

    .section {
      margin: 18px -18px 0;
      padding: 30px 18px;
      scroll-margin-top: 92px;
    }

    .section:nth-of-type(odd) {
      background: rgba(255, 255, 255, .34);
      border-block: 1px solid rgba(217, 212, 207, .58);
    }

    .section:nth-of-type(even) {
      background: rgba(236, 238, 248, .32);
      border-block: 1px solid rgba(96, 108, 165, .14);
    }

    .poster-card .poster-preview {
      background:
        linear-gradient(90deg, rgba(96, 108, 165, .18) 0 18%, transparent 18% 100%),
        repeating-linear-gradient(0deg, #fff, #fff 22px, #f0f2fb 22px, #f0f2fb 24px);
    }

    .section-head {
      align-items: end;
      display: flex;
      gap: 14px;
      justify-content: space-between;
      margin-bottom: 12px;
    }

    .section-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: flex-end;
    }

    .section-head h2 {
      font-size: 27px;
      line-height: 1.12;
      margin: 0 0 4px;
    }

    .section-head p {
      color: var(--muted);
      margin: 0;
      max-width: 720px;
    }

    .map-layout {
      display: grid;
      gap: 16px;
      grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
    }

    .panel {
      padding: 16px;
    }

    .map-visual {
      background:
        linear-gradient(145deg, rgba(236, 238, 248, .9), rgba(251, 248, 242, .88)),
        radial-gradient(circle at 30% 42%, rgba(96, 108, 165, .18), transparent 28%);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      min-height: 370px;
      overflow: hidden;
      position: relative;
    }

    .map-visual svg {
      display: block;
      height: 100%;
      min-height: 370px;
      width: 100%;
    }

    .map-label {
      background: rgba(255, 255, 255, .9);
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--blue-dark);
      font-size: 12px;
      font-weight: 800;
      padding: 5px 8px;
      position: absolute;
      white-space: nowrap;
    }

    .map-label.one { left: 46%; top: 18%; }
    .map-label.two { left: 55%; top: 43%; }
    .map-label.three { left: 31%; top: 53%; }
    .map-label.four { left: 68%; top: 64%; }

    .map-point {
      fill: var(--blue);
      opacity: .94;
    }

    .map-point.subtle {
      fill: var(--green);
    }

    .map-source {
      background: rgba(255, 255, 255, .86);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      bottom: 12px;
      color: var(--muted);
      font-size: 12px;
      font-weight: 700;
      left: 12px;
      padding: 7px 9px;
      position: absolute;
      right: 12px;
    }

    .country-list {
      display: grid;
      gap: 10px;
    }

    .country-card {
      display: grid;
      gap: 8px;
      padding: 13px;
      text-align: left;
      width: 100%;
    }

    .country-card.featured {
      border-color: var(--blue);
      box-shadow: inset 4px 0 0 var(--blue), var(--shadow);
    }

    .country-card h3 {
      font-size: 18px;
      line-height: 1.15;
      margin: 0;
    }

    .country-card p,
    .poster-card p,
    .quote-card p,
    .panel li {
      color: var(--muted);
      font-family: var(--font-body);
      margin: 0;
    }

    .tags {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }

    .tag {
      background: var(--blue-wash);
      border: 1px solid rgba(96, 108, 165, .24);
      border-radius: 999px;
      color: var(--blue-dark);
      font-size: 12px;
      font-weight: 800;
      padding: 4px 8px;
    }

    .tag.peach {
      background: var(--soft-peach);
      color: var(--earth);
    }

    .poster-grid {
      display: grid;
      gap: 12px;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .quote-grid {
      display: grid;
      gap: 12px;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }

    .poster-card,
    .quote-card {
      display: grid;
      gap: 11px;
      padding: 14px;
    }

    .poster-preview {
      align-items: center;
      border: 1px solid var(--line);
      border-radius: var(--radius);
      display: flex;
      justify-content: center;
      min-height: 128px;
      padding: 16px;
    }

    .poster-preview span {
      color: var(--blue-dark);
      font-size: 16px;
      font-weight: 900;
      line-height: 1.05;
      text-align: center;
    }

    .poster-card h3,
    .quote-card h3 {
      font-size: 18px;
      line-height: 1.15;
      margin: 0;
    }

    .insight-layout {
      display: grid;
      gap: 14px;
      grid-template-columns: 1fr 1fr;
      margin-top: 14px;
    }

    .snapshot-numbers {
      display: grid;
      gap: 12px;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      margin-bottom: 14px;
    }

    .snapshot-number {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      display: grid;
      gap: 5px;
      padding: 16px;
    }

    .snapshot-number strong {
      color: var(--heading);
      display: block;
      font-size: 38px;
      line-height: .95;
    }

    .snapshot-number span {
      color: var(--muted);
      font-size: 13px;
      font-weight: 850;
      text-transform: uppercase;
    }

    .who-copy {
      color: var(--ink);
      font-family: var(--font-body);
      font-size: 17px;
      line-height: 1.45;
      margin: 0 0 14px;
      max-width: 920px;
    }

    .who-copy + .who-copy {
      margin-top: -6px;
    }

    .role-grid {
      display: grid;
      gap: 14px;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      margin-top: 14px;
    }

    .chart-panel {
      padding: 16px;
    }

    .ranked-chart {
      display: grid;
      gap: 12px;
      margin-top: 14px;
    }

    .ranked-row {
      display: grid;
      gap: 6px;
    }

    .ranked-top {
      align-items: baseline;
      display: grid;
      gap: 10px;
      grid-template-columns: minmax(0, 1fr) auto;
    }

    .ranked-label {
      color: var(--ink);
      font-size: 13px;
      font-weight: 850;
      line-height: 1.25;
    }

    .ranked-value {
      color: var(--ink);
      font-size: 13px;
      font-weight: 950;
      text-align: right;
    }

    .ranked-track {
      background: #ece8e2;
      border-radius: 999px;
      height: 9px;
      overflow: hidden;
    }

    .ranked-track span {
      background: var(--blue-dark);
      display: block;
      height: 100%;
    }

    .ranked-row:nth-child(2) .ranked-track span,
    .ranked-row:nth-child(3) .ranked-track span {
      background: var(--blue);
    }

    .ranked-row:nth-child(n+4) .ranked-track span {
      background: #aeb8d8;
    }

    .bar-list {
      display: grid;
      gap: 12px;
      margin-top: 12px;
    }

    .bar-row {
      display: grid;
      gap: 6px;
    }

    .bar-top {
      display: block;
    }

    .bar-top strong {
      font-size: 13px;
    }

    .bar-track {
      background: var(--blue-wash);
      border-radius: 999px;
      height: 10px;
      overflow: hidden;
    }

    .bar-track span {
      background: var(--blue);
      display: block;
      height: 100%;
    }

    .quote-card {
      background: var(--cream);
    }

    .library-tools {
      align-items: end;
      display: grid;
      gap: 12px;
      grid-template-columns: minmax(220px, .9fr) minmax(0, 1.4fr) auto;
      margin: 0 0 14px;
    }

    .search-field {
      display: grid;
      gap: 5px;
    }

    .search-field label,
    .filter-label,
    .result-count {
      color: var(--muted);
      font-size: 12px;
      font-weight: 850;
      text-transform: uppercase;
    }

    .search-field input {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 999px;
      min-height: 42px;
      padding: 9px 13px;
      width: 100%;
    }

    .filter-group {
      display: grid;
      gap: 6px;
    }

    .filter-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 7px;
    }

    .filter-button {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--ink);
      cursor: pointer;
      min-height: 36px;
      padding: 7px 11px;
    }

    .filter-button[aria-pressed="true"] {
      background: var(--blue);
      border-color: var(--blue);
      color: #fff;
      font-weight: 850;
    }

    .result-count {
      justify-self: end;
      white-space: nowrap;
    }

    .featured-quotes {
      display: grid;
      gap: 12px;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      margin-bottom: 14px;
    }

    .quote-card.featured {
      background: var(--surface);
      border-color: var(--blue);
      box-shadow: inset 4px 0 0 var(--blue), var(--shadow);
    }

    .quote-meta {
      align-items: center;
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }

    .quote-card[hidden],
    .poster-card[hidden] {
      display: none;
    }

    .library-actions {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-top: 14px;
    }

    .text-button {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--blue-dark);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 850;
      min-height: 40px;
      padding: 8px 14px;
      text-decoration: none;
    }

    .section-download {
      background: transparent;
      border: 0;
      color: var(--blue-dark);
      font-size: 13px;
      font-weight: 850;
      min-height: auto;
      padding: 0;
      text-decoration: underline;
      text-underline-offset: 4px;
    }

    .section-download:hover,
    .section-download:focus-visible {
      color: var(--blue);
      outline: 2px solid rgba(96, 108, 165, .22);
      outline-offset: 4px;
    }

    .text-button[aria-disabled="true"] {
      color: var(--muted);
      cursor: not-allowed;
      opacity: .58;
    }

    .pathway-intro {
      color: var(--ink);
      display: grid;
      font-family: var(--font-body);
      font-size: 17px;
      gap: 12px;
      line-height: 1.45;
      margin-bottom: 16px;
      max-width: 920px;
    }

    .pathway-intro p {
      margin: 0;
    }

    .poster-card h3 {
      order: -2;
    }

    .poster-card .poster-preview {
      order: -1;
    }

    .poster-tags {
      border-top: 1px solid var(--line);
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      padding-top: 11px;
    }

    .poster-card {
      appearance: none;
      color: inherit;
      cursor: pointer;
      font: inherit;
      text-align: left;
    }

    .poster-card:focus-visible,
    .poster-card:hover {
      border-color: var(--blue);
      outline: 3px solid rgba(96, 108, 165, .18);
    }

    .poster-preview img {
      display: block;
      height: 100%;
      object-fit: cover;
      width: 100%;
    }

    .poster-preview.has-image {
      align-items: stretch;
      aspect-ratio: 16 / 10;
      justify-content: stretch;
      overflow: hidden;
      padding: 0;
    }

    .viewer {
      background: rgba(17, 11, 16, .42);
      inset: 0;
      padding: 22px;
      position: fixed;
      z-index: 30;
    }

    .viewer[hidden] {
      display: none;
    }

    .viewer-dialog {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: 0 24px 70px rgba(17, 11, 16, .28);
      display: grid;
      grid-template-rows: auto minmax(0, 1fr);
      margin: 0 auto;
      max-height: calc(100vh - 44px);
      max-width: 1180px;
      overflow: hidden;
    }

    .viewer-head {
      align-items: center;
      border-bottom: 1px solid var(--line);
      display: grid;
      gap: 12px;
      grid-template-columns: minmax(0, 1fr) auto auto;
      padding: 14px 16px;
    }

    .viewer-head h3 {
      font-size: 23px;
      line-height: 1.1;
      margin: 0 0 3px;
    }

    .viewer-head p {
      color: var(--muted);
      font-family: var(--font-body);
      margin: 0;
    }

    .viewer-nav {
      display: flex;
      gap: 7px;
    }

    .viewer-body {
      display: grid;
      gap: 0;
      grid-template-columns: minmax(0, 1fr) 300px;
      min-height: 0;
    }

    .viewer-image {
      background: #efebe6;
      overflow: auto;
      padding: 18px;
    }

    .viewer-image img,
    .viewer-image .poster-preview {
      background: #fff;
      border: 1px solid var(--line);
      display: block;
      margin: 0 auto;
      max-width: none;
      width: var(--viewer-zoom, 100%);
    }

    .viewer-side {
      border-left: 1px solid var(--line);
      display: grid;
      gap: 14px;
      padding: 16px;
      align-content: start;
    }

    .viewer-side p {
      color: var(--muted);
      font-family: var(--font-body);
      margin: 0;
    }

    .zoom-control {
      display: grid;
      gap: 6px;
    }

    .download-stack {
      display: grid;
      gap: 8px;
    }

    .zoom-control input {
      accent-color: var(--blue);
      width: 100%;
    }

    @media (max-width: 920px) {
      .topbar-inner {
        grid-template-columns: 1fr;
      }

      .top-actions {
        margin: 0 -18px;
        padding: 0 18px;
      }

      .hero-inner,
      .map-layout,
      .insight-layout,
      .role-grid {
        grid-template-columns: 1fr;
      }

      .poster-grid,
      .quote-grid,
      .featured-quotes,
      .snapshot-numbers {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .library-tools,
      .viewer-body {
        grid-template-columns: 1fr;
      }

      .viewer-side {
        border-left: 0;
        border-top: 1px solid var(--line);
      }

    }

    @media (max-width: 620px) {
      .poster-grid,
      .quote-grid,
      .featured-quotes,
      .snapshot-numbers {
        grid-template-columns: 1fr;
      }

      .viewer {
        padding: 0;
      }

      .viewer-dialog {
        border-radius: 0;
        max-height: 100vh;
      }

      .viewer-head {
        grid-template-columns: 1fr auto;
      }

      .viewer-nav {
        grid-column: 1 / -1;
      }

      .section-head {
        align-items: start;
        flex-direction: column;
      }

    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <strong>Barnahus Forum 2026</strong>
        <span>24 November - Hamburg</span>
      </div>
      <nav class="top-actions" aria-label="Page links">
        <a class="pill" href="/forum/programme">Programme</a>
        <a class="pill" aria-current="page" href="#top">Participants</a>
        <a class="pill" href="#snapshot">Snapshot</a>
        <a class="pill" href="#good-news">Good news</a>
        <a class="pill" href="#countries">Map</a>
        <a class="pill" href="#posters">Show us your pathway</a>
      </nav>
    </div>
  </header>

  <section class="hero" id="top">
    <div class="hero-inner">
      <div>
        <h1>About the participants</h1>
        <div class="hero-meta">
          <span>24 November 2026</span>
          <a class="map-link" href="https://www.google.com/maps/place/Nord+Event+Panoramadeck/@53.5561899,9.9785809,740m/data=!3m3!1e3!4b1!5s0x47b18f21d3af575d:0xeab10e4e1eb5c6bf!4m6!3m5!1s0x4163bcb34dc214d7:0x700a7fef23e00f7a!8m2!3d53.55619!4d9.9834518!16s%2Fg%2F1hc1wqf9p?entry=ttu" target="_blank" rel="noopener">NordEvent Panoramadeck, Emporio, Dammtorwall 15, Hamburg</a>
          <span class="pill">Last updated 11 July 2026</span>
        </div>
      </div>
      <div class="hero-side" aria-label="Participant highlights">
        <div class="hero-stat">
          <span>Expected</span>
          <strong>120+ participants</strong>
        </div>
        <div class="hero-stat">
          <span>Represented</span>
          <strong>31 delegations</strong>
        </div>
      </div>
    </div>
  </section>

  <main class="page">
    <section class="section" id="snapshot">
      <div class="section-head">
        <div>
          <h2>Participant snapshot</h2>
        </div>
      </div>

      <p class="who-copy">Delegations include Barnahus teams, ministry representatives, service providers, project partners, researchers and representatives from emerging and established Barnahus contexts. Many are involved in implementing or scaling Barnahus nationally. Others are just starting out and are here to learn from those further ahead.</p>

      <div class="snapshot-numbers" aria-label="Participant headline numbers">
        <div class="snapshot-number">
          <strong>120+</strong>
          <span>participants expected</span>
        </div>
        <div class="snapshot-number">
          <strong>31</strong>
          <span>delegations listed</span>
        </div>
      </div>

      <div class="role-grid">
          <div class="panel chart-panel">
            <h3>Roles in Barnahus</h3>
            <div class="ranked-chart" aria-label="Roles in Barnahus">
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">Multidisciplinary interagency collaboration</span><span class="ranked-value">64</span></div>
                <div class="ranked-track"><span style="width: 100%;"></span></div>
              </div>
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">Child protection / social work</span><span class="ranked-value">43</span></div>
                <div class="ranked-track"><span style="width: 67%;"></span></div>
              </div>
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">Criminal investigation / law enforcement</span><span class="ranked-value">27</span></div>
                <div class="ranked-track"><span style="width: 42%;"></span></div>
              </div>
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">Mental health / psychology / therapeutic work</span><span class="ranked-value">27</span></div>
                <div class="ranked-track"><span style="width: 42%;"></span></div>
              </div>
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">Health / medical / physical wellbeing</span><span class="ranked-value">11</span></div>
                <div class="ranked-track"><span style="width: 17%;"></span></div>
              </div>
            </div>
          </div>

          <div class="panel chart-panel">
            <h3>How participants support Barnahus</h3>
            <div class="ranked-chart" aria-label="Supporting roles">
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">Expanding Barnahus nationally</span><span class="ranked-value">62</span></div>
                <div class="ranked-track"><span style="width: 100%;"></span></div>
              </div>
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">Training and professional development</span><span class="ranked-value">38</span></div>
                <div class="ranked-track"><span style="width: 61%;"></span></div>
              </div>
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">Policymaking / government</span><span class="ranked-value">29</span></div>
                <div class="ranked-track"><span style="width: 47%;"></span></div>
              </div>
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">Quality assurance and evaluation</span><span class="ranked-value">29</span></div>
                <div class="ranked-track"><span style="width: 47%;"></span></div>
              </div>
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">Network coordination / leadership roles</span><span class="ranked-value">22</span></div>
                <div class="ranked-track"><span style="width: 35%;"></span></div>
              </div>
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">EU / European-level support / advocacy</span><span class="ranked-value">12</span></div>
                <div class="ranked-track"><span style="width: 19%;"></span></div>
              </div>
              <div class="ranked-row">
                <div class="ranked-top"><span class="ranked-label">Researchers / academics / consultants</span><span class="ranked-value">6</span></div>
                <div class="ranked-track"><span style="width: 10%;"></span></div>
              </div>
            </div>
          </div>
      </div>

      <div class="insight-layout">
        <div class="panel">
          <h3>Challenges people want to solve</h3>
          <div class="bar-list">
            <div class="bar-row">
              <div class="bar-top"><strong>Multidisciplinary teamwork</strong></div>
              <div class="bar-track"><span style="width: 82%;"></span></div>
            </div>
            <div class="bar-row">
              <div class="bar-top"><strong>Sustainable funding</strong></div>
              <div class="bar-track"><span style="width: 76%;"></span></div>
            </div>
            <div class="bar-row">
              <div class="bar-top"><strong>Therapy and crisis support</strong></div>
              <div class="bar-track"><span style="width: 61%;"></span></div>
            </div>
            <div class="bar-row">
              <div class="bar-top"><strong>Child participation</strong></div>
              <div class="bar-track"><span style="width: 54%;"></span></div>
            </div>
          </div>
        </div>

        <div class="panel">
          <h3>Topics people plan to follow</h3>
          <div class="bar-list">
            <div class="bar-row">
              <div class="bar-top"><strong>Policy level recommendations</strong></div>
              <div class="bar-track"><span style="width: 72%;"></span></div>
            </div>
            <div class="bar-row">
              <div class="bar-top"><strong>Therapy</strong></div>
              <div class="bar-track"><span style="width: 66%;"></span></div>
            </div>
            <div class="bar-row">
              <div class="bar-top"><strong>Child participation</strong></div>
              <div class="bar-track"><span style="width: 58%;"></span></div>
            </div>
            <div class="bar-row">
              <div class="bar-top"><strong>Investigation</strong></div>
              <div class="bar-track"><span style="width: 45%;"></span></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="good-news">
      <div class="section-head">
        <div>
          <h2>Good news from the Network</h2>
        </div>
      </div>

      <div class="library-tools" data-library-tools="quotes">
        <div class="search-field">
          <label for="quoteSearch">Search good news</label>
          <input id="quoteSearch" type="search" placeholder="Search quotes, themes or topics">
        </div>
        <div class="filter-group" aria-label="Filter good news">
          <span class="filter-label">Theme</span>
          <div class="filter-buttons">
            <button class="filter-button" type="button" data-filter-group="quotes" data-filter="all" aria-pressed="true">All</button>
            <button class="filter-button" type="button" data-filter-group="quotes" data-filter="implementation" aria-pressed="false">Implementation</button>
            <button class="filter-button" type="button" data-filter-group="quotes" data-filter="evaluation" aria-pressed="false">Evaluation</button>
            <button class="filter-button" type="button" data-filter-group="quotes" data-filter="children" aria-pressed="false">Children's voices</button>
            <button class="filter-button" type="button" data-filter-group="quotes" data-filter="training" aria-pressed="false">Training</button>
            <button class="filter-button" type="button" data-filter-group="quotes" data-filter="collaboration" aria-pressed="false">Collaboration</button>
          </div>
        </div>
        <span class="result-count" id="quoteCount" aria-live="polite">Showing 0</span>
      </div>

      <div class="featured-quotes" aria-label="Featured good news">
        <article class="quote-card featured" data-quote-card data-tags="implementation policy" data-search="national rollout moving planning mobilisation implementation policy">
          <h3>National rollout is moving from planning to mobilisation</h3>
          <p>"National rollout is beginning to mobilise."</p>
          <div class="quote-meta">
            <span class="tag">Implementation</span>
            <span class="tag peach">Policy</span>
          </div>
        </article>
        <article class="quote-card featured" data-quote-card data-tags="service development recognition" data-search="barnahus becoming visible recognised institution system service development recognition">
          <h3>Barnahus is becoming more visible in the system</h3>
          <p>"Becoming a well-recognised institution in the system."</p>
          <div class="quote-meta">
            <span class="tag">Service development</span>
            <span class="tag peach">Recognition</span>
          </div>
        </article>
      </div>

      <div class="quote-grid" id="quoteGrid">
        <article class="quote-card" data-quote-card data-tags="evaluation children" data-search="children evaluation feedback waiting room practice">
          <h3>Children's feedback is changing everyday practice</h3>
          <p>"Feedback from children helped us improve the waiting room and how we prepare families before appointments."</p>
          <div class="quote-meta"><span class="tag">Evaluation</span><span class="tag peach">Children's voices</span></div>
        </article>
        <article class="quote-card" data-quote-card data-tags="collaboration implementation" data-search="multidisciplinary cooperation police health child protection meetings">
          <h3>Shared meetings are becoming more routine</h3>
          <p>"We have strengthened regular multidisciplinary meetings and clearer agreements between child protection, police and health services."</p>
          <div class="quote-meta"><span class="tag">Collaboration</span><span class="tag peach">Implementation</span></div>
        </article>
        <article class="quote-card" data-quote-card data-tags="training" data-search="training forensic interview trauma informed practice">
          <h3>Training is building confidence across teams</h3>
          <p>"More professionals have completed training in child-friendly, trauma-informed practice this year."</p>
          <div class="quote-meta"><span class="tag">Training</span></div>
        </article>
        <article class="quote-card" data-quote-card data-tags="implementation policy" data-search="national standards guidance law policy implementation">
          <h3>National guidance is becoming more concrete</h3>
          <p>"We have made progress towards clearer national guidance for how Barnahus should work in practice."</p>
          <div class="quote-meta"><span class="tag">Implementation</span><span class="tag peach">Policy</span></div>
        </article>
        <article class="quote-card" data-quote-card data-tags="service collaboration" data-search="new service opening cooperation referrals">
          <h3>New services are opening pathways for children</h3>
          <p>"A new Barnahus service has opened, creating a clearer route for children and families to receive support."</p>
          <div class="quote-meta"><span class="tag">Service development</span><span class="tag peach">Collaboration</span></div>
        </article>
        <article class="quote-card" data-quote-card data-tags="evaluation implementation" data-search="quality assurance evaluation data indicators">
          <h3>Quality work is becoming part of the model</h3>
          <p>"We have started using shared indicators to understand quality and identify where the model needs to improve."</p>
          <div class="quote-meta"><span class="tag">Evaluation</span><span class="tag peach">Implementation</span></div>
        </article>
        <article class="quote-card" data-quote-card data-tags="children service" data-search="participation child friendly information children">
          <h3>Children are getting clearer information</h3>
          <p>"We improved the way children receive information before, during and after their visit."</p>
          <div class="quote-meta"><span class="tag">Children's voices</span><span class="tag peach">Service development</span></div>
        </article>
        <article class="quote-card" data-quote-card data-tags="collaboration policy" data-search="agreement protocol ministry cross sector cooperation">
          <h3>Cross-sector agreements are becoming stronger</h3>
          <p>"We have agreed a new protocol that clarifies who does what, and when, across the agencies involved."</p>
          <div class="quote-meta"><span class="tag">Collaboration</span><span class="tag peach">Policy</span></div>
        </article>
      </div>
      <div class="library-actions">
        <button class="text-button" type="button" id="showAllQuotes">Show all good news</button>
      </div>
    </section>

    <section class="section" id="countries">
      <div class="section-head">
        <div>
          <h2>Participant map</h2>
        </div>
      </div>

      <div class="map-layout">
        <div class="panel">
          <div class="map-visual" aria-label="Visual map of participant countries">
            <svg viewBox="0 0 780 430" role="img" aria-label="Participant countries shown as a regional overview">
              <path d="M161 93 239 52 333 65 390 41 483 88 553 80 628 131 589 190 642 260 572 314 474 291 421 359 328 332 239 363 167 303 195 230 133 174Z" fill="#ffffff" stroke="#d9d4cf" stroke-width="3"/>
              <path d="M264 122 328 104 379 137 432 118 486 151 476 207 411 216 369 254 306 232 271 184Z" fill="#eceef8" stroke="#606ca5" stroke-width="3"/>
              <path d="M211 238 278 252 320 313 238 334 183 295Z" fill="#fbebe7" stroke="#eeb6aa" stroke-width="3"/>
              <path d="M493 222 590 233 574 293 492 278Z" fill="#eef2ec" stroke="#60735f" stroke-width="3"/>
              <circle class="map-point" cx="318" cy="118" r="7"/>
              <circle class="map-point" cx="347" cy="136" r="7"/>
              <circle class="map-point" cx="377" cy="153" r="7"/>
              <circle class="map-point" cx="411" cy="170" r="7"/>
              <circle class="map-point" cx="302" cy="189" r="7"/>
              <circle class="map-point" cx="340" cy="214" r="7"/>
              <circle class="map-point" cx="447" cy="206" r="7"/>
              <circle class="map-point" cx="251" cy="272" r="7"/>
              <circle class="map-point" cx="289" cy="305" r="7"/>
              <circle class="map-point" cx="525" cy="257" r="7"/>
              <circle class="map-point" cx="592" cy="315" r="7"/>
              <circle class="map-point subtle" cx="118" cy="340" r="7"/>
              <circle class="map-point subtle" cx="686" cy="312" r="7"/>
            </svg>
            <span class="map-label one">Nordic and Baltic</span>
            <span class="map-label two">Central and Eastern Europe</span>
            <span class="map-label three">UK and Ireland</span>
            <span class="map-label four">Southern Europe</span>
          </div>
        </div>

        <div class="country-list">
          <article class="country-card featured">
            <h3>Participant countries</h3>
            <p>Albania, Australia, Bulgaria, Croatia, Cyprus, Czech Republic, Denmark, England, Estonia, Faroe Islands, Finland, Georgia, Germany, Greece, Hungary, Iceland, Ireland, Latvia, Lithuania, Malta, Moldova, Northern Ireland, Norway, Poland, Romania, Scotland, Slovenia, Spain, Sweden, UAE and Ukraine.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section" id="posters">
      <div class="section-head">
        <div>
          <h2>Show us your pathway</h2>
        </div>
        <div class="section-actions">
          <a class="text-button section-download" href="/forum/assets/pathway-assets/pathways-package.zip" download>Download all pathways</a>
        </div>
      </div>

      <div class="library-tools" data-library-tools="pathways">
        <div class="search-field">
          <label for="pathwaySearch">Search pathways</label>
          <input id="pathwaySearch" type="search" placeholder="Search country, city, system or host">
        </div>
        <div class="filter-group" aria-label="Filter pathways">
          <span class="filter-label">Type</span>
          <div class="filter-buttons">
            <button class="filter-button" type="button" data-filter-group="pathways" data-filter="all" aria-pressed="true">All</button>
            <button class="filter-button" type="button" data-filter-group="pathways" data-filter="national" aria-pressed="false">National</button>
            <button class="filter-button" type="button" data-filter-group="pathways" data-filter="local" aria-pressed="false">Local</button>
            <button class="filter-button" type="button" data-filter-group="pathways" data-filter="health" aria-pressed="false">Health-led</button>
            <button class="filter-button" type="button" data-filter-group="pathways" data-filter="justice" aria-pressed="false">Justice-led</button>
          </div>
        </div>
        <span class="result-count" id="pathwayCount" aria-live="polite">Showing 0</span>
      </div>

      <div class="poster-grid" id="pathwayGrid">
        <button class="poster-card" type="button" data-pathway-card data-tags="justice local" data-title="Slovenia" data-subtitle="Justice system · Ministry / public authority · 1 location" data-search="slovenia justice system ministry public authority one location local" data-image="" data-download="">
          <h3>Slovenia</h3>
          <div class="poster-preview"><span>Barnahus Slovenia pathway</span></div>
          <div class="poster-tags">
            <span class="tag">Justice system</span>
            <span class="tag peach">Ministry / public authority</span>
            <span class="tag">1 location</span>
          </div>
        </button>
        <button class="poster-card" type="button" data-pathway-card data-tags="health local" data-title="London" data-subtitle="Health-led specialist service · Hospital / NHS partnership · 1 location" data-search="london lighthouse england health led specialist service hospital nhs partnership local" data-image="/forum/assets/pathway-assets/england.png" data-download="/forum/assets/pathway-assets/england.png">
          <h3>London</h3>
          <div class="poster-preview has-image"><img src="/forum/assets/pathway-assets/england.png" alt="Preview of the London pathway"></div>
          <div class="poster-tags">
            <span class="tag">Health-led specialist service</span>
            <span class="tag peach">Hospital / NHS partnership</span>
            <span class="tag">1 location</span>
          </div>
        </button>
        <button class="poster-card" type="button" data-pathway-card data-tags="national justice" data-title="Latvia" data-subtitle="National child protection system · Ministry / national authority · National pathway" data-search="latvia national child protection system ministry national authority justice" data-image="" data-download="">
          <h3>Latvia</h3>
          <div class="poster-preview"><span>Latvia pathway</span></div>
          <div class="poster-tags">
            <span class="tag">National child protection system</span>
            <span class="tag peach">Ministry / national authority</span>
            <span class="tag">National pathway</span>
          </div>
        </button>
        <button class="poster-card" type="button" data-pathway-card data-tags="national justice" data-title="Iceland" data-subtitle="Child protection and justice system · National Barnahus service · National pathway" data-search="iceland national child protection justice system barnahus service" data-image="" data-download="">
          <h3>Iceland</h3>
          <div class="poster-preview"><span>Iceland pathway</span></div>
          <div class="poster-tags">
            <span class="tag">Child protection and justice system</span>
            <span class="tag peach">National Barnahus service</span>
            <span class="tag">National pathway</span>
          </div>
        </button>
        <button class="poster-card" type="button" data-pathway-card data-tags="health national" data-title="Finland" data-subtitle="Health and welfare system · Public health / hospital district · Several locations" data-search="finland health welfare system public health hospital district several locations national" data-image="/forum/assets/pathway-assets/finland.png" data-download="/forum/assets/pathway-assets/finland.png">
          <h3>Finland</h3>
          <div class="poster-preview has-image"><img src="/forum/assets/pathway-assets/finland.png" alt="Preview of the Finland pathway"></div>
          <div class="poster-tags">
            <span class="tag">Health and welfare system</span>
            <span class="tag peach">Public health / hospital district</span>
            <span class="tag">Several locations</span>
          </div>
        </button>
        <button class="poster-card" type="button" data-pathway-card data-tags="local justice" data-title="Scotland" data-subtitle="Child protection, justice and care system · Local authority / partnership model · Multiple locations" data-search="scotland local authority partnership child protection justice care system multiple locations" data-image="" data-download="">
          <h3>Scotland</h3>
          <div class="poster-preview"><span>Scotland pathway</span></div>
          <div class="poster-tags">
            <span class="tag">Child protection, justice and care system</span>
            <span class="tag peach">Local authority / partnership model</span>
            <span class="tag">Multiple locations</span>
          </div>
        </button>
      </div>
    </section>

  </main>

  <section class="viewer" id="pathwayViewer" role="dialog" aria-modal="true" aria-labelledby="viewerTitle" hidden>
    <div class="viewer-dialog">
      <div class="viewer-head">
        <div>
          <h2 id="viewerTitle">Pathway</h2>
          <p id="viewerSubtitle"></p>
        </div>
        <div class="viewer-nav" aria-label="Pathway navigation">
          <button class="filter-button" type="button" id="previousPathway" aria-label="Previous pathway">‹</button>
          <button class="filter-button" type="button" id="nextPathway" aria-label="Next pathway">›</button>
        </div>
        <button class="text-button" type="button" id="closeViewer">Close</button>
      </div>
      <div class="viewer-body">
        <div class="viewer-image" id="viewerImage"></div>
        <aside class="viewer-side">
          <div class="zoom-control">
            <label class="filter-label" for="viewerZoom">Zoom</label>
            <input id="viewerZoom" type="range" min="70" max="180" step="10" value="100">
          </div>
          <div class="download-stack">
            <a class="text-button" id="downloadPathway" href="#" download>Download pathway</a>
          </div>
          <div class="poster-tags" id="viewerTags"></div>
        </aside>
      </div>
    </div>
  </section>

  <script>
    const state = {
      quotes: { filter: "all", expanded: false },
      pathways: { filter: "all" },
      activePathway: 0
    };
    let lastFocusedBeforeViewer = null;

    const normalise = value => value.toLowerCase().trim();

    function setFilter(group, value) {
      state[group].filter = value;
      document.querySelectorAll(`[data-filter-group="${group}"]`).forEach(button => {
        button.setAttribute("aria-pressed", button.dataset.filter === value ? "true" : "false");
      });
      if (group === "quotes") updateQuotes();
      if (group === "pathways") updatePathways();
    }

    function matches(card, query, filter) {
      const text = normalise(card.dataset.search || card.textContent);
      const tags = (card.dataset.tags || "").split(" ");
      const matchesSearch = !query || text.includes(query);
      const matchesFilter = filter === "all" || tags.includes(filter);
      return matchesSearch && matchesFilter;
    }

    function updateQuotes() {
      const query = normalise(document.querySelector("#quoteSearch").value);
      const cards = [...document.querySelectorAll("[data-quote-card]")];
      const isFiltered = Boolean(query) || state.quotes.filter !== "all";
      let visible = 0;
      cards.forEach((card, index) => {
        const shouldShow = matches(card, query, state.quotes.filter);
        const withinCollapsedLimit = state.quotes.expanded || index < 6 || isFiltered;
        card.hidden = !(shouldShow && withinCollapsedLimit);
        if (shouldShow) visible += 1;
      });
      document.querySelector("#quoteCount").textContent = `Showing ${Math.min(visible, state.quotes.expanded || isFiltered ? visible : 6)} of ${visible}`;
      const toggle = document.querySelector("#showAllQuotes");
      toggle.hidden = isFiltered || visible <= 6;
      toggle.textContent = state.quotes.expanded ? "Show fewer good news" : "Show all good news";
    }

    function updatePathways() {
      const query = normalise(document.querySelector("#pathwaySearch").value);
      const cards = [...document.querySelectorAll("[data-pathway-card]")];
      let visible = 0;
      cards.forEach(card => {
        const shouldShow = matches(card, query, state.pathways.filter);
        card.hidden = !shouldShow;
        if (shouldShow) visible += 1;
      });
      document.querySelector("#pathwayCount").textContent = `Showing ${visible}`;
    }

    function pathwayCards() {
      return [...document.querySelectorAll("[data-pathway-card]")].filter(card => !card.hidden);
    }

    function openPathway(card, keepFocus = false) {
      const cards = pathwayCards();
      state.activePathway = Math.max(0, cards.indexOf(card));
      const image = card.dataset.image;
      const download = card.dataset.download;
      const viewer = document.querySelector("#pathwayViewer");
      const imageContainer = document.querySelector("#viewerImage");
      const tagsContainer = document.querySelector("#viewerTags");

      if (viewer.hidden) {
        lastFocusedBeforeViewer = card;
      }

      document.querySelector("#viewerTitle").textContent = card.dataset.title;
      document.querySelector("#viewerSubtitle").textContent = card.dataset.subtitle;
      imageContainer.replaceChildren();

      if (image) {
        const previewImage = document.createElement("img");
        previewImage.src = image;
        previewImage.alt = `${card.dataset.title} pathway`;
        imageContainer.appendChild(previewImage);
      } else {
        const preview = document.createElement("div");
        const label = document.createElement("span");
        preview.className = "poster-preview";
        label.textContent = `${card.dataset.title} pathway`;
        preview.appendChild(label);
        imageContainer.appendChild(preview);
      }

      tagsContainer.replaceChildren(...Array.from(card.querySelector(".poster-tags").children).map(tag => tag.cloneNode(true)));
      const downloadLink = document.querySelector("#downloadPathway");
      if (download) {
        downloadLink.href = download;
        downloadLink.removeAttribute("aria-disabled");
        downloadLink.textContent = "Download pathway";
      } else {
        downloadLink.href = "#";
        downloadLink.setAttribute("aria-disabled", "true");
        downloadLink.textContent = "Download pending";
      }
      document.querySelector("#viewerZoom").value = "100";
      imageContainer.style.setProperty("--viewer-zoom", "100%");
      viewer.hidden = false;
      document.body.classList.add("viewer-open");

      if (!keepFocus) {
        requestAnimationFrame(() => document.querySelector("#closeViewer").focus());
      }
    }

    function movePathway(direction) {
      const cards = pathwayCards();
      if (!cards.length) return;
      state.activePathway = (state.activePathway + direction + cards.length) % cards.length;
      openPathway(cards[state.activePathway], true);
    }

    function closePathwayViewer() {
      const viewer = document.querySelector("#pathwayViewer");
      viewer.hidden = true;
      document.body.classList.remove("viewer-open");

      if (lastFocusedBeforeViewer instanceof HTMLElement && lastFocusedBeforeViewer.isConnected) {
        lastFocusedBeforeViewer.focus();
      }

      lastFocusedBeforeViewer = null;
    }

    function viewerFocusableElements() {
      return Array.from(document.querySelector("#pathwayViewer").querySelectorAll("a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex='-1'])"))
        .filter(element => element.getClientRects().length);
    }

    document.querySelectorAll("[data-filter-group]").forEach(button => {
      button.addEventListener("click", () => setFilter(button.dataset.filterGroup, button.dataset.filter));
    });
    document.querySelector("#quoteSearch").addEventListener("input", updateQuotes);
    document.querySelector("#pathwaySearch").addEventListener("input", updatePathways);
    document.querySelector("#showAllQuotes").addEventListener("click", () => {
      state.quotes.expanded = !state.quotes.expanded;
      updateQuotes();
    });
    document.querySelectorAll("[data-pathway-card]").forEach(card => {
      card.addEventListener("click", () => openPathway(card));
    });
    document.querySelector("#closeViewer").addEventListener("click", () => {
      closePathwayViewer();
    });
    document.querySelector("#pathwayViewer").addEventListener("click", event => {
      if (event.target === event.currentTarget) closePathwayViewer();
    });
    document.querySelector("#previousPathway").addEventListener("click", () => movePathway(-1));
    document.querySelector("#nextPathway").addEventListener("click", () => movePathway(1));
    document.querySelector("#viewerZoom").addEventListener("input", event => {
      document.querySelector("#viewerImage").style.setProperty("--viewer-zoom", `${event.target.value}%`);
    });
    document.querySelector("#downloadPathway").addEventListener("click", event => {
      if (event.currentTarget.getAttribute("aria-disabled") === "true") event.preventDefault();
    });
    document.addEventListener("keydown", event => {
      const viewer = document.querySelector("#pathwayViewer");
      if (viewer.hidden) return;
      if (event.key === "Escape") {
        event.preventDefault();
        closePathwayViewer();
        return;
      }
      if (event.key === "Tab") {
        const focusable = viewerFocusableElements();
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
        return;
      }
      if (event.target.matches("input, textarea, select")) return;
      if (event.key === "ArrowLeft") {
        event.preventDefault();
        movePathway(-1);
      }
      if (event.key === "ArrowRight") {
        event.preventDefault();
        movePathway(1);
      }
    });

    updateQuotes();
    updatePathways();
  </script>
</body>
</html>
