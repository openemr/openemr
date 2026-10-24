#!/usr/bin/env bash
#
# Capture the release-state baseline for a recovery-path smoketest run.
#
# The smoketest dispatches build-release / docker-build-release +
# acceptance-only / docker-acceptance-only workflows against the
# current-shipped rel-line's tag. The L4 runtime guardrail asserts
# that neither the git tag SHA nor the GitHub Release's mutation-
# visible state changed during the smoketest -- catching any
# gating regression that would allow the dispatched workflows to
# modify real release artifacts.
#
# This script captures those two values at the start of the smoketest.
# The verify script (recovery-smoketest-verify.sh) reads them back
# at the end and compares.
#
# Extracted from the previously-inline baseline capture blocks in
# recovery-path-smoketest.yml (tarball `smoketest` job + docker
# `smoketest-docker` job) so the logic lands once + gets BATS
# coverage independent of the workflow YAML.
#
# Inputs (env):
#   RELEASE_TAG    Git tag being smoketested (e.g. v8_4_0). Required.
#   REPO           Fully-qualified repo (e.g. openemr/openemr). Required.
#   GH_TOKEN       gh auth token. Required for `gh release view`.
#   GITHUB_ENV     Path to the workflow's env file (auto-set by GitHub
#                  Actions). Required. Script appends BASELINE_TAG_SHA
#                  and BASELINE_RELEASE_HASH to it.
#
# Exit codes:
#   0  baseline captured successfully
#   1  required env var missing
#   2  git ls-remote failed (network / auth) or tag missing on origin
#   3  gh release view failed (Release doesn't exist -- shouldn't
#      happen if assert-release-shipped.sh ran first, but guard
#      defensively)
#
# Baseline fields hashed together via sha256:
#   publishedAt, createdAt, body, name, isDraft, isPrerelease,
#   targetCommitish, assets (with per-asset `downloadCount` stripped
#   -- see below)
#
# body + name are `gh release edit --notes/--title` mutation-visible.
# isDraft + isPrerelease + targetCommitish are `gh release edit`
# mutation-visible. assets is `gh release upload --clobber` mutation-
# visible. publishedAt + createdAt round out the mutation surface.
#
# `downloadCount` is stripped from each asset because it increments
# on every download and the smoketest itself downloads the tarball
# as part of install-check -- leaving it in would false-positive the
# hash-unchanged guardrail on every run. All other asset fields
# (name, size, digest, contentType, url, etc.) are retained since
# they only change via `gh release upload --clobber`, which is what
# the guardrail defends against.
#
# Unit-tested by tests/bats/ci-scripts/recovery-smoketest-baseline/.

set -euo pipefail

if [[ -z "${RELEASE_TAG:-}" ]]; then
    echo "::error::recovery-smoketest-baseline.sh: RELEASE_TAG env var required" >&2
    exit 1
fi

if [[ -z "${REPO:-}" ]]; then
    echo "::error::recovery-smoketest-baseline.sh: REPO env var required" >&2
    exit 1
fi

if [[ -z "${GITHUB_ENV:-}" ]]; then
    echo "::error::recovery-smoketest-baseline.sh: GITHUB_ENV env var required (workflow-env file path -- normally auto-set by GitHub Actions)" >&2
    exit 1
fi

# Capture git tag SHA on origin. `if !` guard so that a git failure
# under `set -euo pipefail` doesn't abort the script before the
# exit-2 branch fires (bare `x=$(git ... | awk ...)` would let
# pipefail propagate git's exit and terminate before diagnostics).
if ! ls_remote_output=$(git ls-remote --tags origin "refs/tags/${RELEASE_TAG}" 2>&1); then
    echo "::error::recovery-smoketest-baseline.sh: git ls-remote failed for ${RELEASE_TAG}: ${ls_remote_output}" >&2
    exit 2
fi
tag_sha=$(printf '%s\n' "${ls_remote_output}" | awk '{print $1}')
if [[ -z "${tag_sha}" ]]; then
    echo "::error::recovery-smoketest-baseline.sh: git ls-remote returned no tag SHA for ${RELEASE_TAG} (tag doesn't exist on origin)" >&2
    exit 2
fi

# Capture GitHub Release payload. Same `if !` guard so a gh failure
# maps to exit 3 with diagnostics rather than a bare pipefail
# termination.
if ! release_json=$(gh release view "${RELEASE_TAG}" --repo "${REPO}" \
    --json publishedAt,createdAt,body,name,isDraft,isPrerelease,targetCommitish,assets \
    --jq '.assets |= map(del(.downloadCount))' 2>&1); then
    echo "::error::recovery-smoketest-baseline.sh: gh release view failed for ${RELEASE_TAG}: ${release_json} (Release doesn't exist? Run assert-release-shipped.sh first to fail fast on this)" >&2
    exit 3
fi

# sha256 the projected JSON. Keeps the env var to 64 chars
# regardless of body length (release notes for major versions can
# be many KB).
release_hash=$(printf '%s' "${release_json}" | sha256sum | awk '{print $1}')

{
    echo "BASELINE_TAG_SHA=${tag_sha}"
    echo "BASELINE_RELEASE_HASH=${release_hash}"
} >> "${GITHUB_ENV}"

echo "==> Baseline captured: tag SHA=${tag_sha}, release hash=${release_hash}"
