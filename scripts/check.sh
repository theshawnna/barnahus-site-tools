#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_DIR="$ROOT_DIR/barnahus-site-tools"
VERSION_FILE="$ROOT_DIR/VERSION"
PLUGIN_FILE="$PLUGIN_DIR/barnahus-site-tools.php"
PLUGIN_README="$PLUGIN_DIR/README.md"

cd "$ROOT_DIR"

echo "Checking plugin files..."

shell_files=()
while IFS= read -r -d '' file; do
  shell_files+=("$file")
done < <(find "$ROOT_DIR/scripts" "$ROOT_DIR/tests" -type f -name '*.sh' -print0 | sort -z)

for file in "${shell_files[@]}"; do
  bash -n "$file"
  echo "OK ${file#$ROOT_DIR/}"
done

if [ ! -f "$VERSION_FILE" ]; then
  echo "Missing VERSION file."
  exit 1
fi

version="$(tr -d '[:space:]' < "$VERSION_FILE")"

if ! printf '%s' "$version" | grep -Eq '^[0-9]+\.[0-9]+\.[0-9]+$'; then
  echo "VERSION must use semantic version format, for example 1.0.1"
  exit 1
fi

if ! grep -q "Version: $version" "$PLUGIN_FILE"; then
  echo "Version mismatch: $PLUGIN_FILE does not match VERSION ($version)."
  exit 1
fi

if ! grep -q "Version: $version" "$PLUGIN_README"; then
  echo "Version mismatch: $PLUGIN_README does not match VERSION ($version)."
  exit 1
fi

php_files=()
while IFS= read -r -d '' file; do
  php_files+=("$file")
done < <(find "$PLUGIN_DIR" -type f -name '*.php' -print0 | sort -z)

if command -v php >/dev/null 2>&1; then
  for file in "${php_files[@]}"; do
    php -l "$file" >/dev/null
    echo "OK ${file#$ROOT_DIR/}"
  done
elif command -v docker >/dev/null 2>&1; then
  for file in "${php_files[@]}"; do
    rel="${file#$ROOT_DIR/}"
    docker run --rm -v "$ROOT_DIR:/work" -w /work php:8.2-cli php -l "$rel" >/dev/null
    echo "OK $rel"
  done
else
  echo "PHP syntax check skipped: install PHP or Docker to enable it."
fi

rtf_files=()
while IFS= read -r -d '' file; do
  if head -c 6 "$file" | grep -q '^{\\rtf'; then
    rtf_files+=("$file")
  fi
done < <(find "$PLUGIN_DIR" -type f \( -name '*.php' -o -name '*.md' \) -print0 | sort -z)

if [ "${#rtf_files[@]}" -gt 0 ]; then
  echo "RTF-formatted source files found. Save these as plain text before deploying:"
  printf '%s\n' "${rtf_files[@]}"
  exit 1
fi

php "$ROOT_DIR/tests/events-taxonomy-test.php"
php "$ROOT_DIR/tests/bmc-test.php"
bash "$ROOT_DIR/tests/preview-safety-test.sh"

git diff --check

echo "Checks complete."
