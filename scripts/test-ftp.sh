#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
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
  echo "Replace FTP_PASSWORD in .env before testing FTP."
  exit 1
fi

curl_protocol="$FTP_PROTOCOL"
if [ "$FTP_PROTOCOL" = "ftps" ]; then
  curl_protocol="ftp"
fi

curl_args=(
  --fail
  --silent
  --show-error
  --user "$FTP_USER:$FTP_PASSWORD"
)

if [ "$FTP_PROTOCOL" = "ftps" ]; then
  curl_args+=(--ssl-reqd)
fi

if [ "${FTP_RESOLVE_IP:-}" != "" ]; then
  curl_args+=(--resolve "$FTP_HOST:21:$FTP_RESOLVE_IP")
fi

test_name="codex-deploy-test.txt"
test_file="$(mktemp)"
trap 'rm -f "$test_file"' EXIT

printf 'Codex FTP deploy test for barnahus-site-tools. Safe to delete.\n' > "$test_file"

remote_file_url="$curl_protocol://$FTP_HOST$FTP_REMOTE_PLUGIN_DIR/$test_name"
remote_dir_url="$curl_protocol://$FTP_HOST$FTP_REMOTE_PLUGIN_DIR/"

curl "${curl_args[@]}" --ftp-create-dirs --upload-file "$test_file" "$remote_file_url"
echo "Uploaded $test_name"

curl "${curl_args[@]}" --list-only "$remote_dir_url" | grep -F "$test_name" >/dev/null
echo "Confirmed $test_name exists remotely"

curl "${curl_args[@]}" --quote "DELE $FTP_REMOTE_PLUGIN_DIR/$test_name" "$curl_protocol://$FTP_HOST/" >/dev/null
echo "Removed $test_name"

echo "FTP test complete."
