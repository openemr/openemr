#!/usr/bin/env bash
#
# Mock `curl` for BATS tests of dockerhub-delete-tag.sh.
#
# The script under test makes two curl calls:
#   1. POST https://hub.docker.com/v2/users/login/  (login → JWT)
#   2. DELETE https://hub.docker.com/v2/repositories/<repo>/tags/<tag>/
#
# Both are exercised through this mock. The mock parses curl's flags
# just enough to identify which call it is (method + URL), then reads
# fixture files controlled by env vars to decide what to emit:
#
#   MOCK_LOGIN_RESPONSE_FILE     path to a file whose contents become
#                                the body written to curl's -o target on
#                                the login POST. Missing/empty file =
#                                empty body.
#   MOCK_LOGIN_EXIT              curl-level exit code for the login POST
#                                (default 0). Set to non-zero to simulate
#                                a network-level failure (DNS, TLS,
#                                timeout, connection reset).
#   MOCK_DELETE_HTTP_CODE        HTTP status code to emit on stdout via
#                                the -w '%{http_code}' contract for the
#                                DELETE (default 204).
#   MOCK_DELETE_RESPONSE_FILE    optional path to a file whose contents
#                                become the body written to curl's -o
#                                target on the DELETE. Missing =
#                                empty body.
#   MOCK_DELETE_EXIT             curl-level exit code for the DELETE
#                                (default 0). Non-zero simulates a
#                                network-level failure post-login.
#
# The mock always honors curl's -o (output file) and -w (write-out
# format) flags. When -w is present and includes %{http_code}, the mock
# writes the (resolved) HTTP code to stdout — matching what the script
# captures via `$(curl ... -w '%{http_code}')`. Other -w tokens are
# passed through unchanged (the script only uses %{http_code}).

set -euo pipefail

method="GET"
output_file=""
write_format=""
url=""

# Parse curl flags. Only the ones the script actually uses need
# handling; everything else is accept-and-ignore so a future tweak to
# the script's curl flags doesn't silently break the mock.
while [[ $# -gt 0 ]]; do
    case "$1" in
        -X) method="$2"; shift 2 ;;
        -X*) method="${1#-X}"; shift ;;
        -o) output_file="$2"; shift 2 ;;
        -w) write_format="$2"; shift 2 ;;
        -H) shift 2 ;;
        -d|--data|--data-raw|--data-binary) shift 2 ;;
        --connect-timeout|--max-time|--retry|--retry-delay) shift 2 ;;
        --retry-connrefused|-sS|-s|-S|-f) shift ;;
        http://*|https://*) url="$1"; shift ;;
        *) shift ;;
    esac
done

if [[ -z "${url}" ]]; then
    echo "curl-mock: no URL provided (parsed method=${method})" >&2
    exit 2
fi

# Route based on URL + method.
case "${url}" in
    https://hub.docker.com/v2/users/login/)
        # Login POST. Guard: a regression that flips the login call to a
        # different HTTP method would still match on URL alone; assert
        # method here so the mock fails loudly instead of pretending to
        # log in with a GET (or whatever).
        if [[ "${method}" != "POST" ]]; then
            echo "curl-mock: expected POST for login, got ${method}" >&2
            exit 2
        fi
        # Simulate a network failure if requested.
        login_exit="${MOCK_LOGIN_EXIT:-0}"
        if [[ "${login_exit}" -ne 0 ]]; then
            exit "${login_exit}"
        fi

        # Write the fixture body to the -o file (script consumes it via
        # `< "${login_response_file}"` for jq parsing).
        if [[ -n "${output_file}" ]]; then
            if [[ -n "${MOCK_LOGIN_RESPONSE_FILE:-}" && -f "${MOCK_LOGIN_RESPONSE_FILE}" ]]; then
                cp "${MOCK_LOGIN_RESPONSE_FILE}" "${output_file}"
            else
                : > "${output_file}"
            fi
        fi

        # The script's login curl doesn't use -w — nothing to write to
        # stdout. Exit 0.
        exit 0
        ;;
    https://hub.docker.com/v2/repositories/*/tags/*/)
        # DELETE tag. Guard method the same way as login above.
        if [[ "${method}" != "DELETE" ]]; then
            echo "curl-mock: expected DELETE for tag route, got ${method}" >&2
            exit 2
        fi
        # Same network-failure simulation hook.
        delete_exit="${MOCK_DELETE_EXIT:-0}"
        if [[ "${delete_exit}" -ne 0 ]]; then
            exit "${delete_exit}"
        fi

        if [[ -n "${output_file}" ]]; then
            if [[ -n "${MOCK_DELETE_RESPONSE_FILE:-}" && -f "${MOCK_DELETE_RESPONSE_FILE}" ]]; then
                cp "${MOCK_DELETE_RESPONSE_FILE}" "${output_file}"
            else
                : > "${output_file}"
            fi
        fi

        # Emit the HTTP code via the -w contract. The script only uses
        # %{http_code}, so we replace that token in the format string
        # rather than parsing arbitrary write-out formats.
        http_code="${MOCK_DELETE_HTTP_CODE:-204}"
        if [[ -n "${write_format}" ]]; then
            printf '%s' "${write_format//%\{http_code\}/${http_code}}"
        fi
        exit 0
        ;;
    *)
        echo "curl-mock: unsupported URL: ${url} (method=${method})" >&2
        exit 2
        ;;
esac
