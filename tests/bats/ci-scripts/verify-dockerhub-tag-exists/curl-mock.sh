#!/usr/bin/env bash
#
# Mock `curl` CLI for BATS tests of verify-dockerhub-tag-exists.sh.
#
# The mock replaces the real `curl` binary on PATH so the script under
# test never touches Docker Hub. Understands the exact invocation shape
# the script uses:
#
#   curl --connect-timeout 10 --max-time 30 -sS \
#       -o <response-body-file> -w '%{http_code}' <url>
#
# Fixture behavior is controlled via three env vars read at mock-invocation
# time:
#
#   MOCK_CURL_HTTP_CODE   HTTP status the mock prints to stdout (matches
#                         the real curl's `-w '%{http_code}'` output).
#                         Default: "200".
#   MOCK_CURL_BODY        Bytes written to the -o file. Default: "" (empty
#                         body -- matches Docker Hub's 404 response shape
#                         which is a JSON error object, but the script
#                         only cares about the body on the unexpected-
#                         status branch, so keep it minimal for the
#                         happy path).
#   MOCK_CURL_EXIT        Mock exits with this status BEFORE writing any
#                         output. Simulates curl-level failure (DNS,
#                         TLS, timeout, connection reset). Default: "0".
#                         When nonzero, MOCK_CURL_HTTP_CODE and
#                         MOCK_CURL_BODY are ignored.

set -euo pipefail

exit_code="${MOCK_CURL_EXIT:-0}"
http_code="${MOCK_CURL_HTTP_CODE:-200}"
body="${MOCK_CURL_BODY:-}"

# Find the -o output file in the argv.
output_file=""
while [[ $# -gt 0 ]]; do
    case "$1" in
        -o)
            output_file="$2"
            shift 2
            ;;
        -o*)
            output_file="${1#-o}"
            shift
            ;;
        *)
            shift
            ;;
    esac
done

if [[ "${exit_code}" -ne 0 ]]; then
    # Simulate curl-level failure: don't print status, don't write body,
    # just exit with the failure code. Real curl behaves the same way for
    # e.g. DNS failure (exit 6, no HTTP response ever).
    exit "${exit_code}"
fi

if [[ -n "${output_file}" ]]; then
    printf '%s' "${body}" > "${output_file}"
fi
# The real curl with `-w '%{http_code}'` prints ONLY the status code to
# stdout when -o directs the response body to a file. Match that shape.
printf '%s' "${http_code}"
