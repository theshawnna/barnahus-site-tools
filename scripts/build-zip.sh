#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_NAME="barnahus-site-tools"
DIST_DIR="$ROOT_DIR/dist"
VERSION="$(tr -d '[:space:]' < "$ROOT_DIR/VERSION")"
ZIP_FILE="$DIST_DIR/$PLUGIN_NAME.zip"
VERSIONED_ZIP_FILE="$DIST_DIR/$PLUGIN_NAME-v$VERSION.zip"

cd "$ROOT_DIR"

if [ -n "$(git status --porcelain --untracked-files=all -- "$PLUGIN_NAME")" ]; then
  echo "The deployable plugin has uncommitted or untracked files."
  echo "Commit the intended release before building so the zip has a reproducible source."
  git status --short --untracked-files=all -- "$PLUGIN_NAME"
  exit 1
fi

protected_bqs_paths=(
  "$PLUGIN_NAME/bqs-preview"
  "$PLUGIN_NAME/bqs-preview-v2"
  "$PLUGIN_NAME/includes/bqs-preview.php"
)

for protected_path in "${protected_bqs_paths[@]}"; do
  if git cat-file -e "HEAD:$protected_path" 2>/dev/null; then
    echo "BQS publishing source belongs in the barnahus-publishing project, not this plugin."
    exit 1
  fi
done

mkdir -p "$DIST_DIR"
rm -f "$ZIP_FILE" "$VERSIONED_ZIP_FILE"

git archive \
  --format=zip \
  --output="$ZIP_FILE" \
  HEAD \
  "$PLUGIN_NAME"

cp "$ZIP_FILE" "$VERSIONED_ZIP_FILE"

echo "Built $ZIP_FILE from commit $(git rev-parse --short HEAD)"
echo "Built $VERSIONED_ZIP_FILE"
