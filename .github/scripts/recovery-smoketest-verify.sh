#!/usr/bin/env bash
#
# Verify the release-state baseline captured by recovery-smoketest-
# baseline.sh has not drifted during a recovery-path smoketest run.
#
# Two-signal L4 guardrail:
#
#   1. Tag SHA + Release-state hash unchanged since baseline. Catches
#      any silent gating regression that let the dispatched workflows
#      modify a real release artifact (`git push tag`, `gh release
#      create`, `gh release edit`, `gh release upload --clobber`).
#
#   2. Publish job = 'skipped' on each dispatched acceptance-only /
#      docker-acceptance-only run. Direct signal that the
#      `if: inputs.no_publish != true` gate held (belt-and-suspenders
#      with the state-unchanged check above).
#
# Extracted from previously-inline verify blocks in recovery-path-
# smoketest.yml (tarball `smoketest` job + docker `smoketest-docker`
# job) so the logic lands once + gets BATS coverage independent of
# the workflow YAML.
#
# Inputs (env):
#   RELEASE_TAG               Same value captured at baseline. Required.
#   REPO                      Fully-qualified repo. Required.
#   GH_TOKEN                  gh auth token. Required.
#   BASELINE_TAG_SHA          From baseline step. Required.
#   BASELINE_RELEASE_HASH     From baseline step. Required.
#
# Inputs (positional args):
#   Zero or more "label:run_id" pairs to check for publish-job-skipped.
#   Label is a human-readable name that appears in error messages
#   ("variant-A", "variant-B"). Empty run_id is skipped silently (the
#   run wasn't dispatched -- some earlier step already failed and
#   emitted its own error).
#
#   Example: variant-A:12345 variant-B:67890
#
# Exit codes:
#   0  all checks pass
#   1  required env var missing
#   2  one or more guardrail checks failed (::error:: annotations
#      already emitted; caller should surface run URL + investigate)
#
# Emits GITHUB_STEP_SUMMARY section on success (skipped on failure --
# each ::error:: line already surfaces in the run's Summary UI).
#
# Unit-tested by tests/bats/ci-scripts/recovery-smoketest-verify/.

set -euo pipefail

if [[ -z "${RELEASE_TAG:-}" ]]; then
    echo "::error::recovery-smoketest-verify.sh: RELEASE_TAG env var required" >&2
    exit 1
fi

if [[ -z "${REPO:-}" ]]; then
    echo "::error::recovery-smoketest-verify.sh: REPO env var required" >&2
    exit 1
fi

if [[ -z "${BASELINE_TAG_SHA:-}" ]]; then
    # Not a hard error -- if baseline was never captured (target
    # picker failed), there's nothing to verify against. Callers
    # invoke this step with `if: always()`, so we tolerate the case
    # gracefully rather than red-flagging a smoketest that failed
    # for unrelated reasons.
    echo "==> Baseline not captured (target picker never ran or failed); nothing to verify."
    exit 0
fi

if [[ -z "${BASELINE_RELEASE_HASH:-}" ]]; then
    echo "==> Baseline release hash not captured; nothing to verify."
    exit 0
fi

FAILED=0

# All probes below use `if ! output=$(...)` guards. Under
# `set -euo pipefail` a bare `x=$(cmd | ...)` exits the script on
# any pipeline failure, which would skip the FAILED=1 sentinel,
# skip later checks, and never reach the documented exit-2
# aggregate. `if !` neutralizes set -e for the assignment while
# still capturing exit status.

# Check 1: tag SHA unchanged.
if ! ls_remote_output=$(git ls-remote --tags origin "refs/tags/${RELEASE_TAG}" 2>&1); then
    echo "::error::GUARDRAIL FAILED: git ls-remote failed for ${RELEASE_TAG}: ${ls_remote_output}" >&2
    current_tag_sha="<ls-remote-failed>"
    FAILED=1
else
    current_tag_sha=$(printf '%s\n' "${ls_remote_output}" | awk '{print $1}')
    if [[ -z "${current_tag_sha}" ]]; then
        current_tag_sha="<tag-missing>"
    fi
fi
if [[ "${current_tag_sha}" != "${BASELINE_TAG_SHA}" ]]; then
    {
        echo "::error::GUARDRAIL FAILED: tag ${RELEASE_TAG} SHA changed during smoketest."
        echo "::error::  baseline: ${BASELINE_TAG_SHA}"
        echo "::error::   current: ${current_tag_sha}"
        echo "::error::This indicates a dry_run-gating regression let a tag push through. Investigate immediately -- the shipped tag now points at a different commit than at ship time."
    } >&2
    FAILED=1
fi

# Check 2: Release state hash unchanged. Use IDENTICAL fields +
# projection as baseline capture -- see recovery-smoketest-
# baseline.sh comment on `downloadCount` stripping.
if ! release_json=$(gh release view "${RELEASE_TAG}" --repo "${REPO}" \
    --json publishedAt,createdAt,body,name,isDraft,isPrerelease,targetCommitish,assets \
    --jq '.assets |= map(del(.downloadCount))' 2>/dev/null); then
    current_release_hash="<release-missing-or-view-failed>"
    FAILED=1
else
    current_release_hash=$(printf '%s' "${release_json}" | sha256sum | awk '{print $1}')
fi
if [[ "${current_release_hash}" != "${BASELINE_RELEASE_HASH}" ]]; then
    {
        echo "::error::GUARDRAIL FAILED: GitHub Release ${RELEASE_TAG} state hash changed during smoketest."
        echo "::error::  baseline: ${BASELINE_RELEASE_HASH}"
        echo "::error::   current: ${current_release_hash}"
        echo "::error::A field covered by the guardrail changed: body / name / isDraft / isPrerelease / targetCommitish / publishedAt / createdAt / any asset (excluding per-asset downloadCount, which mutates on downloads). This indicates a no_publish-gating regression let a Release mutation through. Investigate immediately."
    } >&2
    FAILED=1
fi

# Check 3: publish job = 'skipped' on each supplied run.
#
# Positional args are "label:run_id" pairs. Label is a human-
# readable name for error messages; run_id can be empty (the run
# wasn't dispatched, some earlier step failed) and we silently
# skip -- earlier failure already produced its own annotation.
#
# Filter matches any job whose name contains "publish"
# (case-insensitive). Both acceptance-only.yml ("Publish release")
# and docker-acceptance-only.yml ("Publish + cleanup") satisfy
# this pattern. `first // empty` picks the first match in jq (no
# `head -1` pipe -- that would SIGPIPE gh under pipefail if gh
# emitted multiple lines).
for var_pair in "$@"; do
    label="${var_pair%%:*}"
    rid="${var_pair#*:}"
    if [[ -z "${rid}" ]]; then
        continue
    fi
    if ! publish_status=$(gh run view "${rid}" --repo "${REPO}" \
        --json jobs \
        --jq '[.jobs[] | select(.name | test("(?i)publish")) | .conclusion] | first // empty' 2>/dev/null); then
        echo "::error::GUARDRAIL FAILED (${label}): gh run view failed for run ${rid}." >&2
        FAILED=1
        continue
    fi
    if [[ "${publish_status}" != "skipped" ]]; then
        echo "::error::GUARDRAIL FAILED (${label}): publish job conclusion = '${publish_status:-<not-found>}' (expected 'skipped'). no_publish gate silently failed on run ${rid}." >&2
        FAILED=1
    else
        echo "==> ${label} publish job: skipped (gate held)"
    fi
done

if [[ ${FAILED} -eq 1 ]]; then
    exit 2
fi

echo "==> Guardrail OK: tag SHA + Release state hash unchanged since pre-dispatch baseline; publish jobs skipped on all supplied runs."

if [[ -n "${GITHUB_STEP_SUMMARY:-}" ]]; then
    {
        echo ""
        echo "### Guardrail verify"
        echo ""
        echo "- Result: **PASSED**"
        echo "- Tag SHA: unchanged (\`${current_tag_sha}\`)"
        echo "- Release state hash: unchanged (\`${current_release_hash}\`)"
        if [[ $# -gt 0 ]]; then
            echo "- Publish jobs = skipped on: $*"
        fi
    } >> "${GITHUB_STEP_SUMMARY}"
fi
