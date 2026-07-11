#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_NAME="barnahus-site-tools"
ENV_FILE="$ROOT_DIR/.env"
VERSION="$(tr -d '[:space:]' < "$ROOT_DIR/VERSION")"
ZIP_FILE="$ROOT_DIR/dist/$PLUGIN_NAME-v$VERSION.zip"
BACKUP_ROOT="$ROOT_DIR/../rollback-archives"

if [ ! -f "$ENV_FILE" ]; then
  echo "Missing .env. Copy .env.example to .env and fill in your FTP details."
  exit 1
fi

if ! command -v lftp >/dev/null 2>&1; then
  echo "lftp is required for exact, rollback-ready deployments."
  exit 1
fi

set -a
# shellcheck disable=SC1090
source "$ENV_FILE"
set +a

: "${FTP_PROTOCOL:?Set FTP_PROTOCOL in .env}"
: "${FTP_HOST:?Set FTP_HOST in .env}"
: "${FTP_USER:?Set FTP_USER in .env}"
: "${FTP_PASSWORD:?Set FTP_PASSWORD in .env}"
: "${FTP_REMOTE_PLUGIN_DIR:?Set FTP_REMOTE_PLUGIN_DIR in .env}"

if [ "$FTP_PASSWORD" = "PASTE_PASSWORD_HERE" ]; then
  echo "Replace FTP_PASSWORD in .env before deploying."
  exit 1
fi

if [ "$FTP_PROTOCOL" != "ftp" ] && [ "$FTP_PROTOCOL" != "ftps" ]; then
  echo "FTP_PROTOCOL must be ftp or ftps."
  exit 1
fi

cd "$ROOT_DIR"

echo "Running pre-deploy checks..."
"$ROOT_DIR/scripts/check.sh"

echo "Building the committed release package..."
"$ROOT_DIR/scripts/build-zip.sh"

STAGING_DIR="$(mktemp -d "${TMPDIR:-/tmp}/barnahus-site-tools-deploy.XXXXXX")"
BACKUP_DIR="$BACKUP_ROOT/$(date '+%Y-%m-%d-%H%M%S')-live-$PLUGIN_NAME-before-v$VERSION"
trap 'rm -rf "$STAGING_DIR"' EXIT

unzip -q "$ZIP_FILE" -d "$STAGING_DIR"
DEPLOY_DIR="$STAGING_DIR/$PLUGIN_NAME"

if [ ! -f "$DEPLOY_DIR/$PLUGIN_NAME.php" ]; then
  echo "Release package is missing the main plugin file."
  exit 1
fi

if [ -e "$DEPLOY_DIR/bqs-preview" ] || [ -e "$DEPLOY_DIR/bqs-preview-v2" ] || [ -e "$DEPLOY_DIR/includes/bqs-preview.php" ]; then
  echo "The unfinished BQS preview must not be present in the default release."
  exit 1
fi

lftp_host="${FTP_RESOLVE_IP:-$FTP_HOST}"
lftp_settings="set cmd:fail-exit true; set net:max-retries 2; set net:timeout 20; "

if [ "$FTP_PROTOCOL" = "ftps" ]; then
  lftp_settings+="set ftp:ssl-force true; set ftp:ssl-protect-data true; "

  if [ "${FTP_RESOLVE_IP:-}" != "" ]; then
    lftp_settings+="set ssl:verify-certificate no; "
  fi
fi

mkdir -p "$BACKUP_DIR"
echo "Downloading a live rollback copy to $BACKUP_DIR"
(
  cd "$BACKUP_DIR"
  lftp -u "$FTP_USER,$FTP_PASSWORD" "ftp://$lftp_host" -e \
    "$lftp_settings mirror --verbose \"$FTP_REMOTE_PLUGIN_DIR\" .; bye"
)

if [ ! -f "$BACKUP_DIR/$PLUGIN_NAME.php" ]; then
  echo "The live rollback copy is incomplete; deployment stopped before any upload."
  exit 1
fi

echo "Deploying v$VERSION to $FTP_HOST:$FTP_REMOTE_PLUGIN_DIR"
(
  cd "$DEPLOY_DIR"
  lftp -u "$FTP_USER,$FTP_PASSWORD" "ftp://$lftp_host" -e \
    "$lftp_settings mirror --reverse --delete --verbose . \"$FTP_REMOTE_PLUGIN_DIR\"; bye"
)

echo "FTP deploy complete."
echo "Live rollback copy: $BACKUP_DIR"
