#!/usr/bin/env bash
#
# Mock curl used by the validate-byte-identical BATS suite. Installed at
# the front of PATH by helpers.bash. Honors the subset of curl flags the
# script under test actually uses (-o, -w, plus the -sS / timeout / retry
# flags we accept-and-ignore).
#
# Maps URLs of the form $RAW_FETCH_BASE_URL/<branch>/<path> to fixture
# files at $FAKE_REMOTE_DIR/<branch>/<path>. Returns "200" + the file
# body if the fixture exists; "404" + empty body if it does not.

set -euo pipefail

output_file=""
url=""
while [[ $# -gt 0 ]]; do
  case "$1" in
    -o) output_file="$2"; shift 2 ;;
    -w) shift 2 ;;
    --connect-timeout|--max-time|--retry|--retry-delay) shift 2 ;;
    --retry-connrefused|-sS|-s|-S) shift ;;
    http://*|https://*) url="$1"; shift ;;
    *) shift ;;
  esac
done

: "${RAW_FETCH_BASE_URL:?curl-mock: RAW_FETCH_BASE_URL not set}"
: "${FAKE_REMOTE_DIR:?curl-mock: FAKE_REMOTE_DIR not set}"

path="${url#"${RAW_FETCH_BASE_URL}/"}"
fixture="${FAKE_REMOTE_DIR}/${path}"

if [[ -f "${fixture}" ]]; then
  if [[ -n "${output_file}" ]]; then
    cp "${fixture}" "${output_file}"
  else
    cat "${fixture}"
  fi
  echo "200"
else
  if [[ -n "${output_file}" ]]; then
    : > "${output_file}"
  fi
  echo "404"
fi
