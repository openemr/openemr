#!/usr/bin/env bash
#
# Validate a source workflow run for the acceptance-only recovery path.
#
# Both acceptance-only.yml (tarball recovery) and docker-acceptance-only.yml
# (docker recovery) previously carried near-identical inline blocks that
# validated a source_run_id operator input against three (docker: four)
# guardrails:
#
#   1. wrong-workflow    -- .path must match EXPECTED_WORKFLOW_PATH
#   2. wrong-coordinates -- optional: candidate_tag input must equal
#                           "release-candidate-<run_id>-<run_attempt>"
#                           (docker-side only; skipped when
#                           EXPECTED_CANDIDATE_TAG is unset)
#   3. too-old           -- .created_at must be within 48h of now
#
# Guard ordering: cheapest-first (wrong-workflow -> wrong-coordinates ->
# too-old). All three are pure text/arithmetic on the run JSON; a network
# existence check (docker-side "candidate still on Docker Hub") lives in a
# separate helper, verify-dockerhub-tag-exists.sh, so this script stays
# offline-testable.
#
# Extracted so a fix to any guard (a different age ceiling, an additional
# metadata check, a richer error hint) lands once instead of twice.
#
# Inputs (env)
#   SOURCE_RUN_ID              required. Operator-supplied workflow run
#                              ID; used only for error-message context
#                              (the RUN_JSON is the authoritative shape).
#   EXPECTED_WORKFLOW_PATH     required. e.g.
#                              ".github/workflows/build-release.yml"
#                              (tarball) or
#                              ".github/workflows/docker-build-release.yml"
#                              (docker).
#   RUN_JSON                   required. JSON blob from
#                              `gh api repos/{owner}/{repo}/actions/runs/{id}`.
#                              Read from stdin when the env var is unset
#                              or empty; env is the primary form so
#                              callers can pipe-avoid.
#   EXPECTED_CANDIDATE_TAG     optional. When set, enforces
#                              CANDIDATE_TAG (also required if set) equals
#                              "release-candidate-${SOURCE_RUN_ID}-${.run_attempt}".
#                              Docker-side only.
#   CANDIDATE_TAG              required only when EXPECTED_CANDIDATE_TAG is
#                              enforced. Value the operator typed at
#                              dispatch time.
#   AGE_CEILING_HOURS          optional, default 48. Age ceiling; primarily
#                              a test hook (production callers rely on
#                              the default).
#
# Stdout / stderr
#   Progress lines to stdout, `::error::` annotations on any failure.
#
# Exit codes
#   0   all validations passed
#   1   any validation failed OR required env var missing OR RUN_JSON
#       missing a required field

set -euo pipefail

: "${SOURCE_RUN_ID:?SOURCE_RUN_ID must be set (source workflow run ID)}"
: "${EXPECTED_WORKFLOW_PATH:?EXPECTED_WORKFLOW_PATH must be set (e.g. .github/workflows/build-release.yml)}"

AGE_CEILING_HOURS="${AGE_CEILING_HOURS:-48}"

# Load RUN_JSON from env or stdin. Env is primary so callers running
# `gh api ... | validate-source-run.sh` and callers that already captured
# the JSON into a variable both work without extra glue.
if [[ -z "${RUN_JSON:-}" ]]; then
    RUN_JSON=$(cat)
fi

if [[ -z "${RUN_JSON}" ]]; then
    echo "::error::RUN_JSON is empty (neither the env var nor stdin supplied a run JSON blob)."
    exit 1
fi

# jq -e turns absent/null values into a non-zero exit so a malformed
# blob fails here rather than at the comparison step with a confusing
# empty-string message.
source_path=$(printf '%s' "${RUN_JSON}" | jq -er .path) || {
    echo "::error::RUN_JSON missing required field '.path' (source run metadata is malformed or a different shape than expected)."
    exit 1
}
source_created_at=$(printf '%s' "${RUN_JSON}" | jq -er .created_at) || {
    echo "::error::RUN_JSON missing required field '.created_at' (source run metadata is malformed or a different shape than expected)."
    exit 1
}

# Guard 1 (cheapest): workflow_path.
#
# Uses .path rather than .name to avoid brittleness if the workflow name
# is edited (path is the file location, which is more stable). GitHub's
# workflow listing endpoint returns .path as ".github/workflows/<file>".
echo "==> Confirming source run is a ${EXPECTED_WORKFLOW_PATH##*/} run"
if [[ "${source_path}" != "${EXPECTED_WORKFLOW_PATH}" ]]; then
    echo "::error::source run ${SOURCE_RUN_ID} was produced by '${source_path}', not '${EXPECTED_WORKFLOW_PATH}'."
    echo "::error::this recovery workflow only accepts source runs from ${EXPECTED_WORKFLOW_PATH##*/} -- the artifact/tag shape is specific to that workflow."
    exit 1
fi

# Guard 2 (medium): candidate_tag coordinates binding (docker-side only).
#
# Bind operator-supplied candidate_tag to the source run's own
# coordinates. Without this check, an operator could dispatch a docker
# recovery workflow with a valid source_run_id but a candidate_tag
# pointing at an unrelated image on Docker Hub -- aliasing that foreign
# image under the requested final tags. Match the exact format
# docker-build-release.yml stamps via GATE_CANDIDATE_TAG env:
# release-candidate-<run_id>-<run_attempt>.
if [[ -n "${EXPECTED_CANDIDATE_TAG:-}" ]]; then
    : "${CANDIDATE_TAG:?CANDIDATE_TAG must be set when EXPECTED_CANDIDATE_TAG enforcement is enabled}"

    source_run_attempt=$(printf '%s' "${RUN_JSON}" | jq -er .run_attempt) || {
        echo "::error::RUN_JSON missing required field '.run_attempt' (needed for candidate_tag coordinates binding)."
        exit 1
    }

    echo "==> Confirming candidate_tag matches source run coordinates"
    expected_candidate_tag="release-candidate-${SOURCE_RUN_ID}-${source_run_attempt}"
    if [[ "${CANDIDATE_TAG}" != "${expected_candidate_tag}" ]]; then
        echo "::error::candidate_tag mismatch: operator supplied '${CANDIDATE_TAG}' but source run ${SOURCE_RUN_ID} (attempt ${source_run_attempt}) would have produced '${expected_candidate_tag}'."
        echo "::error::docker-acceptance-only aliases the candidate image under the final tags -- a mismatched candidate_tag risks publishing an unrelated image (accidentally or maliciously). Re-dispatch with candidate_tag='${expected_candidate_tag}', or pick a different source_run_id whose coordinates match the intended candidate."
        exit 1
    fi

    # Enforcement check upstream expected the same value; assert equality
    # so callers can trust EXPECTED_CANDIDATE_TAG === CANDIDATE_TAG after
    # this passes.
    if [[ "${EXPECTED_CANDIDATE_TAG}" != "${expected_candidate_tag}" ]]; then
        echo "::error::EXPECTED_CANDIDATE_TAG ('${EXPECTED_CANDIDATE_TAG}') doesn't match the derived candidate_tag ('${expected_candidate_tag}'). Caller likely computed EXPECTED_CANDIDATE_TAG from stale inputs."
        exit 1
    fi
fi

# Guard 3 (still cheap but last): age ceiling.
#
# Use date -d for portable ISO-8601 parsing; both dates are UTC by
# convention so no TZ juggling needed.
echo "==> Checking source run age (<= ${AGE_CEILING_HOURS}h)"
source_epoch=$(date -d "${source_created_at}" +%s) || {
    echo "::error::source run created_at '${source_created_at}' is not a parseable timestamp."
    exit 1
}
now_epoch=$(date -u +%s)
age_seconds=$(( now_epoch - source_epoch ))
age_hours=$(( age_seconds / 3600 ))
ceiling_seconds=$(( AGE_CEILING_HOURS * 3600 ))

echo "    source_epoch=${source_epoch} now_epoch=${now_epoch} age_hours=${age_hours}"

if [[ "${age_seconds}" -gt "${ceiling_seconds}" ]]; then
    echo "::error::source run ${SOURCE_RUN_ID} is ${age_hours}h old (> ${AGE_CEILING_HOURS}h ceiling)."
    echo "::error::An artifact this old risks being stale -- the rel-branch may have advanced since build, and re-running acceptance against the old bundle would validate a version the branch has already moved past."
    echo "::error::Recovery paths for stale runs: (1) dispatch the source workflow fresh with the same inputs to rebuild + re-gate from current rel-branch tip; (2) re-run the failed source run in-place via the Actions UI (rebuilds from the same commit the annotated tag points at, which is still the ship point)."
    exit 1
fi

echo "==> source run eligible: age=${age_hours}h, workflow=${EXPECTED_WORKFLOW_PATH##*/}"
