#!/usr/bin/env bash
#
# Verify that a Docker Hub tag exists (remote existence probe).
#
# Called from docker-acceptance-only.yml as a fail-fast guardrail: if
# cleanup-candidate somehow ran despite acceptance failure, or an operator
# manually deleted the candidate before recovering, the HEAD-like GET here
# fails fast with a clear error -- much better than acceptance-docker.yml
# failing later with a confusing "image pull failed" message deep in the
# matrix.
#
# Uses the anonymous Docker Hub v2 API tag endpoint: no login needed for
# an existence check.
#
# Extracted from the "Verify candidate tag exists on Docker Hub" step in
# docker-acceptance-only.yml so the curl bounds + response-code branching
# live in one place. Kept separate from validate-source-run.sh because
# this is a remote-state check (needs network + curl mock in tests) --
# validate-source-run.sh is a pure JSON parse.
#
# Inputs (env)
#   TAG    required. The tag to probe (e.g. "release-candidate-30443247578-1").
#   REPO   optional, default "openemr/openemr". Docker Hub repo path.
#
# Stdout / stderr
#   Progress lines to stdout, `::error::` annotations on any failure.
#   On unexpected non-200/404 responses, the response body is dumped to
#   stdout for post-mortem context.
#
# Exit codes
#   0   tag exists (HTTP 200)
#   1   tag missing (HTTP 404), unexpected HTTP status, or curl-level
#       failure (DNS, TLS, timeout, connection reset -- anything that
#       prevents an HTTP response)

set -euo pipefail

: "${TAG:?TAG must be set (Docker Hub tag to probe, e.g. release-candidate-12345-1)}"
REPO="${REPO:-openemr/openemr}"

url="https://hub.docker.com/v2/repositories/${REPO}/tags/${TAG}/"
echo "==> GET ${url}"

# Bound the request explicitly: --connect-timeout guards TCP/TLS setup,
# --max-time guards total wall time. Without these, curl's defaults are
# effectively unbounded and a hung Docker Hub could stall the job until
# the runner's own timeout fires.
#
# Capture curl's exit code explicitly (via `|| curl_exit=$?`) so a
# curl-level failure (DNS, TLS, timeout, connection reset -- anything
# that prevents an HTTP response) produces a clear error instead of
# tripping `set -e` and leaving the case-statement handler unreached.
# `|| ...` disarms `set -e` for this one call; we re-arm by explicitly
# checking $curl_exit.
response_body_file=$(mktemp)
trap 'rm -f "${response_body_file}"' EXIT

curl_exit=0
http_code=$(curl --connect-timeout 10 --max-time 30 -sS \
    -o "${response_body_file}" -w '%{http_code}' "${url}") || curl_exit=$?
if [[ "${curl_exit}" -ne 0 ]]; then
    echo "::error::Docker Hub request failed before receiving an HTTP response (DNS, TLS, timeout, or connection error). curl exit: ${curl_exit}"
    exit 1
fi

case "${http_code}" in
    200)
        echo "==> Tag ${REPO}:${TAG} exists on Docker Hub."
        ;;
    404)
        echo "::error::Tag ${REPO}:${TAG} not found on Docker Hub (HTTP 404)."
        echo "::error::The candidate may have been already cleaned up (successful publish path deletes it) or manually deleted."
        echo "::error::Recovery paths: (1) if the source run's publish succeeded, no recovery needed; (2) if the source run failed BEFORE publish and the candidate is gone anyway, dispatch docker-build-release.yml fresh."
        exit 1
        ;;
    *)
        echo "::error::Unexpected HTTP ${http_code} from Docker Hub v2 API. Response:"
        cat "${response_body_file}" || true
        exit 1
        ;;
esac
