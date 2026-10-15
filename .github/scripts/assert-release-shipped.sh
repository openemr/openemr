#!/usr/bin/env bash
#
# Assert that a release tag has fully shipped: the git tag exists on
# origin AND a GitHub Release exists for that tag.
#
# Used by .github/workflows/recovery-path-smoketest.yml's L2 safety
# gate. The smoketest dispatches build-release / docker-build-release
# against the current-shipped rel-line's tag; if the tag or Release
# is somehow absent, we refuse to proceed rather than risk creating
# a NEW real tag or Release via any downstream gating regression.
#
# Callable independently -- any tool that needs to verify "this
# specific version has fully shipped end-to-end" can invoke this.
#
# Inputs (env):
#   RELEASE_TAG   Git tag in vMAJOR_MINOR_PATCH shape (e.g. v8_4_0).
#                 Required. Shape is not re-validated here; callers
#                 that need shape enforcement should validate first.
#   REPO          Fully-qualified repo (e.g. openemr/openemr). Used
#                 for `gh release view --repo`. Required.
#   GH_TOKEN      gh auth token. Required for the Release-existence
#                 check (git ls-remote works without auth on public
#                 repos).
#
# Exit codes:
#   0  both checks pass -- tag AND Release exist on origin
#   1  RELEASE_TAG unset or empty
#   2  tag does not exist on origin
#   3  GitHub Release does not exist for the tag
#
# Distinct exit codes for the two failure modes so callers can
# distinguish tag-missing vs Release-missing (rare but possible: a
# tag pushed manually without a corresponding Release object).
#
# Unit-tested by tests/bats/ci-scripts/assert-release-shipped/.

set -euo pipefail

if [[ -z "${RELEASE_TAG:-}" ]]; then
    echo "::error::assert-release-shipped.sh: RELEASE_TAG env var required" >&2
    exit 1
fi

if [[ -z "${REPO:-}" ]]; then
    echo "::error::assert-release-shipped.sh: REPO env var required" >&2
    exit 1
fi

# Guard 1: tag exists on origin.
#
# `git ls-remote --tags origin` queries the remote directly (avoids
# any local-cache staleness). Match on the exact refs/tags/<tag>
# line-end so "v8_4_0" doesn't accidentally match "v8_4_0_1".
if ! git ls-remote --tags origin "refs/tags/${RELEASE_TAG}" | grep -q "refs/tags/${RELEASE_TAG}$"; then
    {
        echo "::error::SAFETY GATE FAILED: tag ${RELEASE_TAG} does not exist on origin."
        echo "::error::Refusing to proceed -- any accidental tag push during a dry_run-gating regression could create a NEW real tag."
        echo "::error::If a fresh rel cut just landed with an incremented version, wait until that version actually ships (tag + Release published) before targeting it."
    } >&2
    exit 2
fi

# Guard 2: GitHub Release exists for that tag.
#
# `gh release view` returns non-zero when the tag has no associated
# Release object. Redirect stdout+stderr to /dev/null since we only
# care about the exit code (the API response body itself isn't
# useful for a boolean existence check).
if ! gh release view "${RELEASE_TAG}" --repo "${REPO}" >/dev/null 2>&1; then
    {
        echo "::error::SAFETY GATE FAILED: GitHub Release ${RELEASE_TAG} does not exist."
        echo "::error::Refusing to proceed -- any accidental Release create during a no_publish-gating regression could create a NEW real Release."
    } >&2
    exit 3
fi

echo "==> Safety gates confirmed: tag + Release exist on origin. Cannot stomp on ${RELEASE_TAG}."
