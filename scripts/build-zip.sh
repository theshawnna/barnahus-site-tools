#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_NAME="barnahus-site-tools"
DIST_DIR="$ROOT_DIR/dist"
VERSION="$(tr -d '[:space:]' < "$ROOT_DIR/VERSION")"
ZIP_FILE="$DIST_DIR/$PLUGIN_NAME.zip"
VERSIONED_ZIP_FILE="$DIST_DIR/$PLUGIN_NAME-v$VERSION.zip"

mkdir -p "$DIST_DIR"
rm -f "$ZIP_FILE" "$VERSIONED_ZIP_FILE"

cd "$ROOT_DIR"
zip -qr "$ZIP_FILE" "$PLUGIN_NAME" \
  -x "$PLUGIN_NAME/.DS_Store" \
  -x "$PLUGIN_NAME/**/.DS_Store"

cp "$ZIP_FILE" "$VERSIONED_ZIP_FILE"

echo "Built $ZIP_FILE"
echo "Built $VERSIONED_ZIP_FILE"
