<?php
if (!defined('ABSPATH')) {
    http_response_code(404);
    exit;
}

$barnahus_forum_preview_authorised = current_user_can('edit_posts');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Barnahus Forum 2026 programme</title>
  <style>
    :root {
      --ink: #16131d;
      --muted: #625f6f;
      --line: #dad5ce;
      --paper: #fbf8f4;
      --panel: #ffffff;
      --soft-blue: #eef1fb;
      --blue: #606ca5;
      --deep-blue: #3f4b86;
      --mint: #d9efe8;
      --green: #23775e;
      --amber: #f1b84b;
      --peach: #f6dfd8;
      --rust: #a23b25;
      --violet: #74608f;
      --shadow: 0 16px 40px rgba(43, 39, 55, .12);
      --radius: 8px;
      --font: "Switzer", "Inter", "Helvetica Neue", Arial, sans-serif;
    }

    * {
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      background: var(--paper);
      color: var(--ink);
      font-family: var(--font);
      font-size: 15px;
      line-height: 1.45;
      margin: 0;
    }

    button,
    input,
    select {
      font: inherit;
    }

    button {
      cursor: pointer;
    }

    .shell {
      min-height: 100vh;
    }

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
      grid-template-columns: minmax(220px, 1fr) auto auto;
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

    .tabs,
    .state-toggle {
      align-items: center;
      display: flex;
      gap: 6px;
    }

    .state-toggle[hidden] {
      display: none;
    }

    .tab,
    .state-toggle button,
    .ghost {
      background: transparent;
      border: 0;
      border-radius: 0;
      color: var(--ink);
      font-weight: 780;
      min-height: 38px;
      padding: 8px 6px;
      white-space: nowrap;
    }

    .state-toggle button,
    .ghost {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 999px;
      padding: 8px 14px;
    }

    a.tab {
      align-items: center;
      display: inline-flex;
      text-decoration: none;
    }

    .tab:hover,
    .tab:focus-visible {
      color: var(--deep-blue);
      outline: none;
    }

    .state-toggle button:hover,
    .state-toggle button:focus-visible,
    .primary:hover,
    .primary:focus-visible,
    .secondary:hover,
    .secondary:focus-visible {
      border-color: rgba(96, 108, 165, .48);
      box-shadow: 0 0 0 3px rgba(96, 108, 165, .12);
      outline: none;
    }

    .tab[aria-selected="true"] {
      box-shadow: inset 0 -3px 0 var(--deep-blue);
      color: var(--deep-blue);
    }

    .state-toggle button[aria-pressed="true"] {
      background: var(--blue);
      border-color: var(--blue);
      color: #fff;
    }

    main {
      display: grid;
      gap: 18px;
      margin: 0 auto;
      max-width: 1220px;
      padding: 18px;
    }

    .companion-grid {
      display: grid;
      gap: 18px;
      grid-template-columns: minmax(0, 1.55fr) minmax(330px, .85fr);
    }

    .moment {
      background: linear-gradient(180deg, #eef1fb 0%, #fbf8f4 100%);
      border-block: 1px solid var(--line);
      display: grid;
      gap: 34px;
      grid-template-columns: minmax(0, 1fr) minmax(260px, .72fr);
      margin-inline: calc(50% - 50vw);
      padding: 52px max(18px, calc((100vw - 1220px) / 2 + 18px));
      scroll-margin-top: 96px;
    }

    .kicker {
      color: var(--muted);
      font-size: 12px;
      font-weight: 850;
      letter-spacing: .06em;
      text-transform: uppercase;
    }

    h1,
    h2,
    h3,
    p {
      margin: 0;
    }

    h1 {
      font-family: var(--font);
      font-size: clamp(42px, 6vw, 72px);
      letter-spacing: 0;
      line-height: .96;
      margin-top: 8px;
    }

    h2 {
      font-size: 22px;
      line-height: 1.15;
    }

    h3 {
      font-size: 17px;
      line-height: 1.2;
    }

    .moment-meta {
      align-items: center;
      color: var(--muted);
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 10px;
    }

    .map-link {
      color: var(--deep-blue);
      font-weight: 850;
      text-decoration: underline;
      text-underline-offset: 3px;
    }

    .pill {
      align-items: center;
      background: var(--soft-blue);
      border: 1px solid rgba(96, 108, 165, .24);
      border-radius: 999px;
      color: var(--deep-blue);
      display: inline-flex;
      font-size: 12px;
      font-weight: 850;
      min-height: 28px;
      padding: 5px 10px;
    }

    .moment-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 16px;
    }

    .primary,
    .secondary {
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 850;
      min-height: 42px;
      padding: 10px 14px;
      text-decoration: none;
    }

    .primary {
      background: var(--blue);
      border: 1px solid var(--blue);
      color: #fff;
    }

    .secondary {
      background: #fff;
      border: 1px solid var(--line);
      color: var(--deep-blue);
    }

    .moment-side {
      align-content: start;
      display: grid;
      gap: 10px;
    }

    .status-light {
      align-items: center;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--deep-blue);
      display: inline-flex;
      font-size: 12px;
      font-style: normal;
      font-weight: 900;
      gap: 7px;
      justify-self: start;
      padding: 6px 10px;
      text-transform: uppercase;
    }

    .status-light span {
      background: var(--green);
      border-radius: 999px;
      box-shadow: 0 0 0 5px rgba(35, 119, 94, .14);
      display: block;
      height: 10px;
      width: 10px;
    }

    .status-light em {
      font-style: normal;
    }

    .status-light[data-mode="before"] span {
      background: var(--blue);
      box-shadow: 0 0 0 5px rgba(96, 108, 165, .14);
    }

    .status-light[data-mode="after"] span {
      background: var(--muted);
      box-shadow: 0 0 0 5px rgba(98, 95, 111, .12);
    }

    .status-light[data-mode="live"] span {
      animation: livePulse 1.8s ease-in-out infinite;
    }

    @keyframes livePulse {
      0%, 100% { box-shadow: 0 0 0 4px rgba(35, 119, 94, .14); }
      50% { box-shadow: 0 0 0 8px rgba(35, 119, 94, .04); }
    }

    .now-card,
    .next-card,
    .utility-card,
    .panel,
    .saved-dock {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: var(--radius);
    }

    .now-card,
    .next-card {
      padding: 12px;
    }

    .now-card {
      background: #f8faff;
      border-left: 4px solid var(--green);
    }

    .next-card {
      border-left: 4px solid var(--amber);
    }

    .card-label {
      color: var(--muted);
      font-size: 12px;
      font-weight: 850;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    .card-title {
      display: block;
      font-size: 18px;
      font-weight: 900;
      margin-top: 4px;
    }

    .card-meta {
      color: var(--muted);
      display: block;
      font-size: 13px;
      margin-top: 2px;
    }

    .saved-dock {
      align-items: center;
      box-shadow: 0 10px 26px rgba(43, 39, 55, .08);
      display: grid;
      gap: 12px;
      grid-template-columns: minmax(0, 1fr) auto;
      padding: 12px 14px;
      position: sticky;
      top: 70px;
      z-index: 10;
    }

    .saved-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: flex-end;
    }

    .saved-actions button {
      min-width: 126px;
    }

    .saved-dock strong {
      display: block;
      font-size: 14px;
    }

    .saved-dock span {
      color: var(--muted);
      display: block;
      font-size: 13px;
    }

    .saved-dock.has-clash {
      border-color: rgba(162, 59, 37, .42);
      box-shadow: 0 10px 26px rgba(162, 59, 37, .09);
    }

    .saved-dock.has-clash #savedSummary {
      color: var(--rust);
      font-weight: 850;
    }

    .publish-panel {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: var(--radius);
      display: grid;
      gap: 14px;
      grid-template-columns: minmax(190px, .5fr) minmax(0, 1fr);
      padding: 16px;
    }

    .countdown {
      align-content: center;
      background: var(--soft-blue);
      border: 1px solid rgba(96, 108, 165, .22);
      border-radius: var(--radius);
      display: grid;
      min-height: 120px;
      padding: 14px;
      text-align: center;
    }

    .countdown strong {
      color: var(--deep-blue);
      font-size: 42px;
      line-height: 1;
    }

    .countdown span {
      color: var(--muted);
      font-size: 13px;
      font-weight: 850;
      margin-top: 4px;
      text-transform: uppercase;
    }

    .member-actions {
      display: grid;
      gap: 10px;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      margin-top: 12px;
    }

    .member-action {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: var(--radius);
      color: var(--ink);
      display: grid;
      gap: 4px;
      padding: 12px;
      text-decoration: none;
    }

    .member-action:hover,
    .member-action:focus-visible {
      border-color: rgba(96, 108, 165, .46);
      box-shadow: 0 0 0 3px rgba(96, 108, 165, .12);
      outline: none;
    }

    .member-action strong {
      color: var(--deep-blue);
    }

    .member-action span {
      color: var(--muted);
      font-size: 13px;
    }

    .view {
      display: grid;
      gap: 16px;
      scroll-margin-top: 126px;
    }

    .view > div:first-child:not(.programme-layout) {
      border-bottom: 1px solid var(--line);
      display: grid;
      gap: 2px;
      padding-bottom: 10px;
    }

    .programme-layout {
      display: grid;
      gap: 18px;
      grid-template-columns: minmax(0, 1fr) minmax(340px, .72fr);
    }

    .timeline {
      display: grid;
      gap: 10px;
    }

    .time-block {
      display: grid;
      gap: 10px;
      grid-template-columns: 1fr;
    }

    .session-stack {
      display: grid;
      gap: 10px;
    }

    .time-block.parallel .session-stack {
      background: rgba(255, 255, 255, .58);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      padding: 10px;
    }

    .parallel-label {
      align-items: center;
      color: var(--muted);
      display: flex;
      flex-wrap: wrap;
      font-size: 13px;
      font-weight: 850;
      gap: 8px;
      justify-content: space-between;
      padding: 0 2px 2px;
    }

    .parallel-label strong {
      color: var(--ink);
      font-size: 14px;
    }

    .time {
      display: none;
    }

    .session-meta,
    .session-updated,
    .why-line {
      color: var(--muted);
      font-size: 13px;
    }

    .session-meta {
      font-weight: 850;
    }

    .why-line strong {
      color: var(--deep-blue);
    }

    .session {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: var(--radius);
      display: grid;
      gap: 10px;
      grid-template-columns: minmax(0, 1fr) auto;
      padding: 14px;
      text-align: left;
      width: 100%;
    }

    .session-main {
      appearance: none;
      background: transparent;
      border: 0;
      color: inherit;
      cursor: pointer;
      font: inherit;
      padding: 0;
      text-align: left;
    }

    .session.current {
      border-color: rgba(35, 119, 94, .42);
      box-shadow: inset 4px 0 0 var(--blue);
    }

    .session.saved {
      background: #f8faff;
      border-color: rgba(96, 108, 165, .42);
    }

    .session.conflict {
      background: #fff7f4;
      border-color: rgba(162, 59, 37, .56);
      box-shadow: inset 4px 0 0 var(--rust);
    }

    .session.conflict.current {
      border-color: rgba(162, 59, 37, .64);
      box-shadow: inset 4px 0 0 var(--rust);
    }

    .session.conflict .why-line strong,
    .session.conflict .session-meta {
      color: var(--rust);
    }

    .session.conflict .star {
      background: var(--rust);
      border-color: var(--rust);
      color: #fff;
    }

    .session:hover,
    .person-card:hover,
    .practical-card:hover,
    .utility-card:hover {
      border-color: rgba(96, 108, 165, .38);
      box-shadow: 0 10px 24px rgba(43, 39, 55, .08);
    }

    .session p,
    .panel p,
    .utility-card p {
      color: var(--muted);
    }

    .session-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 8px;
    }

    .room-tag {
      background: #fff;
      border-color: var(--line);
      color: var(--muted);
    }

    .star {
      align-items: center;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--deep-blue);
      display: inline-flex;
      font-size: 20px;
      font-weight: 900;
      height: 38px;
      justify-content: center;
      width: 38px;
    }

    .star:hover,
    .star:focus-visible {
      border-color: var(--blue);
      box-shadow: 0 0 0 3px rgba(96, 108, 165, .14);
      outline: none;
    }

    .session.saved .star {
      background: var(--blue);
      border-color: var(--blue);
      color: #fff;
    }

    .detail-star {
      flex: 0 0 auto;
      width: 38px;
    }

    .detail-star[aria-pressed="true"] {
      background: var(--blue);
      border-color: var(--blue);
      color: #fff;
    }

    .detail {
      align-self: start;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 18px;
      position: sticky;
      top: 140px;
    }

    .detail h2 {
      font-family: var(--font);
      font-size: 28px;
      letter-spacing: 0;
      line-height: 1.06;
      margin: 8px 0 12px;
    }

    .detail-head {
      align-items: start;
      display: grid;
      gap: 12px;
      grid-template-columns: minmax(0, 1fr) auto;
    }

    .detail-head .pill {
      justify-self: start;
    }

    .detail-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }

    .detail-actions,
    .sheet-actions {
      align-items: center;
      display: inline-flex;
      gap: 8px;
      justify-self: end;
    }

    .nav-arrow {
      align-items: center;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--deep-blue);
      display: inline-flex;
      font-size: 18px;
      font-weight: 900;
      height: 38px;
      justify-content: center;
      width: 38px;
    }

    .nav-arrow:hover,
    .nav-arrow:focus-visible {
      border-color: var(--blue);
      box-shadow: 0 0 0 3px rgba(96, 108, 165, .14);
      outline: none;
    }

    .detail-list {
      display: grid;
      gap: 8px;
      margin-top: 14px;
    }

    .detail-section {
      display: grid;
      gap: 10px;
      margin-top: 16px;
    }

    .detail-section h3 {
      font-size: 14px;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    .detail-section ul {
      margin: 0;
      padding-left: 20px;
    }

    .detail-section li + li {
      margin-top: 6px;
    }

    .detail-section p {
      color: var(--ink);
    }

    .person-strip {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 12px;
    }

    .person-chip {
      align-items: center;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--ink);
      display: inline-flex;
      gap: 8px;
      min-height: 34px;
      padding: 4px 10px 4px 4px;
    }

    button.person-chip {
      cursor: pointer;
    }

    .person-chip[aria-pressed="true"] {
      background: var(--soft-blue);
      border-color: rgba(96, 108, 165, .34);
      color: var(--deep-blue);
    }

    .speaker-note {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: var(--radius);
      color: var(--ink);
      display: none;
      gap: 14px;
      margin-top: 10px;
      padding: 14px;
    }

    .speaker-note.visible {
      display: grid;
    }

    .speaker-topline {
      align-items: center;
      display: flex;
      gap: 12px;
      justify-content: space-between;
    }

    .back-link {
      appearance: none;
      background: transparent;
      border: 0;
      color: var(--deep-blue);
      cursor: pointer;
      font: inherit;
      font-size: 14px;
      font-weight: 850;
      padding: 0;
      text-align: left;
      text-decoration: underline;
      text-underline-offset: 3px;
    }

    .back-link:hover,
    .back-link:focus-visible {
      color: var(--violet);
      outline: 2px solid rgba(96, 108, 165, .22);
      outline-offset: 3px;
    }

    .speaker-profile {
      display: grid;
      gap: 12px;
    }

    .speaker-note h4 {
      color: var(--ink);
      font-size: 24px;
      line-height: 1.08;
      margin: 0;
    }

    .speaker-note p {
      margin: 0;
    }

    .speaker-role {
      color: var(--muted);
      display: block;
      font-size: 15px;
      font-weight: 800;
      line-height: 1.35;
      margin-top: 4px;
    }

    .speaker-bio {
      display: grid;
      gap: 8px;
    }

    .speaker-nav {
      align-items: center;
      display: inline-grid;
      gap: 6px;
      grid-template-columns: auto auto auto;
      justify-self: end;
    }

    .speaker-nav button {
      align-items: center;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--deep-blue);
      display: inline-flex;
      font-size: 18px;
      font-weight: 900;
      height: 34px;
      justify-content: center;
      line-height: 1;
      padding: 0;
      width: 34px;
    }

    .speaker-nav button:hover,
    .speaker-nav button:focus-visible {
      border-color: var(--blue);
      outline: 3px solid rgba(96, 108, 165, .16);
    }

    .speaker-nav button:disabled {
      color: var(--line);
      cursor: not-allowed;
      opacity: .72;
    }

    .speaker-count {
      color: var(--muted);
      font-size: 13px;
      font-weight: 850;
      min-width: 44px;
      text-align: center;
      white-space: nowrap;
    }

    .avatar {
      align-items: center;
      background: var(--soft-blue);
      border-radius: 999px;
      color: var(--deep-blue);
      display: inline-flex;
      font-size: 11px;
      font-weight: 900;
      height: 26px;
      justify-content: center;
      width: 26px;
    }

    .people-grid,
    .practical-grid {
      display: grid;
      gap: 12px;
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .practical-layout {
      align-items: start;
      display: grid;
      gap: 14px;
      grid-template-columns: minmax(0, 1fr) minmax(320px, .72fr);
    }

    .person-card,
    .practical-card {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: var(--radius);
      display: grid;
      gap: 10px;
      padding: 14px;
    }

    .practical-card {
      align-content: start;
      min-height: 156px;
    }

    .practical-card.selected {
      border-color: rgba(96, 108, 165, .58);
      box-shadow: inset 4px 0 0 var(--blue);
    }

    .practical-card summary {
      cursor: pointer;
      display: grid;
      gap: 8px;
      grid-template-columns: minmax(0, 1fr) auto;
      list-style: none;
    }

    .practical-card summary > * {
      grid-column: 1;
    }

    .practical-card summary .pill {
      justify-self: start;
    }

    .practical-card summary::-webkit-details-marker {
      display: none;
    }

    .practical-card summary::after {
      align-items: center;
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--deep-blue);
      content: "+";
      display: inline-flex;
      font-size: 18px;
      font-weight: 900;
      grid-column: 2;
      grid-row: 1 / span 4;
      height: 30px;
      justify-content: center;
      justify-self: end;
      align-self: center;
      width: 30px;
    }

    .practical-card[open] summary::after {
      content: "-";
    }

    .practical-detail {
      border-top: 1px solid var(--line);
      display: grid;
      gap: 10px;
      margin-top: 6px;
      padding-top: 12px;
    }

    .practical-detail ul {
      margin: 0;
      padding-left: 20px;
    }

    .practical-detail li + li,
    .practical-detail p + p {
      margin-top: 8px;
    }

    .practical-selected {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: var(--radius);
      display: grid;
      gap: 12px;
      padding: 16px;
      position: sticky;
      top: 138px;
    }

    .practical-selected .practical-detail {
      border-top: 0;
      margin-top: 0;
      padding-top: 0;
    }

    .practical-selected .stripe-area {
      grid-template-columns: 1fr;
    }

    .practical-selected.open .stripe-area {
      display: grid;
    }

    .item-updated {
      color: var(--muted);
      display: block;
      font-size: 12px;
      font-weight: 800;
    }

    .person-card {
      align-content: start;
      min-height: 172px;
    }

    .person-card button,
    .practical-card button {
      justify-self: start;
    }

    .utility-grid {
      display: grid;
      gap: 12px;
      grid-template-columns: minmax(0, 1fr);
    }

    .utility-card {
      padding: 14px;
    }

    .utility-card:target,
    .practical-card:target {
      border-color: rgba(96, 108, 165, .52);
      box-shadow: 0 0 0 3px rgba(96, 108, 165, .12), 0 10px 24px rgba(43, 39, 55, .08);
      scroll-margin-top: 126px;
    }

    #notebookCard {
      background: #fff;
      border-color: rgba(241, 184, 75, .46);
    }

    .notebook-extra {
      display: grid;
      gap: 12px;
      grid-template-columns: 92px minmax(0, 1fr);
    }

    .notebook-art {
      appearance: none;
      background: transparent;
      border: 0;
      height: 72px;
      padding: 0;
      position: relative;
      width: 92px;
    }

    .notebook-page {
      background-color: #fffef9;
      background-image: radial-gradient(rgba(96, 108, 165, .58) 1.05px, transparent 1.05px);
      background-size: 8px 8px;
      border: 1px solid rgba(217, 212, 207, .94);
      border-radius: 4px;
      inset: 8px 0 2px 32px;
      position: absolute;
    }

    .notebook-cover {
      border: 1px solid rgba(54, 52, 87, .18);
      border-radius: 5px;
      box-shadow: 0 10px 22px rgba(54, 52, 87, .18);
      display: block;
      inset: 0 28px 8px 0;
      overflow: hidden;
      position: absolute;
    }

    .notebook-cover img {
      display: block;
      height: 100%;
      object-fit: cover;
      object-position: 50% 34%;
      width: 100%;
    }

    .notebook-art::after {
      align-items: center;
      background: rgba(255, 255, 255, .92);
      border-radius: 999px;
      box-shadow: 0 2px 8px rgba(54, 52, 87, .16);
      color: var(--blue);
      content: "+";
      display: flex;
      font-size: 18px;
      font-weight: 900;
      height: 22px;
      justify-content: center;
      position: absolute;
      right: 8px;
      top: 2px;
      width: 22px;
      z-index: 2;
    }

    .notebook-copy {
      align-content: center;
      display: grid;
      gap: 2px;
    }

    .notebook-copy strong {
      font-size: 15px;
    }

    .notebook-copy span {
      color: var(--muted);
      font-size: 13px;
    }

    .stripe-area {
      border-top: 1px solid var(--line);
      display: none;
      grid-column: 1 / -1;
      grid-template-columns: minmax(260px, 360px) minmax(0, 1fr);
      gap: 12px;
      padding-top: 12px;
    }

    .stripe-status {
      color: var(--muted);
      font-size: 12px;
      font-weight: 750;
      margin: 0;
    }

    .stripe-status.error {
      color: var(--rust);
    }

    #notebookCard.open .stripe-area {
      display: grid;
    }

    .notice {
      background: var(--peach);
      border: 1px solid rgba(162, 59, 37, .24);
      border-radius: var(--radius);
      color: var(--rust);
      font-weight: 800;
      padding: 10px;
    }

    .challenge-head {
      align-items: center;
      display: flex;
      gap: 10px;
      justify-content: space-between;
      margin-top: 10px;
    }

    .challenge-progress {
      color: var(--deep-blue);
      font-size: 13px;
      font-weight: 900;
    }

    .challenge-status {
      align-items: center;
      border-radius: var(--radius);
      display: none;
      gap: 10px;
      grid-template-columns: auto 1fr;
      margin-top: 10px;
      padding: 10px;
    }

    .challenge-status.visible {
      display: grid;
    }

    .challenge-status strong {
      display: block;
      line-height: 1.2;
    }

    .challenge-status p {
      margin: 2px 0 0;
    }

    .challenge-icon {
      align-items: center;
      color: var(--blue);
      display: inline-flex;
      height: 40px;
      justify-content: center;
      width: 40px;
    }

    .challenge-icon svg {
      display: block;
      fill: currentColor;
      height: 100%;
      width: 100%;
    }

    .connection-status {
      background: #fff;
      border: 1px solid rgba(96, 108, 165, .24);
      color: var(--deep-blue);
    }

    .challenge-grid {
      display: grid;
      gap: 8px;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      margin-top: 10px;
    }

    .challenge {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 8px;
      min-height: 104px;
      padding: 10px;
      text-align: left;
    }

    .challenge.done {
      background: var(--mint);
      border-color: rgba(35, 119, 94, .34);
    }

    #networkingCard[hidden] {
      display: none;
    }

    .quiet-link {
      appearance: none;
      background: transparent;
      border: 0;
      color: var(--deep-blue);
      cursor: pointer;
      font: inherit;
      font-weight: 850;
      padding: 0;
      text-decoration: underline;
      text-underline-offset: 3px;
    }

    .practical-card .quiet-link {
      justify-self: start;
      margin-top: 2px;
    }

    .challenge strong {
      display: block;
      font-size: 13px;
      margin-bottom: 4px;
    }

    .challenge span {
      color: var(--muted);
      display: block;
      font-size: 12px;
      line-height: 1.3;
    }

    .ambassador {
      background: var(--mint);
      border: 1px solid rgba(35, 119, 94, .32);
      color: var(--green);
    }

    dialog {
      background: transparent;
      border: 0;
      max-width: min(900px, calc(100vw - 32px));
      padding: 0;
      width: 100%;
    }

    dialog::backdrop {
      background: rgba(33, 31, 46, .55);
    }

    .modal-card {
      background: #fff;
      border-radius: var(--radius);
      display: grid;
      gap: 16px;
      grid-template-columns: minmax(260px, 1fr) minmax(180px, .48fr);
      padding: 18px;
      position: relative;
    }

    .modal-card img,
    .modal-dotgrid {
      border-radius: 6px;
      height: min(66vh, 640px);
      width: 100%;
    }

    .modal-card img {
      object-fit: cover;
      object-position: 50% 38%;
    }

    .modal-dotgrid {
      background-color: #fffef9;
      background-image: radial-gradient(rgba(96, 108, 165, .62) 1.4px, transparent 1.4px);
      background-size: 16px 16px;
      border: 1px solid rgba(217, 212, 207, .9);
    }

    .modal-close {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 999px;
      font-weight: 850;
      padding: 8px 12px;
      position: absolute;
      right: 14px;
      top: 14px;
    }

    body.sheet-open {
      overflow: hidden;
    }

    .bottom-sheet {
      display: none;
    }

    .print-programme {
      display: none;
    }

    .sheet-panel {
      background: #fff;
    }

    .sheet-handle {
      background: rgba(104, 98, 115, .3);
      border-radius: 999px;
      height: 4px;
      justify-self: center;
      width: 48px;
    }

    .sheet-top {
      align-items: center;
      display: flex;
      gap: 10px;
      justify-content: space-between;
    }

    .sheet-title {
      font-family: var(--font);
      font-size: 25px;
      letter-spacing: 0;
      line-height: 1.08;
    }

    .sheet-summary {
      color: var(--muted);
    }

    .sheet-close {
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--deep-blue);
      cursor: pointer;
      font: inherit;
      font-weight: 850;
      padding: 8px 12px;
    }

    .sheet-content {
      display: grid;
      gap: 12px;
    }

    .sheet-content .stripe-area {
      grid-template-columns: 1fr;
    }

    .sheet-content.open .stripe-area {
      display: grid;
    }

    .hidden {
      display: none !important;
    }

    @media (max-width: 980px) {
      .topbar-inner,
      .companion-grid,
      .programme-layout,
      .moment,
      .utility-grid,
      .publish-panel,
      .practical-layout {
        grid-template-columns: 1fr;
      }

      .topbar-inner {
        align-items: start;
      }

      .tabs,
      .state-toggle {
        overflow-x: auto;
        padding-bottom: 2px;
      }

      .saved-dock,
      .detail {
        position: static;
      }

      .programme-layout > .detail {
        display: none;
      }

      .practical-selected {
        display: none;
      }

      .people-grid,
      .practical-grid,
      .member-actions {
        grid-template-columns: 1fr;
      }

      .bottom-sheet {
        align-items: end;
        background: rgba(33, 31, 46, .42);
        display: flex;
        inset: 0;
        opacity: 0;
        pointer-events: none;
        position: fixed;
        transition: opacity .18s ease;
        z-index: 80;
      }

      .bottom-sheet[hidden] {
        display: none;
      }

      .bottom-sheet.open {
        opacity: 1;
        pointer-events: auto;
      }

      .sheet-panel {
        border: 1px solid var(--line);
        border-radius: 18px 18px 0 0;
        box-shadow: 0 -18px 42px rgba(43, 39, 55, .18);
        display: grid;
        gap: 12px;
        max-height: min(84vh, 720px);
        overflow: auto;
        padding: 12px 16px 18px;
        transform: translateY(24px);
        transition: transform .18s ease;
        width: 100%;
      }

      .bottom-sheet.open .sheet-panel {
        transform: translateY(0);
      }
    }

    @media (max-width: 640px) {
      main {
        padding: 12px;
      }

      .moment {
        padding: 14px;
      }

      .saved-dock {
        grid-template-columns: 1fr;
      }

      .saved-actions {
        justify-content: stretch;
      }

      .saved-actions button {
        flex: 1 1 150px;
      }

      .time-block {
        grid-template-columns: 1fr;
      }

      .time {
        padding-top: 0;
      }

      .session {
        grid-template-columns: 1fr;
      }

      .notebook-extra,
      .stripe-area,
      .modal-card,
      .challenge-grid {
        grid-template-columns: 1fr;
      }

      .modal-card img,
      .modal-dotgrid {
        height: auto;
        min-height: 220px;
      }
    }

    @media (prefers-reduced-motion: reduce) {
      html {
        scroll-behavior: auto;
      }

      *,
      *::before,
      *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
      }
    }

    @media print {
      @page {
        size: A4;
        margin: 14mm;
      }

      body {
        background: #fff;
        color: #111;
        font-size: 11pt;
      }

      .topbar,
      .moment,
      .publish-panel,
      .saved-dock,
      #view-programme,
      #view-practical,
      .bottom-sheet,
      dialog {
        display: none !important;
      }

      main {
        display: block;
        max-width: none;
        padding: 0;
      }

      .print-programme {
        display: block;
      }

      .print-programme h1 {
        font-family: var(--font);
        font-size: 25pt;
        line-height: 1.05;
        margin: 0 0 5mm;
      }

      .print-meta,
      .print-updated {
        color: #555;
        font-size: 9.5pt;
      }

      .print-section {
        margin-top: 8mm;
      }

      .print-section h2 {
        border-bottom: 1px solid #bbb;
        font-size: 15pt;
        margin-bottom: 4mm;
        padding-bottom: 2mm;
      }

      .print-session,
      .print-practical {
        break-inside: avoid;
        border: 1px solid #d7d2cb;
        border-radius: 4px;
        margin: 0 0 4mm;
        padding: 4mm;
      }

      .print-session h3,
      .print-practical h3 {
        font-size: 12.5pt;
        margin: 1mm 0 2mm;
      }

      .print-session p,
      .print-practical p,
      .print-session li,
      .print-practical li {
        font-size: 10pt;
      }

      .print-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 2mm;
        margin-top: 2mm;
      }

      .print-tags span {
        border: 1px solid #ccc;
        border-radius: 999px;
        color: #444;
        font-size: 8.5pt;
        padding: 1mm 2mm;
      }
    }
  </style>
</head>
<body data-preview-authorised="<?php echo $barnahus_forum_preview_authorised ? '1' : '0'; ?>">
  <div class="shell">
    <header class="topbar">
      <div class="topbar-inner">
        <div class="brand">
          <strong>Barnahus Forum 2026</strong>
          <span>24 November - Hamburg</span>
        </div>
        <nav class="tabs" aria-label="Main sections">
          <a class="tab" href="#view-programme" data-anchor-tab="programme" aria-selected="true">Programme</a>
          <a class="tab" href="#view-practical" data-anchor-tab="practical" aria-selected="false">Practical</a>
          <a class="tab" href="/forum/participants">Participants</a>
        </nav>
        <div class="state-toggle" aria-label="Preview state" hidden>
          <button type="button" data-state="before" aria-pressed="true">Before</button>
          <button type="button" data-state="live" aria-pressed="false">Forum day</button>
          <button type="button" data-state="after" aria-pressed="false">After</button>
        </div>
      </div>
    </header>

    <main>
      <section class="moment" aria-labelledby="pageTitle">
        <div>
          <div class="kicker" id="stateLabel">Programme published</div>
          <h1 id="pageTitle">Barnahus Forum 2026 programme</h1>
          <div class="moment-meta">
            <span id="dateLabel">Tuesday 24 November 2026</span>
            <a class="map-link" id="venueLink" href="https://www.google.com/maps/place/Nord+Event+Panoramadeck/@53.5561899,9.9785809,740m/data=!3m3!1e3!4b1!5s0x47b18f21d3af575d:0xeab10e4e1eb5c6bf!4m6!3m5!1s0x4163bcb34dc214d7:0x700a7fef23e00f7a!8m2!3d53.55619!4d9.9834518!16s%2Fg%2F1hc1wqf9p?entry=ttu" target="_blank" rel="noopener">NordEvent Panoramadeck, Emporio, Dammtorwall 15, Hamburg</a>
            <span class="pill" id="lastUpdated">Last updated 11 July 2026</span>
          </div>
          <div class="moment-actions">
            <button class="primary" type="button" data-primary-action>Explore programme</button>
            <button class="secondary" type="button" data-jump-view="practical">Open practical info</button>
            <button class="secondary" type="button" data-add-calendar>Add to calendar</button>
          </div>
        </div>
        <div class="moment-side">
          <div class="status-light hidden" id="statusLight" data-mode="before">
            <span aria-hidden="true"></span>
            <em>Programme published</em>
          </div>
          <div class="now-card">
            <span class="card-label">Now</span>
            <strong class="card-title" id="nowTitle">Opening plenary</strong>
            <span class="card-meta" id="nowMeta">09:30-10:30 - Ballroom</span>
          </div>
          <div class="next-card">
            <span class="card-label">Next</span>
            <strong class="card-title" id="nextTitle">Breakout session 1</strong>
            <span class="card-meta" id="nextMeta">11:00-12:30 - choose one track</span>
          </div>
        </div>
      </section>

      <section class="publish-panel" id="publishedToday" aria-labelledby="publishedTodayHeading">
        <div class="countdown">
          <strong id="countdownDays">136</strong>
          <span>days to the Forum</span>
        </div>
        <div>
          <div class="kicker">Programme published</div>
          <h2 id="publishedTodayHeading">Members can start shaping the Forum now</h2>
          <p>The programme is still taking shape. Members are invited to submit session proposals and share their Show me your pathway material so the Forum reflects what is happening across the Network.</p>
          <span class="item-updated">Last updated 11 July 2026</span>
          <div class="member-actions">
            <a class="member-action" href="#submit-session-proposal">
              <strong>Submit a session proposal</strong>
              <span>Suggest a workshop, panel, practical exchange, or peer learning moment.</span>
            </a>
            <a class="member-action" href="#show-me-your-pathway">
              <strong>Show me your pathway</strong>
              <span>Find the pathway prompt without hunting through the programme.</span>
            </a>
          </div>
        </div>
      </section>

      <section class="saved-dock" aria-live="polite">
        <div>
          <strong id="savedTitle">No saved breakout sessions yet</strong>
          <span id="savedSummary">Save sessions to build a personal programme and spot clashes.</span>
        </div>
        <div class="saved-actions">
          <button class="secondary" type="button" data-show-saved aria-pressed="false">Show saved</button>
          <button class="secondary" type="button" data-email-programme>Email my programme</button>
          <button class="secondary" type="button" data-print-programme>Print</button>
          <button class="primary" type="button" data-copy-link>Copy link</button>
        </div>
      </section>

      <section class="view" id="view-programme" aria-labelledby="programmeHeading">
        <div class="programme-layout">
          <div class="timeline" id="timeline">
            <div>
              <div class="kicker">Programme</div>
              <h2 id="programmeHeading">Tuesday 24 November</h2>
            </div>
          </div>
          <aside class="detail" id="detail" aria-live="polite"></aside>
        </div>
      </section>

      <section class="view" id="view-practical" aria-labelledby="practicalHeading">
        <div>
          <div class="kicker">Practical</div>
          <h2 id="practicalHeading">What participants need during the Forum</h2>
        </div>
        <div class="practical-layout">
        <div class="practical-grid">
          <details class="practical-card" id="secondary-traumatisation" data-updated="11 July 2026">
            <summary>
              <span class="pill">Care</span>
              <h3>Preventing secondary traumatisation</h3>
              <p>How we keep heavy discussions safe and constructive.</p>
              <span class="item-updated">Last updated 11 July 2026</span>
            </summary>
            <div class="practical-detail">
              <p>Some discussions may involve cases of violence against children. Participants are asked to help create an environment that is safe and constructive for everyone.</p>
              <p>When discussing specific cases, speakers and participants should limit details strictly to those necessary for learning, reflection, and advancing Barnahus practice. Before presenting or privately discussing potentially disturbing case details, please provide a clear warning so colleagues can make an informed decision about their level of engagement.</p>
              <ul>
                <li>Keep case details relevant and necessary.</li>
                <li>Give health warnings before potentially disturbing details.</li>
                <li>Speak with an organiser if you need additional support.</li>
              </ul>
              <button class="quiet-link" type="button" data-open-game>Open Networking challenge</button>
            </div>
          </details>
          <details class="practical-card" id="child-safeguarding" data-updated="11 July 2026">
            <summary>
              <span class="pill">Policy</span>
              <h3>Child safeguarding</h3>
              <p>Confidentiality and child-safety expectations during the Forum.</p>
              <span class="item-updated">Last updated 11 July 2026</span>
            </summary>
            <div class="practical-detail">
              <p>Please keep all information regarding the identity of any child and the circumstances of any case discussed at the Forum confidential.</p>
              <p>If presented during any part of the Forum, it is forbidden to take photos or videos of media pertaining to specific cases, for example evidence of physical violence or recordings of example interviews.</p>
              <p>In anticipation that children might be present at the conference, participants are expected to read and follow the Network's safeguarding policy. Potential infraction of the policy in connection to the Forum should be notified to the organisers.</p>
              <ul>
                <li>Do not share identifying information about children or families.</li>
                <li>Do not photograph or record case material.</li>
                <li>Raise any safeguarding concern with the organising team.</li>
              </ul>
            </div>
          </details>
          <details class="practical-card" id="photography" data-updated="11 July 2026">
            <summary>
              <span class="pill">Policy</span>
              <h3>Photography</h3>
              <p>Photography, recording, livestreaming, and respectful image use.</p>
              <span class="item-updated">Last updated 11 July 2026</span>
            </summary>
            <div class="practical-detail">
              <p>The organisers will be taking photos and videos during the conference. Sessions in the plenary room may be recorded or live streamed.</p>
              <p>The organisers reserve the right to use pictures and videos taken during the Forum on social media and/or other communication materials. We are not responsible for individual attendee use of your image or likeness.</p>
              <p>We ask participants to neither take nor post photos of children who are at the event unless given explicit consent. Even with consent, please carefully consider the ethics and risks involved.</p>
              <ul>
                <li>Speak with organisers if you do not want to appear in event photos.</li>
                <li>Do not take or post photos of children unless explicit consent has been arranged.</li>
                <li>Respect any no-photo guidance given in a session.</li>
              </ul>
            </div>
          </details>
          <details class="practical-card" id="submit-session-proposal" data-updated="11 July 2026">
            <summary>
              <span class="pill">Members</span>
              <h3>Submit a session proposal</h3>
              <p>Suggest a workshop, panel, practical exchange, or peer learning moment.</p>
              <span class="item-updated">Last updated 11 July 2026</span>
            </summary>
            <div class="practical-detail">
              <p>Members are invited to propose sessions that help peers learn from practice: workshops, implementation stories, tools, panels, or honest problem-solving exchanges.</p>
              <a class="secondary" href="mailto:svb@barnahus.eu?subject=Barnahus%20Forum%20session%20proposal">Submit your proposal</a>
            </div>
          </details>
          <details class="practical-card" id="show-me-your-pathway" data-updated="11 July 2026">
            <summary>
              <span class="pill">Members</span>
              <h3>Show me your pathway</h3>
              <p>Share a pathway so other members can learn from your context.</p>
              <span class="item-updated">Last updated 11 July 2026</span>
            </summary>
            <div class="practical-detail">
              <p>Share the pathway children and families move through in your context, including what works, where coordination is difficult, and what others could learn from your experience.</p>
              <a class="secondary" href="mailto:svb@barnahus.eu?subject=Show%20me%20your%20pathway">Submit your pathway</a>
            </div>
          </details>
          <details class="practical-card" id="notebookCard" data-updated="11 July 2026">
            <summary>
              <span class="pill" id="notebookStatePill">Forum extra</span>
              <h3>Buy a notebook</h3>
              <p id="notebookCopy">Buy a branded dot-grid notebook. Collect it at the Forum. (Attention: shipping is not available.)</p>
              <span class="item-updated">Last updated 11 July 2026</span>
            </summary>
            <div class="practical-detail">
              <div class="notebook-extra">
                <button class="notebook-art" type="button" data-open-notebook aria-label="Inspect the Barnahus Network notebook">
                  <span class="notebook-page" aria-hidden="true"></span>
                  <span class="notebook-cover" aria-hidden="true">
                    <img src="/forum/assets/notebook/forum-notebook-cover.jpg" alt="">
                  </span>
                </button>
                <div class="notebook-copy">
                  <strong>Barnahus Network dot-grid notebook</strong>
                  <span>Payment is handled securely by Stripe. The notebook is for collection at the Forum only. Shipping is not available.</span>
                </div>
              </div>
              <button class="secondary" type="button" id="notebookToggle">Buy</button>
              <div class="stripe-area">
                <p class="stripe-status" data-stripe-status aria-live="polite">Select Buy to load Stripe's secure payment options.</p>
                <stripe-buy-button
                  buy-button-id="buy_btn_1TqeYd53F10x7fDLQJZwGF4m"
                  publishable-key="pk_live_51TUk6p53F10x7fDLxCqYIZkSH8T6trtvKxQ6BMX9WeBHEAc6Ic0mLgZ9BNi8O2hNcPzXXnqo6HnlNqSWkzHpqM4Q00Hne4lJNA">
                </stripe-buy-button>
              </div>
            </div>
          </details>
        </div>
        <aside class="practical-selected" id="practicalDetail" aria-live="polite"></aside>
        </div>

        <div class="utility-grid">
          <article class="utility-card" id="networkingCard" data-updated="11 July 2026" hidden>
            <span class="pill">Networking</span>
            <h3>Networking challenge</h3>
            <p id="networkingText">Open during the Forum day. Complete five prompts to unlock Ambassador status.</p>
            <span class="item-updated">Last updated 11 July 2026</span>
            <div class="challenge-head">
              <span class="challenge-progress" id="challengeProgress">0 of 9 complete</span>
              <button class="secondary" type="button" data-copy-game-link>Copy game link</button>
            </div>
            <div class="challenge-status connection-status" id="connectionNotice">
              <span class="challenge-icon" aria-hidden="true">
                <svg viewBox="-5 -10 110 110" focusable="false">
                  <g>
                    <path d="m42.652 84.703c0.65625 0.39062 1.4258 0.54688 2.1836 0.44531 0.37891-0.070313 0.74219-0.20313 1.0781-0.39063 0.53516-0.37109 1.0039-0.82422 1.3945-1.3398 0.34375-0.36719 0.65625-0.76562 0.93359-1.1875 0.42187-0.73438 0.53125-1.6055 0.3125-2.4219-0.42969-1.6289-1.9883-2.6953-3.6602-2.5039-1.1602 0.26953-1.7852 0.89453-2.5469 1.7852-0.32031 0.33203-0.61328 0.69141-0.87109 1.0781-0.4375 0.73828-0.57812 1.6172-0.39453 2.4531 0.23438 0.87109 0.79688 1.6172 1.5703 2.082z"/>
                    <path d="m30.816 66.145c-0.14844-0.96875-0.67578-1.8359-1.4648-2.4141-0.33984-0.24609-0.72656-0.42188-1.1367-0.51562l-0.09375-0.027344c-0.41016-0.085938-0.83594-0.10547-1.2539-0.058594-0.63281 0.09375-1.2266 0.38281-1.6875 0.82812-0.28906 0.33984-0.55078 0.69531-0.78125 1.0742l-0.89453 1.2461c-0.11719 0.17578-0.20703 0.36719-0.26953 0.57031l-0.03125 0.085938c-0.18359 0.78906-0.054687 1.6211 0.36328 2.3164 0.5 0.86719 1.3125 1.5078 2.2734 1.7891 0.4375 0.10938 0.89062 0.14453 1.3398 0.10938 0.66406-0.070313 1.2656-0.42578 1.6484-0.97266l0.97656-1.3398c0.65234-0.82031 1.1992-1.5898 1.0117-2.6914z"/>
                    <path d="m36.453 79.863c0.61328 0.41797 1.3516 0.62109 2.0938 0.57031 0.55469-0.066406 1.0859-0.26172 1.5547-0.56641 0.50391-0.41797 0.95313-0.89844 1.3398-1.4297 0.34375-0.41016 0.71484-0.8125 1.0312-1.25 0.46875-0.67578 0.65625-1.5039 0.52734-2.3125-0.30859-1.6367-1.7891-2.7852-3.4531-2.6797-0.46094 0.042969-0.90234 0.18359-1.3047 0.41406-0.53906 0.39844-1.0117 0.87891-1.3984 1.4258l-1.3672 1.6953h0.003907c-0.38672 0.47656-0.54688 1.0938-0.44922 1.6992 0.13281 0.96484 0.64453 1.8398 1.4219 2.4336z"/>
                    <path d="m30.48 75.219c0.73828 0.51953 1.625 0.78125 2.5234 0.75 0.44531-0.054688 0.86719-0.20703 1.2461-0.44922 0.47266-0.39453 0.87891-0.86328 1.2031-1.3906l1.2656-1.7344c0.37891-0.55078 0.50391-1.2383 0.34375-1.8867-0.18359-0.92578-0.71484-1.7422-1.4844-2.2812-0.67969-0.45312-1.4883-0.66797-2.3008-0.61719-0.83203 0.050781-1.5977 0.48047-2.0742 1.1641l-1.2422 1.7344h0.003906c-0.31641 0.37109-0.58594 0.78125-0.80078 1.2188-0.13672 0.33984-0.17969 0.70703-0.125 1.0703 0.14453 0.96484 0.66016 1.8359 1.4414 2.4219z"/>
                    <path d="m65.988 28.371-0.10547 0.023437c-1.2539 0.28906-2.8125 0-4.0781-0.14453-1.7188-0.19531-3.543-0.47266-5.2695-0.32422-2.1172 0.11719-4.3203 0.44531-6.2266 1.4336h-0.003906c-0.79688 0.39844-1.543 0.88672-2.2305 1.4531-0.55859 0.47266-1.0625 1.0195-1.582 1.5352l-2.582 2.5781-4.1602 4.0781c-0.75781 0.74609-1.5898 1.4766-2.2969 2.2734-0.73438 0.79297-1.0938 1.8633-0.98828 2.9414 0.070312 0.94531 0.50781 1.8242 1.2227 2.4414 0.67969 0.57422 1.5664 0.83594 2.4453 0.72656 0.59766-0.078125 1.168-0.28516 1.6758-0.60938 1.2891-0.78516 2.4688-1.9336 3.625-2.9102l5.207-4.2812 0.89453-0.76563c0.074219-0.074218 0.15625-0.14062 0.24609-0.20312 0 0 0.03125 0 0.042969 0.03125l0.23047 0.15625 11.496 7.8203 9.1562 6.2539 3.6641 2.5c0.6875 0.53906 1.4141 1.0312 2.168 1.4727l3.125-2.8633-14.754-25.949c-0.29688 0.14063-0.60547 0.25391-0.92188 0.33203z"/>
                    <path d="m1.1523 47.887c0.39062 0.32422 0.80859 0.61719 1.25 0.87109l2.1133 1.3398 8.3438 5.2695 0.5-0.89453 18.02-30.691-14.113-8.8906-11.574 19.703-3.9375 6.6992h-0.003906c-0.56641 0.85938-1.082 1.7539-1.5352 2.6797-0.25391 0.71094-0.28516 1.4766-0.09375 2.207 0.16797 0.65625 0.52734 1.25 1.0312 1.707z"/>
                    <path d="m99.824 46.352c0.31641-0.98437 0.19531-2.0586-0.32422-2.9531l-16.43-28.617-0.13672 0.027344-14.188 8.8086 17.738 31.191 0.29297 0.51953 2.3984-1.4297 4.1641-2.5312 2.9961-1.8203c0.89062-0.47266 1.7344-1.0234 2.5234-1.6484 0.45703-0.41797 0.78906-0.95313 0.96484-1.5469z"/>
                    <path d="m35.281 40.109c0.9375-1.1602 2.1602-2.1562 3.2305-3.2109l4.6992-4.6328c1.1211-1.1641 2.3008-2.2734 3.5352-3.3164 0.41797-0.35938 0.86719-0.67969 1.3398-0.96094l0.44531-0.24219h-0.5625c-1.5391 0.097656-3.0742 0.28516-4.5938 0.57422-2.7344 0.53516-5.6719 1.5156-8.4531 0.78125-0.60547-0.17969-1.1875-0.41016-1.7461-0.69922l-15.312 26.27 6.5 7.5938c0.058594 0.066406 0.042969 0.082031 0.13281 0.085937 0.88672-0.80078 2.0625-1.2031 3.25-1.1211 1.4453 0.11328 2.7812 0.79688 3.7227 1.8984 0.63281 0.73047 1.0273 1.6367 1.1328 2.5977 1.4219-0.28125 2.8984 0.035157 4.0781 0.875 1.1836 0.83984 1.9688 2.125 2.1758 3.5625l0.03125 0.41016c1.1914-0.33984 2.4727-0.17578 3.543 0.45312 1.1875 0.69531 2.0469 1.8398 2.3867 3.1758 0.11719 0.47656 0.17578 0.96484 0.17578 1.457 1.4336 0.027344 2.793 0.625 3.7812 1.6641 0.83594 0.82812 1.3086 1.9531 1.3125 3.1289 0 0.24609-0.066407 0.49609-0.054688 0.74609l2.9102 2.1719 0.003906-0.003906c0.70703 0.60937 1.4766 1.1367 2.2969 1.582 0.51562 0.21875 1.082 0.30078 1.6367 0.23047 0.91406-0.11328 1.7422-0.59766 2.2891-1.3398 0.51562-0.68359 0.73047-1.5508 0.59766-2.4023-0.19141-1.1055-0.83984-2.0781-1.7852-2.6797l-4.582-3.2461h-0.003907c-0.90625-0.5625-1.7773-1.1914-2.5977-1.8789-0.28125-0.23828-0.48047-0.55859-0.57031-0.91797-0.046874-0.26562 0.011719-0.54297 0.16406-0.76953 0.20312-0.29687 0.53516-0.48437 0.89453-0.51172 0.28516-0.046875 0.57812-0.007813 0.83984 0.11328 0.83984 0.45703 1.6328 1 2.3672 1.6172l5.5273 4.0195 1.8828 1.3633h0.003906c0.46484 0.35938 0.94922 0.69922 1.4492 1.0117 0.27734 0.16406 0.57812 0.27734 0.89453 0.33203h0.085938-0.003907c0.84766 0.11719 1.707-0.10156 2.3906-0.61328 0.71484-0.5 1.207-1.2578 1.3633-2.1172 0.15625-0.83984-0.050782-1.7109-0.57031-2.3906-0.41797-0.4375-0.89453-0.81641-1.418-1.125l-2.4023-1.6719-6.8203-4.8828c-0.70703-0.50391-1.7539-1.0508-1.8945-1.9844v0.003906c-0.03125-0.30078 0.058593-0.60156 0.24609-0.83984 0.22266-0.26562 0.54688-0.42969 0.89453-0.44531 0.32422 0 0.64062 0.085937 0.91797 0.24609 0.375 0.21094 0.72656 0.49219 1.0859 0.74219l2.2344 1.6133 6.7734 4.6875 1.6172 1.1211c0.54297 0.44531 1.1602 0.78906 1.8281 1.0039 1.0586 0.22656 2.1562-0.11328 2.9062-0.89062 0.66797-0.64844 1.043-1.543 1.043-2.4727-0.042969-0.79297-0.39062-1.543-0.97656-2.0859-1.0703-0.84766-2.1875-1.6367-3.3438-2.3594l-6.6562-4.6445-1.6758-1.1602 0.003906-0.003906c-0.38281-0.23438-0.75-0.5-1.0898-0.79297-0.27734-0.26172-0.44922-0.61328-0.49609-0.98828-0.027344-0.32812 0.089843-0.65625 0.32031-0.89453 0.23828-0.23437 0.55859-0.36328 0.89453-0.35547 0.38672 0.023437 0.75781 0.15234 1.0742 0.375 1.0781 0.63672 2.0859 1.4766 3.125 2.1914l6.8047 4.6445 1.8359 1.2539 0.003906 0.003906c0.41016 0.31641 0.88281 0.55078 1.3828 0.68359 0.81641 0.13672 1.6875-0.35156 2.2656-0.89453 0.78125-0.71875 1.2617-1.7109 1.3398-2.7695 0.019531-0.91406-0.33594-1.7969-0.98438-2.4414-0.53906-0.46875-1.1172-0.89453-1.7266-1.2734l-3.8516-2.6484-7.5156-5.1367-13.09-8.8516c-0.54688 0.37891-1.0664 0.79297-1.5586 1.2422l-3.7578 2.8945-2.1602 1.6836c-0.85156 0.75-1.8008 1.3789-2.8242 1.8711-1.1523 0.48047-2.4297 0.58203-3.6406 0.28125-1.4336-0.38281-2.6562-1.3203-3.3945-2.6094-0.85938-1.4844-1.0898-3.2539-0.63281-4.9141 0.21875-0.75391 0.58594-1.457 1.0781-2.0664z"/>
                  </g>
                </svg>
              </span>
              <div>
                <strong>Connections made</strong>
                <p>Keep going to complete all 9 challenges.</p>
              </div>
            </div>
            <div class="challenge-status ambassador" id="ambassadorNotice">
              <span class="challenge-icon" aria-hidden="true">
                <svg viewBox="-5 -10 110 110" focusable="false">
                  <path d="m60.09 67.945c0.8125 0.089844 1.6211 0.18359 2.4336 0.29297 0.007812 0 0.015624 0 0.019531 0.003907 4.9688 0.73047 8.8008 4.7461 9.2969 9.7422 0.26172 2.6445 0.54297 5.4688 0.71484 7.3672 0.20703 2.0508-0.96094 3.9961-2.8672 4.7812-0.007812 0.003907-0.015625 0.007813-0.023438 0.007813-12.051 4.7578-26.625 4.8594-39.277-0.003906-0.003907 0-0.011719-0.003907-0.019531-0.007813-1.918-0.76562-3.1055-2.6953-2.9297-4.75 0.16016-1.8828 0.39453-4.6641 0.66406-7.2773 0.43359-5.0273 4.2695-9.1016 9.2656-9.8359h0.015624c0.76172-0.10547 1.5273-0.19922 2.2891-0.28516 2.5234 2.9453 6.1719 4.7891 10.195 4.7891 4.0391 0 7.6992-1.8555 10.223-4.8242zm-10.223-22.578c6.6836 0 12.164 5.7617 12.164 12.922 0 7.1602-5.4805 12.918-12.164 12.918-6.6797 0-12.164-5.7578-12.164-12.918 0-7.1602 5.4844-12.922 12.164-12.922zm21.594 26.918c1.0273 0.30469 2.1133 0.46875 3.2305 0.46875 3.7461 0 7.125-1.8359 9.3203-4.7266 4.957 0.73047 8.793 4.7422 9.2852 9.7422 0.16406 1.6328 0.32422 3.25 0.42969 4.5039 0.19922 2.0586-0.98437 4.0039-2.9023 4.7773-0.007813 0.003907-0.015625 0.003907-0.027344 0.007813-5.6562 2.1641-11.953 3.1094-18.246 2.7969l0.70312-0.83594 0.73047-1.8164 0.14453-0.99219-0.015625-1.0156c-0.17578-1.8867-0.45703-4.7148-0.71875-7.3633-0.19922-2.0312-0.88281-3.918-1.9336-5.5469zm3.2305-23.086c5.6914 0 10.367 4.8984 10.367 10.996 0 6.0977-4.6758 10.996-10.367 10.996s-10.371-4.8984-10.371-10.996c0-6.0977 4.6797-10.996 10.371-10.996zm-47.32 40.652c-6.2305 0.32422-12.621-0.59375-18.527-2.793-0.007812-0.003906-0.015625-0.007813-0.023438-0.011719-1.9336-0.75391-3.1406-2.6953-2.9648-4.7617 0.10547-1.2266 0.23828-2.8086 0.43359-4.4062 0.4375-5 4.2344-9.0391 9.1719-9.8008 2.1992 2.8594 5.5586 4.6758 9.2891 4.6758 1.3516 0 2.6602-0.23828 3.8789-0.68359-1.1523 1.707-1.8945 3.7188-2.0859 5.8828-0.26562 2.6211-0.50391 5.4062-0.66406 7.293-0.14453 1.7148 0.42188 3.3633 1.4922 4.6055zm-2.6211-40.652c5.6875 0 10.363 4.8984 10.363 10.996 0 6.0977-4.6758 10.996-10.363 10.996-5.6953 0-10.371-4.8984-10.371-10.996 0-6.0977 4.6758-10.996 10.371-10.996zm26.734-41.871 3.0078 9.2578h9.7305c0.67969 0 1.2773 0.43359 1.4883 1.0781 0.21094 0.64453-0.023438 1.3477-0.56641 1.7461l-7.875 5.7227 3.0078 9.2539c0.21094 0.64453-0.023438 1.3477-0.56641 1.7461-0.55078 0.39844-1.2891 0.39844-1.8398 0l-7.8711-5.7227-7.8711 5.7227c-0.55078 0.39844-1.2891 0.39844-1.8398 0-0.54687-0.39844-0.77734-1.1016-0.56641-1.7461l3.0078-9.2539-7.875-5.7227c-0.54688-0.39844-0.77734-1.1016-0.56641-1.7461 0.21094-0.64453 0.80859-1.0781 1.4883-1.0781h9.7305l3.0078-9.2578c0.20703-0.64062 0.80859-1.0781 1.4844-1.0781s1.2773 0.4375 1.4844 1.0781zm25 10.938 2.7695 8.5273h8.9688c0.67188 0 1.2734 0.43359 1.4844 1.0781 0.20703 0.64453-0.019531 1.3516-0.57031 1.7461l-7.25 5.2695 2.7695 8.5273c0.21094 0.64453-0.019531 1.3477-0.56641 1.7461-0.55078 0.39844-1.2891 0.39844-1.8359 0l-7.2539-5.2695-7.2539 5.2695c-0.54688 0.39844-1.2891 0.39844-1.8359 0-0.54688-0.39844-0.77734-1.1016-0.56641-1.7461l2.7695-8.5273-7.25-5.2695c-0.55078-0.39453-0.77734-1.1016-0.57031-1.7461 0.21094-0.64453 0.80859-1.0781 1.4844-1.0781h8.9688l2.7695-8.5273c0.20703-0.64453 0.80469-1.0781 1.4844-1.0781 0.67578 0 1.2734 0.43359 1.4844 1.0781zm-50 0 2.7695 8.5273h8.9648c0.67969 0 1.2773 0.43359 1.4844 1.0781 0.21094 0.64453-0.015625 1.3516-0.56641 1.7461l-7.25 5.2695 2.7695 8.5273c0.20703 0.64453-0.019531 1.3477-0.57031 1.7461-0.54297 0.39844-1.2852 0.39844-1.832 0l-7.2539-5.2695-7.2539 5.2695c-0.54688 0.39844-1.2891 0.39844-1.8359 0-0.54688-0.39844-0.77734-1.1016-0.56641-1.7461l2.7695-8.5273-7.2539-5.2695c-0.54688-0.39453-0.77734-1.1016-0.56641-1.7461 0.21094-0.64453 0.80859-1.0781 1.4844-1.0781h8.9648l2.7734-8.5273c0.20703-0.64453 0.80859-1.0781 1.4844-1.0781s1.2773 0.43359 1.4844 1.0781z"/>
                </svg>
              </span>
              <div>
                <strong>Unofficial Barnahus Ambassador</strong>
                <p>You completed the Networking challenge. Keep the conversations going.</p>
              </div>
            </div>
            <div class="challenge-grid" id="challengeGrid"></div>
          </article>

        </div>
      </section>

      <section class="print-programme" id="printProgramme" aria-label="Printable programme"></section>
    </main>
  </div>

  <div class="bottom-sheet" id="bottomSheet" hidden>
    <section class="sheet-panel" role="dialog" aria-modal="true" aria-labelledby="sheetTitle">
      <span class="sheet-handle" aria-hidden="true"></span>
      <div class="sheet-top">
        <span class="pill" id="sheetPill"></span>
        <div class="sheet-actions" id="sheetActions">
          <button class="sheet-close" type="button" data-close-sheet>Close</button>
        </div>
      </div>
      <h2 class="sheet-title" id="sheetTitle"></h2>
      <p class="sheet-summary" id="sheetSummary"></p>
      <div class="sheet-content" id="sheetContent"></div>
    </section>
  </div>

  <dialog id="notebookDialog" aria-labelledby="notebookDialogTitle">
    <div class="modal-card">
      <button class="modal-close" type="button" data-close-notebook>Close</button>
      <img src="/forum/assets/notebook/forum-notebook-cover.jpg" alt="Yellow Barnahus Network notebook cover">
      <div class="modal-dotgrid" role="img" aria-label="Graphic sample of the notebook dot-grid paper"></div>
      <div>
        <div class="kicker">Forum extra</div>
        <h2 id="notebookDialogTitle">Barnahus Network dot-grid notebook</h2>
        <p>Branded cover with dot-grid paper for notes, sketches and planning. Collection at the Forum only.</p>
      </div>
    </div>
  </dialog>

  <script>
    const sessions = [
      {
        id: "registration",
        time: "08:30-09:30",
        title: "Registration",
        room: "Main foyer",
        type: "Shared",
        subjects: ["Check-in"],
        summary: "Show your Luma QR code for faster registration.",
        description: [
          "Show your Luma QR code at the registration desk for faster check-in. Coffee will be available nearby.",
          "This is also the best moment to ask the organising team about access needs, room directions, practical questions, or any concern you would rather not raise during a session."
        ],
        outcomes: [
          "Have your Luma QR code ready on your phone",
          "Collect your badge and any Forum materials",
          "Arrive early if you need help from the organising team"
        ],
        people: []
      },
      {
        id: "opening",
        time: "09:30-10:30",
        title: "Opening plenary",
        room: "Ballroom",
        type: "Plenary",
        subjects: ["Network"],
        summary: "Welcome, milestones and scene-setting for the Forum.",
        description: [
          "Opening remarks and shared orientation for the day, with welcome words from Estonia, Iceland, the Council of the Baltic Sea States and the Barnahus Network.",
          "The Forum begins by orienting participants to the structure of the day, the shared aims of the Network, and the practical ways participants can use the programme, saved sessions, practical information and networking prompts."
        ],
        outcomes: [
          "Understand the focus and structure of the day",
          "Hear key milestones from the Network",
          "Know where to find programme, room and support information"
        ],
        people: ["Signe Riisalo", "Olof Asta Farestveit", "Markus Helavuori", "Olivia Lind Haldorsson"]
      },
      {
        id: "morningbreak",
        time: "10:30-11:00",
        title: "Morning break",
        room: "Coffee area",
        type: "Shared",
        subjects: ["Break"],
        summary: "Coffee, tea and a chance to move calmly into the first breakout block.",
        description: [
          "Coffee and tea will be available in the coffee area. Use this time to check your saved session, move calmly to the right room, and ask someone what brought them to this Forum.",
          "The Networking challenge is active during the break for participants who would like a small prompt for meeting new people."
        ],
        outcomes: [
          "Check your saved session before choosing a room",
          "Ask someone what brought them to this Forum",
          "Refill water before the breakout sessions begin"
        ],
        exchangeChallenge: true,
        people: []
      },
      {
        id: "standard11",
        time: "11:00-12:30",
        title: "Workshop to finalise the new standard on child protection",
        room: "Sydney",
        type: "Workshop",
        subjects: ["Child protection", "Standards"],
        summary: "A collaborative review of the final draft of Barnahus Quality Standard 11 on Child Protection.",
        description: [
          "This interactive workshop invites participants to take part in a collaborative review of the final draft of Barnahus Quality Standard 11 on Child Protection. Developed through extensive consultation with practitioners and experts across Europe, this standard sets out principles and expectations for how child protection agencies work in and with Barnahus.",
          "Participants will work in small groups to explore key questions that remain open in the draft, such as the order of application of the standards, the inclusion of siblings, the role of formal vs. statutory coordination, and how to reflect diverse national contexts. Your insights will directly shape the final text of the standard."
        ],
        outcomes: [
          "Understand the proposed content and scope of Standard 11 on Child Protection",
          "Contribute feedback and recommendations to shape the final version",
          "Share examples from practice to support practical implementation",
          "Strengthen shared understanding of child protection roles in Barnahus"
        ],
        people: ["Eimear Timmons"]
      },
      {
        id: "disabilities",
        time: "11:00-12:30",
        title: "Ensuring access and participation for children with disabilities in Barnahus",
        room: "Atlantic",
        type: "Panel and practical examples",
        subjects: ["Disability", "Access"],
        summary: "Tools, examples and perspectives for making Barnahus more accessible and inclusive for children with disabilities.",
        description: [
          "This session aims to provide participants with tools, examples, and perspectives to ensure that their services can meet the needs of all children, regardless of ability. It will highlight practical and policy-based approaches for making Barnahus more accessible and inclusive for children with disabilities. It will include contributions from projects working directly on access to justice and participation for children with disabilities, as well as national practice examples from Sweden.",
          "Children with disabilities are at higher risk of experiencing violence and abuse, but often face significant barriers in accessing justice and protection services, including Barnahus. These barriers may include communication challenges, lack of accessible environments, assumptions about credibility or capacity, and limited training for professionals.",
          "A contributor from INSIDE EU will share tools and recommendations for any professional on how to promote meaningful participation of children with disabilities and fewer opportunities in justice and recovery settings, tailored to the Barnahus context.",
          "Barnahus Linkoping, Sweden will present a trauma screening toolbox developed for use with children in social services, with a focus on how it is applied in practice with children with disabilities. The presentation will give recommendations on how to adapt the approach in your service, alongside information about the effort by THL in Finland to adapt and evaluate this approach.",
          "The Validity Foundation will present results from the Link project, which developed guidance for legal professionals in adapting court procedures and environments to meet the needs of children with disabilities. The presentation will also discuss the importance of adapting traditional professional frameworks to accommodate the needs of people with disabilities."
        ],
        outcomes: [
          "Be informed about common barriers children with intellectual and/or psychosocial disabilities face in accessing justice",
          "Gain practical ideas for adapting procedures, communication, and environments for this target group",
          "Learn about tools for trauma screening and participation that can be adapted and adopted",
          "Reflect on how to strengthen your own Barnahus team and practice"
        ],
        people: ["Emilie Rivas", "Anna Arganashvili", "Christian Sweeney", "Helena Asplund Carlqvist", "Ingrid Arkehed"]
      },
      {
        id: "participation",
        time: "11:00-12:30",
        title: "From voice to impact: Embedding child participation in Barnahus evaluation",
        room: "Singapore",
        type: "Case studies",
        subjects: ["Child participation", "Evaluation"],
        summary: "How Barnahus can collect, validate and use children's feedback so that participation meaningfully influences service quality and evaluation.",
        description: [
          "This session explores how Barnahus can meaningfully involve children in shaping, evaluating, and improving the support they receive. It will discuss not only how to collect meaningful feedback, but also how to effectively ensure the feedback is given space, audience, and influence, particularly in the context of evaluating Barnahus.",
          "The session begins with insights from Charite Berlin on their translation and validation of the Child Participation Tool to the German context, demonstrating how structured feedback from children can be embedded into quality assurance processes and recognised as credible across systems.",
          "The Lighthouse in London will share how they gather and apply feedback from children and young people throughout the criminal justice process, including around the Video Recorded Interview, and how this has led to a new feedback tool co-developed with youth.",
          "The University of Bedfordshire will present learning from the North Strathclyde Bairns Hoose evaluation, and introduce plans to embed children's voices in the development of a European evaluation framework for Barnahus."
        ],
        outcomes: [
          "Hear about how other countries have involved children in their Barnahus evaluation work",
          "Understand the role of validated tools and youth-designed methods for collecting and using feedback",
          "Reflect on key principles and practical strategies for embedding participation in Barnahus evaluations",
          "Consider how children's voices can meaningfully influence service quality and systemic change"
        ],
        people: ["Sven Wilson", "Camille Warrington", "Clementine Anderson", "Gina-Melissa Semrau"]
      },
      {
        id: "interviewing",
        time: "11:00-12:30",
        title: "Embedding evidence-based interviewing into multidisciplinary teams",
        room: "Ballroom",
        type: "Practice session",
        subjects: ["Forensic interviewing", "MDT"],
        summary: "A practical look at how forensic interviewing protocols can be embedded within multidisciplinary teams while respecting the rights of the child as a victim of crime.",
        description: [
          "This session aims to support interviewers and multidisciplinary teams to make the most of their work together. Experience across Europe shows that collaboration within Barnahus has led to improved, individualised planning and execution of forensic interviews with children, resulting in better outcomes.",
          "It explores how forensic interviewing protocols can be embedded within multidisciplinary child protection teams while respecting the rights of the child as a victim of crime. It looks at how inputs from other professionals before, during and after interviews can improve both the interview process and the quality of evidence.",
          "The session also examines how trauma-sensitive approaches before, during and after interviews can improve the child's capacity to share their experiences, while ensuring their rights are respected under the Victims' Rights Directive.",
          "Rebecca O'Donnell will provide updates on the progress to revise the Victims' Rights Directive. Niamh O'Loughlin will share practical experience and approaches from Barnahus West to embed evidence-based interviewing in their multidisciplinary team. Maria Keller-Hamela will reflect on how similar principles have informed practice in Poland. Relevant tools from recent EU-funded projects will be highlighted."
        ],
        outcomes: [
          "Reflect on progress to embed forensic interviews into robust multidisciplinary practice",
          "Explore how different professional inputs improve the quality and child-friendliness of interviews",
          "Be updated on the evolving legal framework under the Victims' Rights Directive",
          "Have information and inspiration to continue making progress"
        ],
        people: ["Rebecca O'Donnell", "Niamh O'Loughlin", "Maria Keller-Hamela"]
      },
      {
        id: "lunch",
        time: "12:30-14:00",
        title: "Lunch",
        room: "Restaurant area",
        type: "Shared",
        subjects: ["Lunch", "Networking"],
        summary: "Informal networking tables and Networking challenge prompts.",
        description: [
          "Lunch is open seating. A gentle prompt: sit with someone from a different country or role and ask what one practice idea they are taking home.",
          "The Networking challenge is active during lunch for participants who would like a small prompt for meeting new people."
        ],
        outcomes: [
          "Find a table without needing to sign up",
          "Use the time for informal exchange",
          "Check the afternoon breakout options before leaving"
        ],
        exchangeChallenge: true,
        people: []
      },
      {
        id: "childprotection",
        time: "14:00-15:30",
        title: "The practice of child protection in and with Barnahus",
        room: "Ballroom",
        type: "Panel",
        subjects: ["Child protection", "Coordination"],
        summary: "Roles, tasks, protocols and coordination across countries.",
        description: [
          "This session brings together practitioners and policy experts to explore how child protection operates in and with Barnahus across Europe. It begins with a presentation of new findings from a mapping of child protection roles and tasks in Barnahus, followed by an introduction to a newly developed checklist designed to complement the forthcoming Standard 11 on Child Protection.",
          "Ireland's Tusla Child and Family Agency will present their work to establish a national standard operating procedure to support coordinated action between Barnahus staff and child protection services. Participants will share how child protection is practiced in their own context, highlighting models, challenges, and success stories that could support implementation of the new standard."
        ],
        outcomes: [
          "Learn about the diversity of child protection practices in Barnahus across Europe",
          "Gain access to new tools, including a practical checklist and protocol guidance",
          "Share examples from your own work that may inform the finalisation of the forthcoming standard",
          "Reflect on coordination between Barnahus and child protection agencies"
        ],
        people: ["Fiona Geraghty", "Linda Jonsson", "Julie O'Donnell", "Sven Wilson"]
      },
      {
        id: "onlineharm",
        time: "14:00-15:30",
        title: "Digital shadows: Meeting the needs of children affected by online sexual violence in Barnahus",
        room: "Atlantic",
        type: "Panel",
        subjects: ["Online harm", "Caregivers"],
        summary: "Meeting the needs of children affected by online sexual violence, including children's voices, caregiver support and interagency coordination.",
        description: [
          "Online sexual violence against children is rising rapidly, yet many children affected by this form of abuse remain underserved. This session explores what Barnahus teams need to know and do differently to meet the needs of these children and support their recovery and access to justice.",
          "Linda Jonsson will set the scene about the unique challenges presented by online sexual violence against children, including the persistent perception among many professionals that it has a lesser impact on children compared to conventional forms of violence.",
          "She will further present recent findings from Sweden, including the new Childhood Foundation report Cracks in the System, which highlights gaps in interagency coordination and systemic barriers children face after online abuse.",
          "The voices of children will feature centrally through insights gathered by Children First, reflecting what young people want and need from professionals after online harm. Themes include the importance of being believed, going at the child's pace, having support from a trusted adult or organisation, and careful coordination between Barnahus, police, and other actors.",
          "Protect Children, Finland will present selected insights from their work supporting non-offending caregivers of children affected by online sexual violence. Drawing from their peer support group model You Are Enough, and an accompanying caregiver guide developed with European partners, the presentation will explore how caregivers process complex emotions such as guilt, grief, and helplessness, and how professionals can support them in turn.",
          "Topics will include how to help caregivers respond constructively to disclosure, manage feelings of failure and shame, and rebuild trust with their children."
        ],
        outcomes: [
          "Be informed about specific challenges and misconceptions that limit effective responses to online sexual violence against children",
          "Gain insights into what children and young people say they need from professionals after online harm",
          "Understand how to support non-offending caregivers through trauma, disclosure and family healing processes",
          "Reflect on practical ways Barnahus teams and interagency partners can adapt case management and service delivery"
        ],
        people: ["Anette Birgersson", "Katariina Leivo", "Mary Glasgow", "Linda Jonsson"]
      },
      {
        id: "medical",
        time: "14:00-15:30",
        title: "Mind and body",
        room: "Sydney",
        type: "Practice",
        subjects: ["Medical", "Assessment"],
        summary: "Medical and functional symptom assessments in Barnahus.",
        description: [
          "This session takes a joint perspective on medical and functional symptom assessments in Barnahus, with attention to how medical input informs legal, therapeutic and protection decisions.",
          "The session will explore how medical staff can be routinely included in multidisciplinary work and how teams can better use medical findings and functional symptom information in coordinated care."
        ],
        outcomes: [
          "Advocate for routine inclusion of medical staff",
          "Understand how medical findings inform decisions",
          "Explore how teams can use medical input in coordinated care",
          "Consider functional symptoms that affect interviews and recovery"
        ],
        people: ["Briony Arrowsmith", "Dr Anna Uwagboe"]
      },
      {
        id: "afternoonbreak",
        time: "15:30-16:00",
        title: "Afternoon break",
        room: "Coffee area",
        type: "Shared",
        subjects: ["Break"],
        summary: "A short reset before the closing plenary.",
        description: [
          "Coffee and tea will be available in the coffee area before the closing plenary. If your brain is full, this is the official permission slip to step outside for five minutes.",
          "Use the break to save any resources or contacts, complete a Networking challenge prompt, or check in with the organising team if you need support."
        ],
        outcomes: [
          "Return to the Ballroom for the closing plenary",
          "Save any resources or contacts before the end of the day",
          "Take a quiet moment if the discussions have been heavy"
        ],
        exchangeChallenge: true,
        people: []
      },
      {
        id: "closing",
        time: "16:00-17:00",
        title: "Momentum and milestones in the Barnahus Network",
        room: "Ballroom",
        type: "Plenary",
        subjects: ["Network"],
        summary: "Shared reflections and next steps.",
        description: [
          "This session highlights key milestones and special moments from across the Barnahus Network. We will welcome new members and new steering group members, recognise achievements, share updates on recent developments, and build momentum for the year ahead.",
          "Barnahus across Europe continue to take meaningful steps forward in their setup and practice. They are opening new locations, strengthening national frameworks, expanding services, building stronger connections with police and courts, establishing protocols, and embedding Barnahus more firmly into child protection and justice systems.",
          "Others have focused on improving the quality of care through training, new therapeutic models like EMDR and CPC-CBT, or by upgrading their physical spaces to better meet children's needs.",
          "Progress has also been made in raising awareness, influencing legislation, improving interagency cooperation, and applying evidence to practice. From launching tools to track outcomes, to supporting families more holistically, to welcoming children who were previously underserved, the momentum is clear.",
          "Join us in this plenary to see, hear and celebrate what colleagues across the Network are achieving, and what it means for the future of Barnahus."
        ],
        outcomes: [
          "Hear success stories and practical examples from across the Barnahus Network",
          "Celebrate peer accomplishments and collective growth",
          "Be updated on the latest developments within the Network",
          "Connect your own work to the shared vision and progress of the wider community"
        ],
        people: ["Olivia Lind Haldorsson", "Shawnna von Blixen-Finecke"]
      }
    ];

    const people = [
      { name: "Signe Riisalo", role: "Estonian Parliament", session: "opening" },
      { name: "Olof Asta Farestveit", role: "National Agency for Children and Families, Iceland", session: "opening" },
      { name: "Markus Helavuori", role: "Council of the Baltic Sea States", session: "opening" },
      { name: "Olivia Lind Haldorsson", role: "Barnahus Network", session: "opening" },
      { name: "Eimear Timmons", role: "The Lighthouse, London", session: "standard11" },
      { name: "Emilie Rivas", role: "Save the Children, Spain", session: "disabilities" },
      { name: "Anna Arganashvili", role: "Validity Foundation, Hungary", session: "disabilities" },
      { name: "Christian Sweeney", role: "Inside EU, Ireland", session: "disabilities" },
      { name: "Helena Asplund Carlqvist", role: "Barnahus Linkoping, Sweden", session: "disabilities" },
      { name: "Ingrid Arkehed", role: "Barnahus Linkoping, Sweden", session: "disabilities" },
      { name: "Sven Wilson", role: "Council of the Baltic Sea States", session: "participation" },
      { name: "Camille Warrington", role: "University of Bedfordshire, England", session: "participation" },
      { name: "Clementine Anderson", role: "The Lighthouse, London", session: "participation" },
      { name: "Gina-Melissa Semrau", role: "Childhood Haus Berlin, Charite University Hospital", session: "participation" },
      { name: "Rebecca O'Donnell", role: "Child Circle and Barnahus Network Steering Group", session: "interviewing" },
      { name: "Niamh O'Loughlin", role: "Barnahus West in Galway, Ireland", session: "interviewing" },
      { name: "Maria Keller-Hamela", role: "Empowering Children Foundation, Poland", session: "interviewing" },
      { name: "Fiona Geraghty", role: "Tusla, Ireland and Barnahus Network Steering Group", session: "childprotection" },
      { name: "Linda Jonsson", role: "Marie Cederschiold University, Sweden", session: "childprotection" },
      { name: "Julie O'Donnell", role: "Tusla, Ireland", session: "childprotection" },
      { name: "Anette Birgersson", role: "Marie Cederschiold University, Sweden", session: "onlineharm" },
      { name: "Katariina Leivo", role: "Protect Children, Finland", session: "onlineharm" },
      { name: "Mary Glasgow", role: "Children First, Scotland", session: "onlineharm" },
      { name: "Briony Arrowsmith", role: "The Havens", session: "medical" },
      { name: "Dr Anna Uwagboe", role: "The Lighthouse", session: "medical" },
      { name: "Shawnna von Blixen-Finecke", role: "Barnahus Network", session: "closing" }
    ];

    const speakerProfiles = {
      "Signe Riisalo": {
        "title": "Signe Riisalo",
        "role": "Member of Parliament and Chairman of the Social Affairs Committee of Estonian Parliament, Estonia; Former Minister of Social Protection; former member of the CBSS Expert Group on Children at Risk",
        "bio": [
          "Ms Riisalo is an Estonian politician and social policy expert with a distinguished career dedicated to child protection and social welfare. She has been a member of the Estonian Reform Party since 1994. Ms Riisalo currently serves as a Member of the Estonian Parliament (Riigikogu), following her tenure as Minister of Social Protection from January 2021 to March 2025.",
          "Ms Riisalo’s professional journey in social affairs began in 1993 at the Ministry of Social Affairs, where she worked until 2019, focusing on child and family policy. One assignment during this time was representing Estonia in the Council of the Baltic Sea States (CBSS) Expert Group on Children at Risk. In this role, she contributed to regional strategies aimed at safeguarding children’s rights and promoting child protection across the Baltic Sea Region.",
          "Throughout her career, she has been instrumental in developing policies that enhance social inclusion and protect vulnerable populations. Her work reflects a deep commitment to ensuring that all children have the opportunity to grow and thrive in a safe and supportive environment."
        ],
        "updated": "11 July 2026"
      },
      "Olof Asta Farestveit": {
        "title": "Ólöf Ásta Farestveit",
        "role": "Director General of Iceland’s National Agency for Children and Families, and member of the CBSS Expert Group on Children at Risk",
        "bio": [
          "Ólöf Ásta Farestveit has been a driving force behind Barnahus – at home and abroad. She joined Barnahus Iceland in 2001, specialising in forensic child interviewing, drawing on her academic background in pedagogy, criminology, and family therapy to make children’s voices count in court.",
          "As Director of Barnahus Iceland (2007–2021) she steered both clinical practice and day‑to‑day operations, turning Barnahus Iceland into a benchmark that continues to inspire the organisation and practice of similar services across Europe.",
          "On that foundation, Ólöf is one of the Europe’s most sought-after experts in multidisciplinary interagency services for children who are or who may be victims of violence. She has hosted countless international trainings and study visits, mentored emerging teams, and advised legislators across the continent.",
          "Since 2015 she has been a core contributor to the tools and guidance developed and promoted by the CBSS and the Barnahus Network, including the Barnahus Quality Standards, which support members to make incremental progress towards achieving the Network’s vision of a Europe where all children enjoy their right to be protected from violence. She was furthermore a co-signatory of the statutes that formalised the Barnahus Network in 2019.",
          "Today, as Director General of Iceland’s National Agency for Children and Families, she oversees nationwide child‑protection policy and continues to champion integrated services that put children at the centre of public services. A sought‑after speaker at Council of Europe, EU and global forums, she shares 25 years of experience on making child‑friendly justice a reality."
        ],
        "updated": "11 July 2026"
      },
      "Markus Helavuori": {
        "title": "Markus Helavuori",
        "role": "Deputy Director General, Council of the Baltic Sea States",
        "bio": [
          "Markus Helavuori is a Finnish expert in international policy and regulation, currently serving as the Deputy Director General of the Council of the Baltic Sea States (CBSS) Secretariat in Stockholm since 1 April 2025.  In this role, he supports the Director General in overseeing the Secretariat’s operations and advancing the CBSS’s strategic objectives across the Baltic Sea Region.",
          "Prior to his current position, he was the Deputy Executive Secretary at the Baltic Marine Environment Protection Commission (HELCOM), where he played a key role in coordinating regional efforts to protect the Baltic Sea environment. Earlier in his career, Helavuori served at the International Maritime Organization (IMO), contributing to the development of international maritime regulations.",
          "He also held positions within the Finnish government, focusing on legislative and policy development in maritime and environmental sectors."
        ],
        "updated": "11 July 2026"
      },
      "Olivia Lind Haldorsson": {
        "title": "Olivia Lind Haldorsson",
        "role": "Head of the Children at Risk unit at the Council of the Baltic Sea States, Secretary General of the Barnahus Network",
        "bio": [
          "Olivia Lind Haldorsson is a children’s rights advocate with nearly 30 years of experience in the field. She currently serves as Senior Adviser and Head of the Children at Risk Unit at the Council of the Baltic Sea States, where she leads regional efforts on integrated, resilient child protection systems. She is also the Secretary General of the Barnahus Network.",
          "As the author of the Barnahus Quality Standards and other practical tools, she has shaped how states meet their legal obligations to provide justice, protection, and recovery to children who are or may be victims of violence.  She has further been central to many projects that provided momentum for the establishment of Barnahus throughout Europe.",
          "Ms Lind Haldorsson holds a master’s degree in international relations and European Studies from the University of Kent. Her professional background is marked by strategic advocacy for child-friendly, multidisciplinary services, including serving as the Director of Save the Children International’s EU Office in Brussels and co-founding Child Circle, a Brussels-based NGO focusing on strengthening national child protection systems."
        ],
        "updated": "11 July 2026"
      },
      "Eimear Timmons": {
        "title": "Eimear Timmons",
        "role": "The Lighthouse, London",
        "bio": [
          "Eimear TIMMONS is a seasoned social work professional with over 30 years’ experience in child protection and children’s services. She is the Practice Development Manager at The Lighthouse in London, the UK’s first Barnahus-style Children’s House for young survivors of sexual abuse. In this role, Eimear draws on her extensive background as a frontline social worker and team manager to ensure a child-centred, trauma-informed approach. She has been pivotal in integrating social care with medical, therapeutic, and police services under one roof, improving experiences and outcomes for child victims. Her expertise encompasses multidisciplinary interagency coordination, court processes, and implementing the Barnahus Quality Standards in practice. Eimear is also active in training and sharing best practices internationally to help other regions establish similar child-friendly, multidisciplinary responses to child abuse."
        ],
        "updated": "11 July 2026"
      },
      "Anna Arganashvili": {
        "title": "Anna Arganashvili",
        "role": "Validity Foundation, Hungary",
        "bio": [
          "Anna ARGANASHVILI is a litigation officer at Validity Foundation (formerly MDAC), specialising in strategic litigation to advance the rights of persons with intellectual and psychosocial disabilities. A Georgian lawyer with 15 years of experience in child rights advocacy, her litigation before domestic courts, the European Court of Human Rights, and the UN Committee on the Rights of the Child has contributed to landmark decisions on violence against children and deinstitutionalisation. At Validity, she works on strategic cases across Europe that challenge systemic barriers preventing children with disabilities from accessing protection and redress. Anna also serves on expert panels at UN Women and the Council of Europe, advises governments on aligning judicial procedures with disability rights standards."
        ],
        "updated": "11 July 2026"
      },
      "Christian Sweeney": {
        "title": "Christian Sweeney",
        "role": "Inside EU, Ireland",
        "bio": [
          "Christian SWEENEY is an Irish social inclusion advocate and trainer. He serves as CEO of Institute for Studies in Social Inclusion, Diversity and Engagement (INSIDE EU), a community-led NGO based in Ireland. At INSIDE, Christian works to empower marginalised groups – including immigrants, minorities, and people with disabilities – through education, employment, and entrepreneurship initiatives. He has a background in human rights, and has delivered training across Europe on topics like diversity, anti-discrimination, and inclusive policymaking. Christian is also involved in research and international projects, and advocates for amplifying the voices of all vulnerable children."
        ],
        "updated": "11 July 2026"
      },
      "Helena Asplund Carlqvist": {
        "title": "Helena Asplund Carlqvist",
        "role": "Barnahus Linköping, Sweden",
        "bio": [
          "Helena ASPLUND CARLQVIST is a clinical psychologist at Barnahus Linköping and a specialist in psychological treatment and psychotherapy in Sweden. In her role at one of Sweden’s flagship Barnahus, Helena provides crisis support to children and families immediately after abuse disclosures, conducts in-depth trauma assessments, and delivers therapeutic services. She is also a trainer of educating social service staff on trauma and how to respond to child victims. Helena completed a specialist study on children’s and parents’ experiences of the Barnahus process in Linköping, using those findings to improve practice. She has previously worked at Barnafrid (the national knowledge centre for Sweden), where she contributed to national Barnahus training programs and materials."
        ],
        "updated": "11 July 2026"
      },
      "Ingrid Arkehed": {
        "title": "Ingrid Arkehed",
        "role": "Barnahus Linköping, Sweden",
        "bio": [
          "Ingrid ARKEHED is a clinical psychologist and key team member at Barnahus Linköping in Region Östergötland. Ingrid has been involved in expanding Barnahus services to new target groups – notably children and adolescents displaying harmful sexual behaviour (HSB) – while maintaining a child-centred, trauma-informed focus. Drawing on her expertise in child psychology, she helps develop assessment and intervention models that address both the needs of these youth and the safety of other children. Ingrid often shares practical insights from Sweden’s experience, including at the Barnahus Forum 2025, to inform ethical and effective strategies for integrating all vulnerable children into Barnahus in a safe and supportive way."
        ],
        "updated": "11 July 2026"
      },
      "Sven Wilson": {
        "title": "Sven Wilson",
        "role": "Council of the Baltic Sea States",
        "bio": [
          "Sven WILSON Sven Wilson is a policy and research specialist at the Council of the Baltic Sea States (CBSS) Children at Risk Unit in Stockholm. He focuses on regional cooperation to improve child protection, including supporting the Barnahus projects across Europe. Sven has supported the research and drafting of numerous groundbreaking Barnahus initiatives on child protection, child participation, and online sexual violence.",
          "He has led developments in digital innovation in Barnahus services – for example, a new child-friendly app that informs children about the Barnahus process. Sven works with governments and practitioners to strengthen multidisciplinary responses to child abuse. He has contributed to training webinars and tools for Barnahus, emphasising the importance of clear child communication and feedback.",
          "With a background in philosophy, research, and international policy, Sven plays a key part in the Barnahus Network’s efforts to ensure every child victim is heard, supported, and informed throughout their journey to justice."
        ],
        "updated": "11 July 2026"
      },
      "Camille Warrington": {
        "title": "Camille Warrington",
        "role": "University of Bedfordshire, England",
        "bio": [
          "Camille WARRINGTON is a leading UK researcher specialising in children’s rights, child sexual abuse and exploitation (CSAE), and participatory research methods. She is an Associate Professor at the University of Bedfordshire’s Institute of Applied Social Research, where she has pioneered work involving young people in shaping welfare and justice services to respond to abuse. Camille’s expertise centres on amplifying the voices of young survivors – her Ph.D. research examined how sexually exploited youth can be more involved in decisions about their care. She has over 15 years of experience in qualitative and creative research, often partnering with charities to ensure findings translate into practice improvements. Camille was a co-investigator on the landmark “Our Voices” project, promoting child participation in CSE services across Europe. Most recently, Camille has played a leading role in evaluating Scotland’s first Barnahus (‘Bairns’ Hoose), and the early development of three additional ‘Bairns’ Hoose’ Pathfinder sites."
        ],
        "updated": "11 July 2026"
      },
      "Clementine Anderson": {
        "title": "Clementine Anderson",
        "role": "The Lighthouse, London, England",
        "bio": [
          "Clementine ANDERSON is Consultant Social Worker The Lighthouse. A registered social worker with more than a decade’s experience in safeguarding and sexual-violence advocacy, Clementine leads the Lighthouse’s Child & Family Practitioner team (including Social Workers and Independent Sexual Violence Advisors), overseeing referral pathways, risk assessment, and ongoing support for children and families affected by sexual abuse. She has also been involved with co-designing a new evaluation tool that captures children’s views and experiences on their justice journey. Clementine regularly delivers training to police officers, social care staff, and health professionals on trauma-informed practice and child-centred engagement, and she has presented at UK and European forums on embedding meaningful child participation in service evaluation. Her practice blends frontline insight with a strong commitment to ensuring that children’s lived experiences directly shape improvements in Barnahus."
        ],
        "updated": "11 July 2026"
      },
      "Gina-Melissa Semrau": {
        "title": "Gina-Melissa Semrau",
        "role": "Childhood Haus Berlin, Charité University Hospital",
        "bio": [
          "Gina-Melissa SEMRAU is a research assistant at Childhood-Haus Berlin at Charité University Hospital. Since 2022, she has been contributing her expertise in psychology, with a focus on legal psychology, and is currently in advanced training as a child and adolescent psychotherapist. Her particular focus (and topic of her dissertation) is on the participation of children and adolescents and the implementation of joint research projects at German Childhood-Häuser (Barnahus)- including the development of standardised basic documentation. She has been actively involved in international collaborations to adapt Barnahus to emerging challenges – for example, contributing to the EU-funded PROMISE Elpis project which focuses on tailoring Barnahus processes to cases of online sexual violence. Gina shares Germany’s experiences through research and speaking engagements, helping to advance best practices in child protection and inspire the growth of similar services across Europe."
        ],
        "updated": "11 July 2026"
      },
      "Rebecca O'Donnell": {
        "title": "Rebecca O’Donnell",
        "role": "Child Circle and Barnahus Network Steering Group member",
        "bio": [
          "Rebecca O’DONNELL is an Irish barrister who has been working in Brussels for over twenty years. She is a Founding Member and the Director of Child Circle, a European NGO that strengthens child protection policy and practice. Rebecca has played a central role as a legal adviser to Promise from its inception in 2014/15, connecting Barnahus standards and practice with EU legal obligations and policy recommendations.",
          "With over 20 years’ experience in EU law and advocacy, O’Donnell specialises in areas like child-friendly justice, children in migration, and cross border protection.  She is also currently the senior EU policy adviser to the European Guardianship Network.  She previously served as Senior Child Protection Adviser at Save the Children’s EU Office, and was a partner in an international law firm focusing on EU law. Rebecca has contributed to influential projects on Barnahus.",
          "An experienced strategist and author of legal analyses, O’Donnell is dedicated to ensuring that systems of justice and child protection uphold children’s rights and welfare at every step."
        ],
        "updated": "11 July 2026"
      },
      "Niamh O'Loughlin": {
        "title": "Niamh O’Loughlin",
        "role": "Barnahus West in Galway, Ireland",
        "bio": [
          "Niamh O’LOUGHLIN is a specialist social work practitioner who holds the position of Interim Manager at Barnahus West in Galway, Ireland. Niamh has a master’s in social work from University of Galway, and a Professional Diploma in Court Intermediary Studies from the University of Limerick. Niamh has expertise as a Forensic Interviewer, and has undergone training in various therapeutic interventions for children and their families.",
          "Niamh played a key role in Ireland’s Barnahus pilot, bringing extensive frontline experience in child protection practice. At Barnahus West, she leads a multidisciplinary team dedicated to supporting child victims and their families through one coordinated process. Niamh’s expertise is nationally recognised, particularly in evidence-based interviewing of children. As an advocate for Barnahus, she frequently provides specialised training and speaks on the benefits of this innovative approach.",
          "In her work, Niamh emphasises child-friendly justice, rights-based frameworks, family engagement, and minimising trauma during investigations. Her leadership, collaboration, and practical insights have contributed to the expansion of Barnahus in Ireland, and she continues to advocate for refinements in policy and practice to better protect children."
        ],
        "updated": "11 July 2026"
      },
      "Maria Keller-Hamela": {
        "title": "Maria Keller-Hamela",
        "role": "Empowering Children Foundation, Poland, and Barnahus Network Steering Group member",
        "bio": [
          "Maria KELLER-HAMELA is a clinical psychologist and one of Poland’s foremost child protection experts. Since 1996, she has been working at the Empowering Children Foundation – formerly the Nobody’s Children Foundation – where she served for many years as Vice President of the Board. Maria has dedicated her career to preventing child abuse and improving responses for victims.",
          "She holds a psychology degree from the University of Warsaw and is certified in family violence prevention. Maria has pioneered child forensic interviewing techniques in Poland and in Eastern Europe, has trained judges, prosecutors, police officers, and therapists nationwide and abroad.",
          "Internationally, she has been coordinating for many years projects across Eastern Europe “Childhood without violence – towards the better child protection system” and is active in EU Barnahus initiatives, sharing her expertise to establish child-friendly services. She is the member of the editorial board of the scientific quarterly Child Abuse: Theory, Research, Practice."
        ],
        "updated": "11 July 2026"
      },
      "Fiona Geraghty": {
        "title": "Fiona Geraghty",
        "role": "Tusla, Ireland and Barnahus Network Steering Group member",
        "bio": [
          "Fiona GERAGHTY is an Irish social work leader serving as the Manager of Barnahus South in Cork under Tusla – Ireland’s Child and Family Agency. In this role, she oversees one of Ireland’s first Barnahus pilot services. Geraghty represents Tusla on the European Barnahus Network Steering Group, contributing her on-the-ground insights to the international Barnahus community. She previously worked as a Principal Social Worker in Cork and has a deep understanding of Ireland’s child protection system.",
          "Fiona has been instrumental in adapting Barnahus to the Irish context – establishing interagency protocols, training specialised staff, and ensuring services are child-friendly. Her work is helping to pave the way for a national rollout of Barnahus so that all children in Ireland have access to these one-stop, child-centred services."
        ],
        "updated": "11 July 2026"
      },
      "Linda Jonsson": {
        "title": "Linda Jonsson",
        "role": "Marie Cederschiöld University, Sweden",
        "bio": [
          "Linda JONSSON is a Swedish researcher and clinical expert specialising in child sexual abuse and exploitation. She holds a Ph.D. in child psychiatry and is an Associate Professor of social work, currently lecturing and researching at Marie Cederschiöld University.",
          "Jonsson’s career spans roles as head of the sexual abuse unit at Barnafrid (Sweden’s national knowledge center on violence against children) and as the European Barnahus competence centre coordinator for the Barnahus Network at the Council of the Baltic Sea States. Her research focuses on online exploitation, impact of trauma, and improving therapeutic interventions for child victims. Linda has published extensively, and has helped develop a Barnahus Quality Standard on child protection.",
          "She is actively involved in international Barnahus initiatives, ensuring that practice is informed by the latest evidence."
        ],
        "updated": "11 July 2026"
      },
      "Julie O'Donnell": {
        "title": "Julie O’Donnell",
        "role": "Tusla, Ireland",
        "bio": [
          "Julie O’DONNELL is a social worker who leads Barnahus development at Ireland’s Child and Family Agency (Tusla). Qualified in 2000, she has over two decades of experience in statutory child protection services. Julie has served as social work team leader and principal social worker, and was recently appointed National General Manager for Tusla Barnahus. She holds postgraduate qualifications in public management and leadership. She is also involved in training (e.g.",
          "at University of Galway) and has been a key contributor to Ireland’s Barnahus pilot in Galway. Julie’s leadership is driving the rollout of child-friendly, multiagency centres so that Irish children only have to tell their story as few times as necessary, and in a safe environment."
        ],
        "updated": "11 July 2026"
      },
      "Anette Birgersson": {
        "title": "Anette Birgersson",
        "role": "Marie Cederschiöld University, Sweden",
        "bio": [
          "Anette BIRGERSSON is a Swedish trauma therapist and internationally recognised trainer in evidence-based therapies for abused children. A qualified social worker and licensed psychotherapist, she has over 20 years’ experience working with children and adolescents impacted by sexual abuse and trauma. Anette is a certified trainer and supervisor in Trauma-Focused Cognitive Behavioural Therapy (TF-CBT) and Dialectical Behaviour Therapy (DBT) for youth.",
          "She founded SkillsClinic in Sweden, and frequently serves as a keynote speaker and educator across Europe, Australia, and the US. Anette’s mission is to improve practitioners’ understanding of trauma and to spread effective therapeutic approaches within Barnahus and similar services. She collaborates with Barnahus teams to implement therapeutic approaches and has been involved in the Barnahus Network’s capacity-building (for example, delivering TF-CBT courses to clinicians).",
          "With her engaging style and deep expertise, Anette Birgersson has trained hundreds of professionals, ultimately helping to ensure child victims receive compassionate, skilled therapy on their road to healing."
        ],
        "updated": "11 July 2026"
      },
      "Katariina Leivo": {
        "title": "Katariina Leivo",
        "role": "Protect Children, Finland",
        "bio": [
          "Katariina LEIVO is a Senior Specialist at Protect Children (Suojellaan Lapsia ry) in Finland, as well as a trauma-focused cognitive behavioural therapist working with children and youth affected by sexual violence, including online exploitation. She spent 17 years in London’s multicultural child services sector, promoting the wellbeing and recovery of vulnerable children and families, before returning to Finland to focus on protecting children from sexual abuse and supporting survivors.",
          "At Protect Children, Katariina plays a leading role in developing innovative support models – for example, facilitating the “You Are Enough” peer support groups for non-offending parents of child victims of online sexual abuse. She actively contributes her clinical expertise to governmental and international working groups, and regularly speaks at international conferences and panels on child protection.",
          "Katariina’s work ensures that Barnahus and similar services integrate specialised support for victims of online sexual exploitation and their families, helping professionals respond to new challenges in the digital age."
        ],
        "updated": "11 July 2026"
      },
      "Mary Glasgow": {
        "title": "Mary Glasgow",
        "role": "Children First, Scotland",
        "bio": [
          "Mary GLASGOW is the Chief Executive of Children First, Scotland’s national children’s charity, and a driving force behind the country’s implementation of Barnahus in Scotland (known locally as “Bairns’ Hoose”). Under her leadership, Children First established Scotland’s first Bairns’ Hoose in North Strathclyde. Mary has over three decades of experience in children’s services and has championed transformational change in how the justice and care systems respond to abuse.",
          "She works closely with government, academia, and partners (like Victim Support Scotland and police/judicial authorities) to test and develop the Barnahus approach in the Scottish context. Mary is a vocal advocate for children’s rights: she emphasises that children should be able to give evidence and heal from trauma without enduring the harm of traditional court processes. Internationally, she shares Scotland’s journey and encourages bold, rights-based reforms.",
          "Through her vision and advocacy, the Bairns’ Hoose has already supported hundreds of children and influenced plans to roll out Scotland’s approach nationally."
        ],
        "updated": "11 July 2026"
      },
      "Briony Arrowsmith": {
        "bio": [
          "Briony Arrowsmith brings specialist clinical and service experience from The Havens, with a focus on medical responses for children and young people after sexual abuse.",
          "Her contribution connects medical care, trauma-informed practice, and coordinated multidisciplinary support."
        ],
        "updated": "11 July 2026"
      },
      "Dr Anna Uwagboe": {
        "bio": [
          "Dr Anna Uwagboe brings specialist medical expertise from The Lighthouse, with a focus on child-centred health responses after abuse.",
          "Her work contributes to practical discussions about integrating medical care within multidisciplinary services."
        ],
        "updated": "11 July 2026"
      },
      "Shawnna von Blixen-Finecke": {
        "title": "Shawnna von Blixen-Finecke",
        "role": "Deputy Secretary General, Barnahus Network",
        "bio": [
          "Shawnna von BLIXEN-FINECKE is the Deputy Secretary General of the Barnahus Network. She has extensive knowledge of how Barnahus has been adapted in different national contexts, and plays a leading role in coordinating and advising Barnahus initiatives through bilateral support and international exchange.",
          "Shawnna played a central role in developing and coordinating training during various PROMISE and other projects, which were foundational in expanding Barnahus across Europe and strengthening their organisation and practice. An experienced project manager with a background in communications, she previously worked for the U.S. State Department and the European Commission, and holds a master’s in political science from Uppsala University.",
          "Shawnna’s work ensures that best practices and innovations in Barnahus implementation are shared throughout the network, strengthening services for children."
        ],
        "updated": "11 July 2026"
      }
    };

    const stateConfig = {
      before: {
        label: "Programme published",
        heading: "Barnahus Forum 2026 programme",
        date: "Tuesday 24 November 2026",
        pill: "136 days to the Forum",
        now: ["Now", "Call for member input", "Submit proposals and pathways"],
        next: ["Next", "Programme develops over summer", "Session details will be updated as confirmed"],
        primary: "Explore programme",
        notebook: "preorder",
        networking: "preview",
        showStatusLight: false,
        calendar: true,
        memberSubmissions: true
      },
      live: {
        label: "Live now",
        heading: "Opening plenary",
        date: "Tuesday 24 November",
        pill: "09:45 local time",
        now: ["Now", "Opening plenary", "09:30-10:30 - Ballroom"],
        next: ["Next", "Breakout session 1", "11:00-12:30 - choose one track"],
        primary: "Choose next breakout",
        notebook: "buy",
        networking: "active",
        showStatusLight: true,
        calendar: true,
        memberSubmissions: false
      },
      after: {
        label: "After the Forum",
        heading: "Programme archive",
        date: "From Wednesday 25 November",
        pill: "Follow-up mode",
        now: ["Available", "Programme archive", "Session details remain available"],
        next: ["Saved", "Personal programme", "Open your starred sessions and link"],
        primary: "Review saved sessions",
        notebook: "hidden",
        networking: "closed",
        showStatusLight: false,
        calendar: false,
        memberSubmissions: false
      }
    };

    const exchangePrompts = [
      ["similar-challenge", "Similar challenge", "Find one person whose country is facing a similar implementation challenge as you are facing."],
      ["child-feedback", "Child feedback", "Talk to someone who has used child feedback in evaluation."],
      ["first-forum", "First Forum", "Find someone attending the Forum for the first time."],
      ["three-forums", "Three Forums", "Find someone who attended all three Forums: Stockholm, Tallinn and Hamburg."],
      ["last-year", "Last year", "Ask someone what changed in their Barnahus in the last year."],
      ["tool-share", "Tool share", "Find a person who can share a tool, protocol or template after the Forum that would help you solve a current challenge."],
      ["peer-role", "Peer role", "Talk to someone from a different country who does similar work as you do."],
      ["wildcard", "Wildcard", "Write your own challenge before the next break."],
      ["best-view", "Best view", "Find the best view from the venue and take a photo with someone you just met."]
    ];
    const connectionMilestone = 5;

    function storedSet(key) {
      try {
        const value = JSON.parse(localStorage.getItem(key) || "[]");
        return new Set(Array.isArray(value) ? value : []);
      } catch {
        return new Set();
      }
    }

    function linkedSet(param, allowed) {
      const value = new URL(location.href).searchParams.get(param);
      if (!value) return new Set();
      return new Set(value.split(",").map(item => item.trim()).filter(item => allowed.has(item)));
    }

    const sessionCountsByTime = sessions.reduce((counts, session) => {
      counts[session.time] = (counts[session.time] || 0) + 1;
      return counts;
    }, {});
    const savableSessionIds = new Set(sessions
      .filter(session => session.type !== "Shared" && sessionCountsByTime[session.time] > 1)
      .map(session => session.id));
    const exchangePromptIds = new Set(exchangePrompts.map(([id]) => id));
    const linkedSaved = linkedSet("saved", savableSessionIds);
    const linkedExchange = linkedSet("game", exchangePromptIds);
    const stateIds = new Set(Object.keys(stateConfig));
    const sessionIds = new Set(sessions.map(session => session.id));
    const previewControlsAllowed = document.body.dataset.previewAuthorised === "1";
    const previewMode = previewControlsAllowed && new URL(location.href).searchParams.get("preview") === "1";
    const stateToggle = document.querySelector(".state-toggle");
    if (stateToggle) stateToggle.hidden = !previewMode;
    const saved = storedSet("forumCompanionV2Saved");
    Array.from(saved).forEach(id => {
      if (!savableSessionIds.has(id)) saved.delete(id);
    });
    linkedSaved.forEach(id => saved.add(id));
    const exchangeDone = storedSet("forumCompanionV2Exchange");
    Array.from(exchangeDone).forEach(id => {
      if (!exchangePromptIds.has(id)) exchangeDone.delete(id);
    });
    linkedExchange.forEach(id => exchangeDone.add(id));
    if (linkedSaved.size) localStorage.setItem("forumCompanionV2Saved", JSON.stringify(Array.from(saved)));
    if (linkedExchange.size) localStorage.setItem("forumCompanionV2Exchange", JSON.stringify(Array.from(exchangeDone)));

    const eventDetails = {
      title: "Barnahus Forum 2026",
      start: "20261124T083000",
      end: "20261124T170000",
      timezone: "Europe/Berlin",
      venue: "NordEvent Panoramadeck, Emporio, Dammtorwall 15, Hamburg",
      venueUrl: "https://www.google.com/maps/place/Nord+Event+Panoramadeck/@53.5561899,9.9785809,740m/data=!3m3!1e3!4b1!5s0x47b18f21d3af575d:0xeab10e4e1eb5c6bf!4m6!3m5!1s0x4163bcb34dc214d7:0x700a7fef23e00f7a!8m2!3d53.55619!4d9.9834518!16s%2Fg%2F1hc1wqf9p?entry=ttu",
      description: "Barnahus Forum 2026 participant programme and practical information."
    };
    const contentLastUpdated = "11 July 2026";
    const forumStartsAt = new Date("2026-11-24T08:30:00+01:00");
    const forumEndsAt = new Date("2026-11-24T17:00:00+01:00");

    function inferredState(date = new Date()) {
      if (date >= forumStartsAt && date <= forumEndsAt) return "live";
      if (date > forumEndsAt) return "after";
      return "before";
    }

    function initialState() {
      const linkedState = new URL(location.href).searchParams.get("state");
      return previewMode && stateIds.has(linkedState) ? linkedState : inferredState();
    }

    function initialSession(state) {
      const linkedSession = new URL(location.href).searchParams.get("session");
      if (sessionIds.has(linkedSession)) return linkedSession;
      if (linkedSaved.size) return Array.from(linkedSaved)[0];
      return state === "live" ? "opening" : "registration";
    }

    let currentState = initialState();
    let currentSession = initialSession(currentState);
    let savedOnly = false;

    const mapUrl = eventDetails.venueUrl;
    const timeline = document.getElementById("timeline");
    const detail = document.getElementById("detail");
    const practicalDetail = document.getElementById("practicalDetail");
    const venueLink = document.getElementById("venueLink");
    const calendarButton = document.querySelector("[data-add-calendar]");
    const lastUpdated = document.getElementById("lastUpdated");
    const savedTitle = document.getElementById("savedTitle");
    const savedSummary = document.getElementById("savedSummary");
    const printProgramme = document.getElementById("printProgramme");
    const notebookCard = document.getElementById("notebookCard");
    const notebookCopy = document.getElementById("notebookCopy");
    const notebookToggle = document.getElementById("notebookToggle");
    const networkingCard = document.getElementById("networkingCard");
    const networkingText = document.getElementById("networkingText");
    const publishedToday = document.getElementById("publishedToday");
    const statusLight = document.getElementById("statusLight");
    const challengeGrid = document.getElementById("challengeGrid");
    const challengeProgress = document.getElementById("challengeProgress");
    const connectionNotice = document.getElementById("connectionNotice");
    const ambassadorNotice = document.getElementById("ambassadorNotice");
    const notebookDialog = document.getElementById("notebookDialog");
    const bottomSheet = document.getElementById("bottomSheet");
    const sheetPill = document.getElementById("sheetPill");
    const sheetTitle = document.getElementById("sheetTitle");
    const sheetSummary = document.getElementById("sheetSummary");
    const sheetContent = document.getElementById("sheetContent");
    const sheetActions = document.getElementById("sheetActions");
    const collapsedLayout = window.matchMedia("(max-width: 980px)");
    const forumDate = new Date("2026-11-24T08:30:00+01:00");
    let lastFocusedBeforeSheet = null;
    let lastFocusSelector = "";
    let currentPracticalId = "";
    const practicalCardIds = [
      "secondary-traumatisation",
      "child-safeguarding",
      "photography",
      "submit-session-proposal",
      "show-me-your-pathway",
      "notebookCard"
    ];

    function initials(name) {
      return name.split(" ").map(part => part[0]).join("").slice(0, 2).toUpperCase();
    }

    function sessionById(id) {
      return sessions.find(session => session.id === id) || sessions[0];
    }

    function personByName(name) {
      return people.find(person => person.name === name);
    }

    function isSavableSession(session) {
      return savableSessionIds.has(session.id);
    }

    function isVisibleInSavedMode(session) {
      return !savedOnly || !isSavableSession(session) || saved.has(session.id);
    }

    function savedConflictGroups() {
      return Array.from(saved).map(sessionById).filter(isSavableSession).reduce((groups, session) => {
        groups[session.time] = groups[session.time] || [];
        groups[session.time].push(session);
        return groups;
      }, {});
    }

    function savedConflictIds() {
      return new Set(Object.values(savedConflictGroups())
        .filter(group => group.length > 1)
        .flat()
        .map(session => session.id));
    }

    function isCollapsedLayout() {
      return collapsedLayout.matches;
    }

    function sessionTagMarkup(session) {
      return `
        <div class="detail-tags">
          ${(session.subjects || []).map(subject => `<span class="pill">${subject}</span>`).join("")}
          <span class="pill">${session.type}</span>
          <span class="pill room-tag">${session.room}</span>
        </div>
      `;
    }

    function sessionsAtSameTime(session) {
      return sessions.filter(item => item.time === session.time && isSavableSession(item));
    }

    function sessionPeer(offset) {
      const session = sessionById(currentSession);
      const peers = sessionsAtSameTime(session);
      if (peers.length < 2) return null;
      const index = peers.findIndex(item => item.id === session.id);
      return peers[(index + offset + peers.length) % peers.length];
    }

    function sessionNavigationControls(session) {
      const peers = sessionsAtSameTime(session);
      if (peers.length < 2) return "";
      const previous = peers[(peers.findIndex(item => item.id === session.id) - 1 + peers.length) % peers.length];
      const next = peers[(peers.findIndex(item => item.id === session.id) + 1) % peers.length];
      return `
        <button class="nav-arrow" type="button" data-session-nav="prev" aria-label="Previous breakout session: ${previous.title}">‹</button>
        <button class="nav-arrow" type="button" data-session-nav="next" aria-label="Next breakout session: ${next.title}">›</button>
      `;
    }

    function practicalCardById(id) {
      return document.getElementById(id);
    }

    function practicalPeer(id, offset) {
      const visibleIds = practicalCardIds.filter(item => !practicalCardById(item)?.classList.contains("hidden"));
      const index = visibleIds.indexOf(id);
      if (index === -1 || visibleIds.length < 2) return "";
      return visibleIds[(index + offset + visibleIds.length) % visibleIds.length];
    }

    function practicalNavigationControls(id) {
      const previousId = practicalPeer(id, -1);
      const nextId = practicalPeer(id, 1);
      if (!previousId || !nextId) return "";
      const previousTitle = practicalCardById(previousId)?.querySelector("h3")?.textContent || "Previous";
      const nextTitle = practicalCardById(nextId)?.querySelector("h3")?.textContent || "Next";
      return `
        <button class="nav-arrow" type="button" data-practical-nav="prev" aria-label="Previous practical item: ${previousTitle}">‹</button>
        <button class="nav-arrow" type="button" data-practical-nav="next" aria-label="Next practical item: ${nextTitle}">›</button>
      `;
    }

    function practicalCardMeta(card) {
      const summary = card?.querySelector("summary");
      return {
        id: card?.id || "",
        pill: summary?.querySelector(".pill")?.textContent || "Practical",
        title: summary?.querySelector("h3")?.textContent || "Practical detail",
        summary: summary?.querySelector("p")?.textContent || "",
        updated: card?.dataset.updated || contentLastUpdated,
        content: card?.querySelector(".practical-detail")?.innerHTML || ""
      };
    }

    function cleanPracticalContent(target) {
      target.querySelector("[data-generated-practical-nav]")?.remove();
      const notebookButton = target.querySelector("#notebookToggle");
      if (notebookButton) {
        notebookButton.removeAttribute("id");
        notebookButton.setAttribute("data-panel-notebook-toggle", "true");
      }
    }

    let stripeLoaderPromise = null;

    function setStripeStatus(message, isError = false) {
      document.querySelectorAll("[data-stripe-status]").forEach(status => {
        status.textContent = message;
        status.classList.toggle("error", isError);
      });
    }

    function loadStripeBuyButton() {
      if (window.customElements?.get("stripe-buy-button")) {
        setStripeStatus("Stripe's secure payment options are ready.");
        return Promise.resolve();
      }

      if (stripeLoaderPromise) return stripeLoaderPromise;

      setStripeStatus("Loading Stripe's secure payment options...");
      stripeLoaderPromise = new Promise((resolve, reject) => {
        const finish = function () {
          window.customElements.whenDefined("stripe-buy-button").then(() => {
            setStripeStatus("Stripe's secure payment options are ready.");
            resolve();
          });
        };
        const fail = function () {
          setStripeStatus("Stripe could not be loaded. Please try again or speak with the Forum team.", true);
          stripeLoaderPromise = null;
          reject(new Error("Stripe Buy Button failed to load."));
        };
        const existingScript = document.querySelector('script[src="https://js.stripe.com/v3/buy-button.js"]');

        if (existingScript) {
          existingScript.addEventListener("error", fail, { once: true });
          finish();
          return;
        }

        const script = document.createElement("script");
        script.async = true;
        script.src = "https://js.stripe.com/v3/buy-button.js";
        script.dataset.barnahusStripeBuyButton = "true";
        script.addEventListener("load", finish, { once: true });
        script.addEventListener("error", fail, { once: true });
        document.head.appendChild(script);
      });

      return stripeLoaderPromise;
    }

    function toggleNotebookPayment(container) {
      const isOpen = container.classList.toggle("open");

      if (isOpen) {
        loadStripeBuyButton().catch(() => {});
      }
    }

    function showPracticalDetail(id = currentPracticalId || "secondary-traumatisation") {
      const card = practicalCardById(id);
      if (!card || card.classList.contains("hidden") || !practicalDetail) return;
      const meta = practicalCardMeta(card);
      currentPracticalId = meta.id;
      document.querySelectorAll(".practical-card").forEach(item => {
        item.classList.toggle("selected", item.id === meta.id);
        if (!isCollapsedLayout()) item.open = false;
      });
      practicalDetail.classList.remove("open");
      practicalDetail.innerHTML = `
        <div class="detail-head">
          <span class="pill">${meta.pill}</span>
          <div class="detail-actions">${practicalNavigationControls(meta.id)}</div>
        </div>
        <h2>${meta.title}</h2>
        <p>${meta.summary}</p>
        <span class="item-updated">Last updated ${meta.updated}</span>
        <div class="practical-detail">${meta.content}</div>
      `;
      cleanPracticalContent(practicalDetail);
    }

    function renderPracticalNavigation() {
      practicalCardIds.forEach(id => {
        const card = practicalCardById(id);
        const detailPanel = card?.querySelector(".practical-detail");
        if (!detailPanel) return;
        detailPanel.querySelector("[data-generated-practical-nav]")?.remove();
        const nav = practicalNavigationControls(id);
        if (nav) detailPanel.insertAdjacentHTML("afterbegin", `<div class="detail-actions" data-generated-practical-nav>${nav}</div>`);
      });
    }

    function speakerPeer(name, offset) {
      const session = sessionById(currentSession);
      const people = session.people || [];
      const index = people.indexOf(name);
      const peerIndex = index + offset;
      if (index === -1 || peerIndex < 0 || peerIndex >= people.length) return "";
      return people[peerIndex];
    }

    function renderSpeakerCard(scope, name) {
      const person = personByName(name);
      const note = scope.querySelector("[data-speaker-note]");
      if (!person || !note) return;
      const speakerSession = sessionById(person.session);
      const profile = speakerProfiles[person.name] || {};
      const sessionPeople = speakerSession.people || [];
      const speakerIndex = sessionPeople.indexOf(person.name);
      const previous = speakerPeer(person.name, -1);
      const next = speakerPeer(person.name, 1);
      scope.querySelectorAll("[data-speaker]").forEach(button => {
        button.setAttribute("aria-pressed", String(button.dataset.speaker === person.name));
      });
      note.innerHTML = `
        <div class="speaker-topline">
          <button class="back-link" type="button" data-return-session>Back to ${speakerSession.title}</button>
          ${sessionPeople.length > 1 && speakerIndex >= 0 ? `
            <div class="speaker-nav" aria-label="Speaker navigation">
              ${previous ? `<button type="button" data-speaker-nav="prev" aria-label="Previous speaker">‹</button>` : `<button type="button" aria-label="Previous speaker" disabled>‹</button>`}
              <span class="speaker-count">${speakerIndex + 1} of ${sessionPeople.length}</span>
              ${next ? `<button type="button" data-speaker-nav="next" aria-label="Next speaker">›</button>` : `<button type="button" aria-label="Next speaker" disabled>›</button>`}
            </div>
          ` : ""}
        </div>
        <div class="speaker-profile">
          <div>
            <h4>${profile.title || person.name}</h4>
            <span class="speaker-role">${profile.role || person.role}</span>
            <span class="item-updated">Last updated ${profile.updated || contentLastUpdated}</span>
          </div>
        </div>
        <div class="speaker-bio">
          ${(profile.bio || ["Biography to be confirmed."]).map(paragraph => `<p>${paragraph}</p>`).join("")}
        </div>
      `;
      note.dataset.currentSpeaker = person.name;
      note.classList.add("visible");
    }

    function renderTimeline() {
      const existing = timeline.querySelectorAll(".time-block");
      existing.forEach(item => item.remove());
      const conflictingSaved = savedConflictIds();
      const visibleSessions = sessions.filter(isVisibleInSavedMode);
      const grouped = visibleSessions.reduce((groups, session) => {
        const group = groups.find(item => item.time === session.time);
        if (group) group.sessions.push(session);
        else groups.push({ time: session.time, sessions: [session] });
        return groups;
      }, []);
      document.getElementById("programmeHeading").textContent = savedOnly ? "My saved programme" : "Tuesday 24 November";
      grouped.forEach(group => {
        const block = document.createElement("div");
        block.className = `time-block ${group.sessions.length > 1 ? "parallel" : ""}`;
        block.innerHTML = `
          <div class="time">${group.time.replace("-", "<br>")}</div>
          <div class="session-stack">
            ${group.sessions.length > 1 ? `<div class="parallel-label"><strong>Breakout sessions</strong><span>Choose one of ${group.sessions.length} parallel sessions</span></div>` : ""}
            ${group.sessions.map(session => `
              <article class="session ${session.id === currentSession ? "current" : ""} ${saved.has(session.id) && isSavableSession(session) ? "saved" : ""} ${conflictingSaved.has(session.id) ? "conflict" : ""}" id="session-${session.id}" data-session-card="${session.id}">
                <button class="session-main" type="button" data-session="${session.id}">
                  <span class="session-meta">${session.time} - ${session.room}</span>
                  <h3>${session.title}</h3>
                  ${isSavableSession(session) ? `<p class="why-line"><strong>Why attend:</strong> ${session.summary}</p>` : `<p>${session.summary}</p>`}
                  <span class="session-tags">
                    ${(session.subjects || []).map(subject => `<span class="pill">${subject}</span>`).join("")}
                    <span class="pill">${session.type}</span>
                    <span class="pill room-tag">${session.room}</span>
                  </span>
                  <span class="session-updated">Last updated ${session.updated || contentLastUpdated}</span>
                </button>
                ${isSavableSession(session) ? `<button class="star" type="button" data-save="${session.id}" aria-label="${saved.has(session.id) ? "Remove from saved sessions" : "Save session"}" aria-pressed="${saved.has(session.id) ? "true" : "false"}">${saved.has(session.id) ? "★" : "☆"}</button>` : ""}
              </article>
            `).join("")}
          </div>
        `;
        timeline.appendChild(block);
      });
    }

    function sessionDetailSections(session) {
      const descriptionMarkup = session.description?.length
        ? `<section class="detail-section"><h3>About this session</h3>${session.description.map(paragraph => `<p>${paragraph}</p>`).join("")}</section>`
        : "";
      const outcomesMarkup = session.outcomes?.length
        ? `<section class="detail-section"><h3>Participants will</h3><ul>${session.outcomes.map(outcome => `<li>${outcome}</li>`).join("")}</ul></section>`
        : "";
      const peopleMarkup = session.people.length
        ? `<div class="person-strip">${session.people.map(name => `<button class="person-chip" type="button" data-speaker="${name}" aria-pressed="false"><span class="avatar">${initials(name)}</span>${name}</button>`).join("")}</div><div class="speaker-note" data-speaker-note aria-live="polite"></div>`
        : "";
      const challengeMarkup = session.exchangeChallenge
        ? `<section class="detail-section"><button class="quiet-link" type="button" data-open-game>Open Networking challenge</button></section>`
        : "";
      return `
        ${sessionTagMarkup(session)}
        <p class="session-updated">Last updated ${session.updated || contentLastUpdated}</p>
        <div class="detail-list">
          <span><strong>Time:</strong> ${session.time}</span>
          <span><strong>Room:</strong> ${session.room}</span>
        </div>
        ${descriptionMarkup}
        ${outcomesMarkup}
        ${peopleMarkup}
        ${challengeMarkup}
      `;
    }

    function sessionSaveButton(session) {
      if (!isSavableSession(session)) return "";
      return `<button class="star detail-star" type="button" data-save="${session.id}" aria-label="${saved.has(session.id) ? "Remove from saved sessions" : "Save session"}" aria-pressed="${saved.has(session.id) ? "true" : "false"}">${saved.has(session.id) ? "★" : "☆"}</button>`;
    }

    function renderDetail() {
      const session = sessionById(currentSession);
      detail.innerHTML = `
        <div class="detail-head">
          <span class="pill">Session detail</span>
          <div class="detail-actions">${sessionNavigationControls(session)}${sessionSaveButton(session)}</div>
        </div>
        <h2>${session.title}</h2>
        <p>${session.summary}</p>
        ${sessionDetailSections(session)}
      `;
    }

    function openBottomSheet({ kind, pill, title, summary, content, returnFocusSelector = "" }) {
      if (!bottomSheet.classList.contains("open")) {
        lastFocusedBeforeSheet = document.activeElement;
      }
      lastFocusSelector = returnFocusSelector || lastFocusSelector;
      bottomSheet.hidden = false;
      bottomSheet.dataset.sheetKind = kind;
      sheetPill.textContent = pill || "";
      sheetTitle.textContent = title || "";
      sheetSummary.textContent = summary || "";
      sheetActions.innerHTML = `<button class="sheet-close" type="button" data-close-sheet>Close</button>`;
      sheetContent.classList.remove("open");
      sheetContent.innerHTML = content || "";
      sheetContent.querySelector("[data-generated-practical-nav]")?.remove();
      const sheetNotebookToggle = sheetContent.querySelector("#notebookToggle");
      if (sheetNotebookToggle) {
        sheetNotebookToggle.removeAttribute("id");
        sheetNotebookToggle.setAttribute("data-sheet-notebook-toggle", "true");
      }
      requestAnimationFrame(() => {
        bottomSheet.classList.add("open");
        document.body.classList.add("sheet-open");
        bottomSheet.querySelector("[data-close-sheet]")?.focus();
      });
    }

    function closeBottomSheet() {
      bottomSheet.classList.remove("open");
      document.body.classList.remove("sheet-open");
      bottomSheet.hidden = true;
      bottomSheet.dataset.sheetKind = "";
      const fallbackTarget = lastFocusSelector ? document.querySelector(lastFocusSelector) : null;
      if (lastFocusedBeforeSheet instanceof HTMLElement && lastFocusedBeforeSheet.isConnected && lastFocusedBeforeSheet !== document.body) {
        lastFocusedBeforeSheet.focus();
      } else if (fallbackTarget instanceof HTMLElement) {
        fallbackTarget.focus();
      }
      lastFocusedBeforeSheet = null;
      lastFocusSelector = "";
    }

    function sheetFocusableElements() {
      return Array.from(bottomSheet.querySelectorAll("a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex='-1'])"))
        .filter(element => element.getClientRects().length);
    }

    function openSessionSheet() {
      if (!isCollapsedLayout()) return;
      const session = sessionById(currentSession);
      openBottomSheet({
        kind: "session",
        pill: session.type,
        title: session.title,
        summary: session.summary,
        content: sessionDetailSections(session),
        returnFocusSelector: `[data-session="${session.id}"]`
      });
      if (sessionSaveButton(session)) {
        sheetActions.insertAdjacentHTML("afterbegin", sessionSaveButton(session));
      }
      const nav = sessionNavigationControls(session);
      if (nav) sheetActions.insertAdjacentHTML("afterbegin", nav);
    }

    function openPracticalSheet(card) {
      if (!card) return;
      const meta = practicalCardMeta(card);
      currentPracticalId = meta.id;
      openBottomSheet({
        kind: "practical",
        pill: meta.pill,
        title: meta.title,
        summary: `${meta.summary} Last updated ${meta.updated}.`,
        content: meta.content,
        returnFocusSelector: `#${meta.id} summary`
      });
      const nav = practicalNavigationControls(card.id);
      if (nav) sheetActions.insertAdjacentHTML("afterbegin", nav);
    }

    function updateSavedDock() {
      const savedSessions = Array.from(saved).map(sessionById).filter(isSavableSession);
      const savedButton = document.querySelector("[data-show-saved]");
      const savedDock = document.querySelector(".saved-dock");
      savedButton.textContent = savedOnly ? "Show full programme" : "Show saved";
      savedButton.setAttribute("aria-pressed", String(savedOnly));
      if (!savedSessions.length) {
        savedDock.classList.remove("has-clash");
        savedTitle.textContent = "No saved breakout sessions yet";
        savedSummary.textContent = "Save sessions to build a personal programme and spot clashes.";
        return;
      }
      const conflicts = Object.entries(savedConflictGroups()).filter(([, group]) => group.length > 1);
      const hasConflict = Boolean(conflicts.length);
      savedDock.classList.toggle("has-clash", hasConflict);
      savedTitle.textContent = `${savedSessions.length} saved session${savedSessions.length === 1 ? "" : "s"}`;
      savedSummary.textContent = hasConflict
        ? `Clash to resolve: choose one session at ${conflicts.map(([time]) => time).join(" and ")}.`
        : "No clashes. Use the buttons to view, email, print, or copy your programme.";
    }

    function normaliseHash(hash) {
      return String(hash || "").replace(/^#/, "");
    }

    function urlWithCompanionState(hash = `session-${currentSession}`, includePreviewState = false) {
      const url = new URL(location.href);
      url.hash = normaliseHash(hash);
      url.searchParams.set("session", currentSession);
      if (includePreviewState && previewMode) {
        url.searchParams.set("preview", "1");
        url.searchParams.set("state", currentState);
      } else {
        url.searchParams.delete("preview");
        url.searchParams.delete("state");
      }
      if (saved.size) url.searchParams.set("saved", Array.from(saved).join(","));
      else url.searchParams.delete("saved");
      if (exchangeDone.size) url.searchParams.set("game", Array.from(exchangeDone).join(","));
      else url.searchParams.delete("game");
      return url.toString();
    }

    function updateAddress(hash = `session-${currentSession}`) {
      history.replaceState(null, "", urlWithCompanionState(hash, true));
    }

    function personalUrl(hash = `session-${currentSession}`) {
      return urlWithCompanionState(hash, false);
    }

    function personalProgrammeLines() {
      const savableSaved = new Set(Array.from(saved).filter(id => isSavableSession(sessionById(id))));
      const lines = sessions
        .filter(session => !isSavableSession(session) || savableSaved.has(session.id))
        .map(session => `${session.time}: ${session.title} - ${session.room}`);
      if (!savableSaved.size) {
        lines.push("No breakout sessions selected yet.");
      }
      return lines;
    }

    function printableSessions() {
      return sessions.filter(isVisibleInSavedMode);
    }

    function printablePracticalHtml(card) {
      const detailPanel = card.querySelector(".practical-detail");
      if (!detailPanel) return "";
      const clone = detailPanel.cloneNode(true);
      clone.querySelectorAll("button, stripe-buy-button, [data-generated-practical-nav]").forEach(element => element.remove());
      return clone.innerHTML;
    }

    function renderPrintProgramme() {
      const visiblePracticalCards = practicalCardIds
        .map(practicalCardById)
        .filter(card => card && !card.classList.contains("hidden") && card.id !== "notebookCard");
      printProgramme.innerHTML = `
        <header>
          <p class="print-meta">${stateConfig[currentState].date} - ${eventDetails.venue}</p>
          <h1>${savedOnly ? "My saved Barnahus Forum programme" : "Barnahus Forum 2026 programme"}</h1>
          <p class="print-updated">Last updated ${contentLastUpdated}</p>
        </header>
        <section class="print-section">
          <h2>${savedOnly ? "Saved programme" : "Programme"}</h2>
          ${printableSessions().map(session => `
            <article class="print-session">
              <p class="print-meta">${session.time} - ${session.room}</p>
              <h3>${session.title}</h3>
              <p><strong>Why attend:</strong> ${session.summary}</p>
              <div class="print-tags">
                ${(session.subjects || []).map(subject => `<span>${subject}</span>`).join("")}
                <span>${session.type}</span>
                <span>${session.room}</span>
              </div>
              <p class="print-updated">Last updated ${session.updated || contentLastUpdated}</p>
            </article>
          `).join("")}
        </section>
        <section class="print-section">
          <h2>Practical information</h2>
          ${visiblePracticalCards.map(card => `
            <article class="print-practical">
              <p class="print-meta">${card.querySelector(".pill")?.textContent || "Practical"}</p>
              <h3>${card.querySelector("h3")?.textContent || ""}</h3>
              ${printablePracticalHtml(card)}
              <p class="print-updated">Last updated ${card.dataset.updated || contentLastUpdated}</p>
            </article>
          `).join("")}
        </section>
      `;
    }

    function calendarText(value) {
      return String(value)
        .replace(/\\/g, "\\\\")
        .replace(/\n/g, "\\n")
        .replace(/,/g, "\\,")
        .replace(/;/g, "\\;");
    }

    function downloadCalendar() {
      const nowStamp = new Date().toISOString().replace(/[-:]/g, "").replace(/\.\d{3}Z$/, "Z");
      const lines = [
        "BEGIN:VCALENDAR",
        "VERSION:2.0",
        "PRODID:-//Barnahus Network//Forum 2026//EN",
        "CALSCALE:GREGORIAN",
        "METHOD:PUBLISH",
        "BEGIN:VEVENT",
        `UID:barnahus-forum-2026@barnahus.eu`,
        `DTSTAMP:${nowStamp}`,
        `DTSTART;TZID=${eventDetails.timezone}:${eventDetails.start}`,
        `DTEND;TZID=${eventDetails.timezone}:${eventDetails.end}`,
        `SUMMARY:${calendarText(eventDetails.title)}`,
        `LOCATION:${calendarText(eventDetails.venue)}`,
        `DESCRIPTION:${calendarText(`${eventDetails.description}\n\nVenue: ${eventDetails.venue}\nMap: ${eventDetails.venueUrl}`)}`,
        "END:VEVENT",
        "END:VCALENDAR"
      ];
      const blob = new Blob([`${lines.join("\r\n")}\r\n`], { type: "text/calendar;charset=utf-8" });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = "barnahus-forum-2026.ics";
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);
    }

    function emailProgramme() {
      const subject = encodeURIComponent("My Barnahus Forum programme");
      const gameProgress = `${exchangeDone.size} of ${exchangePrompts.length} Networking challenge prompts complete`;
      const body = encodeURIComponent([
        "My personal programme",
        "",
        ...personalProgrammeLines(),
        "",
        "Open my programme and game progress:",
        personalUrl(),
        "",
        gameProgress,
        "",
        "Practical details",
        "",
        `Venue: ${eventDetails.venue}`,
        `Google Maps: ${mapUrl}`,
        "Registration: show your Luma QR code at the registration desk for faster check-in.",
        "Preventing secondary traumatisation: keep case details relevant and necessary, give warnings before disturbing details, and speak with an organiser if you need support."
      ].join("\n"));
      window.location.href = `mailto:?subject=${subject}&body=${body}`;
    }

    function renderExchangeChallenge() {
      const complete = exchangePrompts.filter(([id]) => exchangeDone.has(id)).length;
      challengeProgress.textContent = `${complete} of ${exchangePrompts.length} complete`;
      connectionNotice.classList.toggle("visible", complete >= connectionMilestone && complete < exchangePrompts.length);
      ambassadorNotice.classList.toggle("visible", complete === exchangePrompts.length);
      challengeGrid.innerHTML = exchangePrompts.map(([id, label, prompt]) => `
        <button class="challenge ${exchangeDone.has(id) ? "done" : ""}" type="button" data-exchange="${id}" aria-pressed="${exchangeDone.has(id) ? "true" : "false"}">
          <strong>${label}</strong>
          <span>${prompt}</span>
        </button>
      `).join("");
    }

    function applyState() {
      const config = stateConfig[currentState];
      document.getElementById("stateLabel").textContent = config.label;
      document.getElementById("pageTitle").textContent = config.heading;
      document.getElementById("dateLabel").textContent = config.date;
      lastUpdated.textContent = `Last updated ${contentLastUpdated}`;
      document.getElementById("nowTitle").textContent = config.now[1];
      document.getElementById("nowMeta").textContent = config.now[2];
      document.querySelector(".now-card .card-label").textContent = config.now[0];
      document.getElementById("nextTitle").textContent = config.next[1];
      document.getElementById("nextMeta").textContent = config.next[2];
      document.querySelector(".next-card .card-label").textContent = config.next[0];
      document.querySelector("[data-primary-action]").textContent = config.primary;
      document.querySelectorAll("[data-state]").forEach(button => {
        button.setAttribute("aria-pressed", String(button.dataset.state === currentState));
      });
      statusLight.dataset.mode = currentState;
      statusLight.querySelector("em").textContent = config.label;
      statusLight.classList.toggle("hidden", !config.showStatusLight);
      calendarButton.classList.toggle("hidden", !config.calendar);

      publishedToday.classList.toggle("hidden", currentState !== "before");
      document.getElementById("submit-session-proposal")?.classList.toggle("hidden", !config.memberSubmissions);
      document.getElementById("show-me-your-pathway")?.classList.toggle("hidden", !config.memberSubmissions);
      notebookCard.classList.toggle("hidden", config.notebook === "hidden");
      if (config.notebook === "buy") {
        notebookToggle.textContent = "Buy";
        notebookCopy.textContent = "Buy a branded dot-grid notebook. Collect it at the Forum. (Attention: shipping is not available.)";
      } else if (config.notebook === "preorder") {
        notebookToggle.textContent = "Pre-order";
        notebookCopy.textContent = "Pre-order a branded dot-grid notebook. Collect it at the Forum. (Attention: shipping is not available.)";
      }

      if (config.networking === "closed") {
        networkingText.textContent = "The challenge is closed. Keep the conversations going through your saved contacts.";
      } else if (config.networking === "preview") {
        networkingText.textContent = "Opens for travelling participants the day before the Forum.";
      } else {
        networkingText.textContent = "Open during the Forum day. Complete five prompts to unlock Ambassador status.";
      }
      renderPracticalNavigation();
      if (!isCollapsedLayout()) {
        const preferredPractical = practicalCardById(currentPracticalId)?.classList.contains("hidden") ? "secondary-traumatisation" : currentPracticalId || "secondary-traumatisation";
        showPracticalDetail(preferredPractical);
      }
    }

    function showView(view) {
      if (!document.getElementById(`view-${view}`)) view = "programme";
      document.querySelectorAll("[data-view-tab]").forEach(button => {
        button.setAttribute("aria-selected", String(button.dataset.viewTab === view));
      });
      document.querySelectorAll("[data-anchor-tab]").forEach(link => {
        link.setAttribute("aria-selected", String(link.dataset.anchorTab === view));
      });
      document.getElementById(`view-${view}`)?.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    function syncSectionFromHash() {
      const target = location.hash ? document.getElementById(normaliseHash(location.hash)) : null;
      if (target?.matches("[data-session-card]")) {
        currentSession = target.dataset.sessionCard;
        renderTimeline();
        renderDetail();
        updateSavedDock();
        if (isCollapsedLayout()) openSessionSheet();
      }
      if (target?.matches("details.practical-card")) {
        if (isCollapsedLayout()) openPracticalSheet(target);
        else {
          showPracticalDetail(target.id);
          showView("practical");
        }
      }
      const view = target?.closest(".view")?.id?.replace("view-", "") || "programme";
      document.querySelectorAll("[data-anchor-tab]").forEach(link => {
        link.setAttribute("aria-selected", String(link.dataset.anchorTab === view));
      });
    }

    function updateCountdown() {
      const now = new Date();
      const diff = Math.max(0, Math.ceil((forumDate - now) / 86400000));
      document.getElementById("countdownDays").textContent = String(diff);
      if (currentState === "before") {
        lastUpdated.textContent = `Last updated ${contentLastUpdated}`;
      }
    }

    function firstBreakoutId() {
      return sessions.find(session => isSavableSession(session) && session.time === "11:00-12:30")?.id || sessions.find(isSavableSession)?.id || "opening";
    }

    function firstSavedId() {
      return Array.from(saved).find(id => isSavableSession(sessionById(id))) || "";
    }

    function showSession(id) {
      currentSession = id;
      showView("programme");
      renderTimeline();
      renderDetail();
      updateSavedDock();
      openSessionSheet();
      updateAddress(`session-${currentSession}`);
    }

    function toggleSavedProgramme() {
      const firstSaved = firstSavedId();
      if (!firstSaved) {
        savedOnly = false;
        updateSavedDock();
        savedSummary.textContent = "No saved sessions yet. Save a breakout session first.";
        return;
      }
      savedOnly = !savedOnly;
      if (savedOnly && !isVisibleInSavedMode(sessionById(currentSession))) {
        currentSession = firstSaved;
      }
      showView("programme");
      renderTimeline();
      renderDetail();
      updateSavedDock();
    }

    window.addEventListener("hashchange", syncSectionFromHash);
    window.addEventListener("beforeprint", renderPrintProgramme);
    collapsedLayout.addEventListener("change", event => {
      if (!event.matches) closeBottomSheet();
    });

    document.addEventListener("keydown", event => {
      if (!bottomSheet.classList.contains("open")) return;
      if (event.key === "Escape") {
        closeBottomSheet();
        return;
      }
      if (event.key !== "Tab") return;
      const focusable = sheetFocusableElements();
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
    });

    document.addEventListener("click", event => {
      if (event.target === bottomSheet || event.target.closest("[data-close-sheet]")) {
        closeBottomSheet();
        return;
      }

      const anchorTab = event.target.closest("[data-anchor-tab]");
      if (anchorTab) {
        document.querySelectorAll("[data-anchor-tab]").forEach(link => {
          link.setAttribute("aria-selected", String(link === anchorTab));
        });
        return;
      }

      const viewTab = event.target.closest("[data-view-tab], [data-jump-view]");
      if (viewTab) {
        showView(viewTab.dataset.viewTab || viewTab.dataset.jumpView);
        return;
      }

      const practicalSummary = event.target.closest(".practical-card summary");
      if (practicalSummary) {
        event.preventDefault();
        const practicalCard = practicalSummary.closest(".practical-card");
        if (isCollapsedLayout()) openPracticalSheet(practicalCard);
        else {
          showPracticalDetail(practicalCard.id);
          updateAddress(practicalCard.id);
        }
        return;
      }

      const stateButton = event.target.closest("[data-state]");
      if (stateButton) {
        currentState = stateButton.dataset.state;
        savedOnly = false;
        applyState();
        updateCountdown();
        renderTimeline();
        renderDetail();
        updateSavedDock();
        closeBottomSheet();
        updateAddress();
        return;
      }

      const sessionButton = event.target.closest("[data-session]");
      if (sessionButton) {
        showSession(sessionButton.dataset.session);
        return;
      }

      const sessionNav = event.target.closest("[data-session-nav]");
      if (sessionNav) {
        const peer = sessionPeer(sessionNav.dataset.sessionNav === "prev" ? -1 : 1);
        if (peer) showSession(peer.id);
        return;
      }

      const speakerButton = event.target.closest("[data-speaker]");
      if (speakerButton) {
        const speakerScope = speakerButton.closest(".sheet-content") || detail;
        renderSpeakerCard(speakerScope, speakerButton.dataset.speaker);
        return;
      }

      const speakerNav = event.target.closest("[data-speaker-nav]");
      if (speakerNav) {
        const speakerScope = speakerNav.closest(".sheet-content") || detail;
        const note = speakerScope.querySelector("[data-speaker-note]");
        const currentSpeaker = note?.dataset.currentSpeaker || sessionById(currentSession).people[0];
        const peer = speakerPeer(currentSpeaker, speakerNav.dataset.speakerNav === "prev" ? -1 : 1);
        if (peer) renderSpeakerCard(speakerScope, peer);
        return;
      }

      if (event.target.closest("[data-return-session]")) {
        const speakerScope = event.target.closest(".sheet-content") || detail;
        const note = speakerScope.querySelector("[data-speaker-note]");
        if (note) {
          note.classList.remove("visible");
          note.innerHTML = "";
          note.dataset.currentSpeaker = "";
        }
        speakerScope.querySelectorAll("[data-speaker]").forEach(button => {
          button.setAttribute("aria-pressed", "false");
        });
        speakerScope.querySelector(".person-strip")?.scrollIntoView({ behavior: "smooth", block: "center" });
        return;
      }

      const practicalNav = event.target.closest("[data-practical-nav]");
      if (practicalNav) {
        const currentCard = practicalNav.closest(".practical-card") || practicalCardById(currentPracticalId);
        const peerId = practicalPeer(currentCard?.id || currentPracticalId, practicalNav.dataset.practicalNav === "prev" ? -1 : 1);
        const peerCard = practicalCardById(peerId);
        if (!peerCard) return;
        if (isCollapsedLayout() || bottomSheet.classList.contains("open")) {
          openPracticalSheet(peerCard);
        } else {
          showPracticalDetail(peerCard.id);
          updateAddress(peerCard.id);
        }
        return;
      }

      const saveButton = event.target.closest("[data-save]");
      if (saveButton) {
        const id = saveButton.dataset.save;
        if (!savableSessionIds.has(id)) return;
        if (saved.has(id)) saved.delete(id);
        else saved.add(id);
        if (savedOnly && !firstSavedId()) savedOnly = false;
        if (savedOnly && !isVisibleInSavedMode(sessionById(currentSession))) {
          currentSession = firstSavedId() || "registration";
        }
        localStorage.setItem("forumCompanionV2Saved", JSON.stringify(Array.from(saved)));
        renderTimeline();
        renderDetail();
        updateSavedDock();
        if (bottomSheet.classList.contains("open") && bottomSheet.dataset.sheetKind === "session") openSessionSheet();
        updateAddress();
        return;
      }

      if (event.target.closest("[data-show-saved]")) {
        toggleSavedProgramme();
        return;
      }

      if (event.target.closest("[data-primary-action]")) {
        if (currentState === "after") {
          if (!savedOnly) toggleSavedProgramme();
          else showSession(firstSavedId() || "opening");
        } else {
          showSession(firstBreakoutId());
        }
        return;
      }

      if (event.target.closest("[data-copy-link]")) {
        navigator.clipboard?.writeText(personalUrl());
        savedSummary.textContent = "Personal programme link copied.";
        return;
      }

      if (event.target.closest("[data-email-programme]")) {
        emailProgramme();
        return;
      }

      if (event.target.closest("[data-print-programme]")) {
        renderPrintProgramme();
        window.print();
        return;
      }

      if (event.target.closest("[data-add-calendar]")) {
        downloadCalendar();
        return;
      }

      if (event.target.closest("[data-copy-game-link]")) {
        navigator.clipboard?.writeText(personalUrl("networkingCard"));
        networkingText.textContent = "Networking challenge link copied.";
        return;
      }

      if (event.target.closest("[data-open-game]")) {
        networkingCard.hidden = false;
        showView("practical");
        networkingText.textContent = "You found the Networking challenge. Complete five prompts to unlock Ambassador status.";
        updateAddress("networkingCard");
        return;
      }

      const exchangeButton = event.target.closest("[data-exchange]");
      if (exchangeButton) {
        const id = exchangeButton.dataset.exchange;
        if (exchangeDone.has(id)) exchangeDone.delete(id);
        else exchangeDone.add(id);
        localStorage.setItem("forumCompanionV2Exchange", JSON.stringify(Array.from(exchangeDone)));
        renderExchangeChallenge();
        if (exchangeDone.size >= connectionMilestone && exchangeDone.size < exchangePrompts.length) {
          networkingText.textContent = "Connections made. Keep going.";
        }
        updateAddress("networkingCard");
        return;
      }

      if (event.target.closest("#notebookToggle")) {
        toggleNotebookPayment(notebookCard);
        return;
      }

      if (event.target.closest("[data-sheet-notebook-toggle]")) {
        toggleNotebookPayment(sheetContent);
        return;
      }

      if (event.target.closest("[data-panel-notebook-toggle]")) {
        toggleNotebookPayment(practicalDetail);
        return;
      }

      if (event.target.closest("[data-open-notebook]")) {
        notebookDialog.showModal();
        return;
      }

      if (event.target.closest("[data-close-notebook]")) {
        notebookDialog.close();
      }
    });

    venueLink.href = eventDetails.venueUrl;
    venueLink.textContent = eventDetails.venue;

    if (location.hash === "#networkingCard" || linkedExchange.size) {
      networkingCard.hidden = false;
    }
    renderTimeline();
    renderDetail();
    updateSavedDock();
    renderExchangeChallenge();
    applyState();
    updateCountdown();
    renderPrintProgramme();
    syncSectionFromHash();
  </script>
</body>
</html>
