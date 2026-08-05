#!/usr/bin/env bash
#
# Delete a tag from Docker Hub via the v2 API.
#
# Docker Hub's tag-delete endpoint requires a JWT obtained from the user-
# login endpoint (a raw personal-access-token isn't sufficient for this
# specific endpoint). Cheap two-call flow: POST /v2/users/login/ ->
# extract .token -> DELETE the tag.
#
# Extracted from .github/workflows/reusable-docker-publish.yml's
# cleanup-candidate job. That job carries `continue-on-error: true` on
# the workflow side (a stale release-candidate-* tag is cosmetically
# annoying but not a correctness issue), so a silent regression in the
# inline JWT flow would go unnoticed forever. Pulling the logic into a
# script lets BATS assert the exit-code / status-code interpretation
# stays correct.
#
# Inputs (env, all required unless noted)
#   DOCKERHUB_USERNAME    Docker Hub username used for the login POST.
#   DOCKERHUB_TOKEN       Docker Hub password or PAT used for login. Must
#                         be a token/password with permission to delete
#                         repository tags (the reusable workflow passes
#                         the same DOCKERHUB_TOKEN it uses for
#                         docker/login-action).
#   TAG                   The tag to delete (e.g.
#                         `release-candidate-30443247578-1`). Just the
#                         tag portion — the repository is separate.
#   REPO                  Optional. Repository the tag lives under.
#                         Defaults to `openemr/openemr`.
#
# Exit codes
#   0   Tag deleted (HTTP 204), OR tag already gone (HTTP 404 —
#       idempotent re-run).
#   1   JWT login failed (network error, non-JSON body, missing .token),
#       OR the DELETE returned an unexpected status (401, 403, 5xx, etc.)
#
# The caller in reusable-docker-publish.yml keeps
# `continue-on-error: true` on the workflow step, so a non-zero exit
# here surfaces as an annotation without failing the run. That's a
# separate decision from what THIS script returns — the script's job is
# to interpret the API responses honestly; the workflow decides whether
# a cleanup failure should block the release.

set -euo pipefail

: "${DOCKERHUB_USERNAME:?DOCKERHUB_USERNAME env var is required}"
: "${DOCKERHUB_TOKEN:?DOCKERHUB_TOKEN env var is required}"
: "${TAG:?TAG env var is required}"
REPO="${REPO:-openemr/openemr}"

# Mint a JWT. Bounded curl: --connect-timeout 10 (TCP/TLS setup) +
# --max-time 30 (total wall time). Without those, a hung Docker Hub
# stalls the job until the runner's outer timeout fires. -sS keeps
# routine output quiet but still surfaces curl errors on stderr.
#
# Capture curl's exit code explicitly so a network-level failure (DNS,
# TLS, timeout, connection reset) produces a clear ::error:: instead of
# silently propagating a JSON-parse failure downstream.
login_response_file=$(mktemp)
# Write the token to a tempfile so jq can read it via --rawfile
# instead of receiving it as a --arg. That keeps the raw token out of
# jq's argv (visible via `ps aux` for the jq call's lifetime). Both
# tempfiles cleaned up in the same trap.
token_file=$(mktemp)
trap 'rm -f "${login_response_file}" "${token_file}"' EXIT
printf '%s' "${DOCKERHUB_TOKEN}" > "${token_file}"

echo "==> POST https://hub.docker.com/v2/users/login/"
# Build the JSON body with jq so a `"` or `\` in either credential
# doesn't produce a malformed request. Username is short + non-secret
# so --arg is fine; token uses --rawfile (see token_file above) to
# stay out of argv. Body piped to curl via stdin so it also stays out
# of curl's argv.
login_body=$(jq -n \
    --arg u "${DOCKERHUB_USERNAME}" \
    --rawfile p "${token_file}" \
    '{username: $u, password: $p}')
curl_exit=0
curl --connect-timeout 10 --max-time 30 -sS \
    -H 'Content-Type: application/json' \
    -X POST \
    --data-binary @- \
    -o "${login_response_file}" \
    https://hub.docker.com/v2/users/login/ <<< "${login_body}" || curl_exit=$?

if [[ "${curl_exit}" -ne 0 ]]; then
    echo "::error::Docker Hub login request failed before receiving an HTTP response (DNS, TLS, timeout, or connection error). curl exit: ${curl_exit}"
    exit 1
fi

# Parse .token. jq -r on missing/null keys emits the literal string
# "null" (not empty), so both "" and "null" mean "no usable token." A
# response body without a .token key also lands in the "null" bucket.
JWT=$(jq -r '.token // ""' < "${login_response_file}" 2>/dev/null || echo "")
if [[ -z "${JWT}" ]] || [[ "${JWT}" = "null" ]]; then
    echo "::error::Could not obtain Docker Hub JWT from login response (no .token field, or field was null/empty)."
    echo "::error::Response body (each line prefixed to prevent workflow-command injection):"
    # `sed 's/^/  /'` on every line + `tr -d '\r'` strips CR — prevents
    # a response containing `::error::` or `%0A` from being interpreted
    # as an injected GHA workflow command.
    sed 's/^/  /' "${login_response_file}" | tr -d '\r' || true
    exit 1
fi

# DELETE the tag. Same bounded-curl pattern as the login POST. HTTP-code
# semantics preserved from the inline block being extracted:
#   204 = deleted (success)
#   404 = already gone (also success — idempotent re-run)
#   *   = anything else is unexpected; surface the response body and
#         exit non-zero so the operator can see what happened.
delete_response_file=$(mktemp)
trap 'rm -f "${login_response_file}" "${token_file}" "${delete_response_file}"' EXIT

url="https://hub.docker.com/v2/repositories/${REPO}/tags/${TAG}/"
echo "==> DELETE ${url}"
curl_exit=0
http_code=$(curl --connect-timeout 10 --max-time 30 -sS \
    -X DELETE \
    -H "Authorization: JWT ${JWT}" \
    -o "${delete_response_file}" \
    -w '%{http_code}' \
    "${url}") || curl_exit=$?

if [[ "${curl_exit}" -ne 0 ]]; then
    echo "::error::Docker Hub DELETE request failed before receiving an HTTP response (DNS, TLS, timeout, or connection error). curl exit: ${curl_exit}"
    exit 1
fi

case "${http_code}" in
    204)
        echo "==> cleanup OK: ${REPO}:${TAG} deleted (HTTP 204)"
        ;;
    404)
        echo "==> cleanup OK: ${REPO}:${TAG} already gone (HTTP 404)"
        ;;
    *)
        echo "::error::Docker Hub tag delete returned HTTP ${http_code}. Response body (each line prefixed to prevent workflow-command injection):"
        # Same sanitization as the login-response echo above.
        sed 's/^/  /' "${delete_response_file}" | tr -d '\r' || true
        exit 1
        ;;
esac
