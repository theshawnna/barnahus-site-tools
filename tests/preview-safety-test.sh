#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_DIR="$ROOT_DIR/barnahus-site-tools"

if find "$PLUGIN_DIR/forum-preview" -type f -name '*.html' -print -quit | rg -q '.'; then
  echo "Forum HTML templates must not be directly web-readable."
  exit 1
fi

if [ -e "$PLUGIN_DIR/bqs-preview" ] || [ -e "$PLUGIN_DIR/bqs-preview-v2" ] || [ -e "$PLUGIN_DIR/includes/bqs-preview.php" ]; then
  echo "BQS publishing source belongs in the separate barnahus-publishing project."
  exit 1
fi

if rg -n '/Users/|/home/|[A-Za-z]:\\\\Users\\\\' "$PLUGIN_DIR"; then
  echo "A local filesystem path is present in deployable plugin source."
  exit 1
fi

programme_blocked="$(php "$ROOT_DIR/tests/render-forum-route.php" /forum/programme 0 0)"
programme_unapproved="$(php "$ROOT_DIR/tests/render-forum-route.php" /forum/programme 1 0)"
programme_live="$(php "$ROOT_DIR/tests/render-forum-route.php" /forum/programme 1 1)"
participants_live="$(php "$ROOT_DIR/tests/render-forum-route.php" /forum/participants 1 1)"
programme_editor="$(php "$ROOT_DIR/tests/render-forum-route.php" '/forum/programme?preview=1' 1 1 1)"

if [ "$programme_blocked" != 'BLOCKED' ] || [ "$programme_unapproved" != 'BLOCKED' ]; then
  echo "Forum routes must require both publication controls."
  exit 1
fi

if ! rg -q '<h1[^>]*>Barnahus Forum 2026 programme</h1>' <<< "$programme_live";
then
  echo "The approved programme route did not render its production template."
  exit 1
fi

if ! rg -q '<h1>About the participants</h1>' <<< "$participants_live";
then
  echo "The approved participant route did not render its production template."
  exit 1
fi

if ! rg -q 'data-preview-authorised="1"' <<< "$programme_editor";
then
  echo "Authorised editor preview state was not exposed to the template."
  exit 1
fi

if rg -q '<script[^>]+src="https://js\.stripe\.com/v3/buy-button\.js"' <<< "$programme_live";
then
  echo "Stripe must load only after a participant opens the notebook payment controls."
  exit 1
fi

echo "Forum and preview safety checks passed."
