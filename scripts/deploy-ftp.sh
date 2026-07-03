#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_DIR="$ROOT_DIR/barnahus-site-tools"
ENV_FILE="$ROOT_DIR/.env"

if [ ! -f "$ENV_FILE" ]; then
  echo "Missing .env. Copy .env.example to .env and fill in your FTP details."
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

echo "Uploading barnahus-site-tools to $FTP_HOST:$FTP_REMOTE_PLUGIN_DIR"

curl_args=(
  --fail
  --silent
  --show-error
  --user "$FTP_USER:$FTP_PASSWORD"
  --ftp-create-dirs
)

if [ "$FTP_PROTOCOL" = "ftps" ]; then
  curl_args+=(--ssl-reqd)
fi

if [ "${FTP_RESOLVE_IP:-}" != "" ]; then
  curl_args+=(--resolve "$FTP_HOST:21:$FTP_RESOLVE_IP")
fi

while IFS= read -r -d '' file; do
  rel="${file#$PLUGIN_DIR/}"
  curl_protocol="$FTP_PROTOCOL"
  if [ "$FTP_PROTOCOL" = "ftps" ]; then
    curl_protocol="ftp"
  fi
  remote_url="$curl_protocol://$FTP_HOST$FTP_REMOTE_PLUGIN_DIR/$rel"

  upload_attempt=1
  until curl \
    "${curl_args[@]}" \
    --upload-file "$file" \
    "$remote_url"
  do
    if [ "$upload_attempt" -ge 3 ]; then
      echo "Upload failed after $upload_attempt attempts: $rel"
      exit 1
    fi

    upload_attempt=$((upload_attempt + 1))
    echo "Upload failed for $rel. Retrying attempt $upload_attempt of 3..."
    sleep 3
  done

  echo "Uploaded $rel"
done < <(find "$PLUGIN_DIR" -type f ! -name '.DS_Store' -print0 | sort -z)

echo "FTP deploy complete."
