#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_FILE="$ROOT_DIR/barnahus-site-tools/barnahus-site-tools.php"
PLUGIN_README="$ROOT_DIR/barnahus-site-tools/README.md"
VERSION_FILE="$ROOT_DIR/VERSION"

if [ "$#" -ne 1 ]; then
  echo "Usage: ./scripts/bump-version.sh 1.0.1"
  exit 1
fi

version="$1"

if ! printf '%s' "$version" | grep -Eq '^[0-9]+\.[0-9]+\.[0-9]+$'; then
  echo "Version must use semantic version format, for example 1.0.1"
  exit 1
fi

printf '%s\n' "$version" > "$VERSION_FILE"
perl -0pi -e "s/Version: [0-9]+\\.[0-9]+\\.[0-9]+/Version: $version/" "$PLUGIN_FILE"
perl -0pi -e "s/Version: [0-9]+\\.[0-9]+\\.[0-9]+/Version: $version/" "$PLUGIN_README"

echo "Updated Barnahus Site Tools to version $version"
