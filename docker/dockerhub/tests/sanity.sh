#!/usr/bin/env bash
# shellcheck disable=SC2312
#   Same rationale as render.sh: under `set -euo pipefail`, command
#   substitution failures propagate; the per-pipeline subprocess-status
#   nag adds noise without catching anything `set -e` doesn't already.
#
# Tier 1 sanity checks for the Docker Hub readme renderer.
#
# Runs render.sh against the live inputs and asserts structural properties
# of the output. Catches:
#   - Placeholders that the renderer forgot to substitute (template/renderer
#     getting out of sync on the placeholder names)
#   - Renderer dropping the release-bullet block, flex-bullet block, or the
#     "Current production OpenEMR version is X" headline
#   - Bullet counts that don't match the input data (renderer skipping rows,
#     or php_versions parsing breaking)
#   - Leaked openemr-devops paths or non-https links (catches stale content
#     port-overs after future template edits)
#   - Truncated output (silent template edit accidentally dropping a section)
#
# Does NOT catch (out of scope for Tier 1):
#   - Subtle wording changes that don't affect the structural assertions
#   - Tag-set drift between render.sh's flex-tag synthesis and
#     docker-build-flex-core.yml's Build tags step (Tier 3 / drift canary)
#
# Usage:
#   ./docker/dockerhub/tests/sanity.sh
#
# Exits 0 on all checks passing, non-zero with diagnostic output on any
# failure. CI invokes it as a workflow step before the push decision.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
DOCKERHUB_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
ROOT_DIR="$(cd "${DOCKERHUB_DIR}/../.." && pwd)"
RENDER_SCRIPT="${DOCKERHUB_DIR}/render.sh"
RELEASE_TARGETS="${ROOT_DIR}/.github/release-targets.yml"
WORKFLOWS_DIR="${ROOT_DIR}/.github/workflows"

# Render once into a temp file and inspect it. Everything downstream reads
# from this snapshot so a slow yq/jq doesn't run repeatedly.
TMPDIR=$(mktemp -d)
# shellcheck disable=SC2064  # Intentional early expansion -- TMPDIR is final
trap "rm -rf '${TMPDIR}'" EXIT
RENDERED="${TMPDIR}/rendered.md"

"${RENDER_SCRIPT}" "${RENDERED}"

# Pass/fail tally + a tiny assertion helper. Each `assert` records the
# outcome but never short-circuits, so a single run reports every failure
# instead of dribbling them out one CI cycle at a time.
PASS=0
FAIL=0
assert() {
    local label="$1" outcome="$2" detail="${3:-}"
    if [[ "${outcome}" == "pass" ]]; then
        echo "✓ ${label}"
        PASS=$((PASS + 1))
    else
        echo "✗ ${label}"
        [[ -n "${detail}" ]] && echo "    ${detail}"
        FAIL=$((FAIL + 1))
    fi
}

# ---------------------------------------------------------------------------
# 1. No unresolved placeholders. Catches renames in either template or
#    renderer that leave the other side referencing the old name.
# ---------------------------------------------------------------------------
UNRESOLVED=$(grep -oE '__[A-Z_]+__' "${RENDERED}" | sort -u | tr '\n' ' ' || true)
if [[ -z "${UNRESOLVED}" ]]; then
    assert "no unresolved __PLACEHOLDER__ tokens" pass
else
    assert "no unresolved __PLACEHOLDER__ tokens" fail "found: ${UNRESOLVED}"
fi

# ---------------------------------------------------------------------------
# 2. Headline version line present + looks version-shaped. The exact value
#    is checked against the row carrying `latest` (assertion 6 below).
# ---------------------------------------------------------------------------
HEADLINE=$(grep -E '^\*\*Current production OpenEMR version is [0-9]+(\.[0-9]+)+\*\*$' "${RENDERED}" || true)
if [[ -n "${HEADLINE}" ]]; then
    assert "headline line present + version-shaped" pass
else
    assert "headline line present + version-shaped" fail \
        "expected '**Current production OpenEMR version is X.Y.Z**'"
fi

# ---------------------------------------------------------------------------
# 3. Release bullet count matches release-targets.yml row count (excluding
#    `unreleased: true` placeholder rows, which the renderer skips).
# ---------------------------------------------------------------------------
EXPECTED_RELEASE_ROWS=$(yq -r '[.[] | select(.unreleased != true)] | length' "${RELEASE_TARGETS}")
# Release bullets link into docker/release on a branch; flex bullets link
# into docker/flex on master. Distinguish on the link substring.
ACTUAL_RELEASE_BULLETS=$(grep -cE '^\* `.*\[Dockerfile\]\(https://github\.com/openemr/openemr/blob/[^)]+/docker/release/Dockerfile\)' "${RENDERED}" || true)
if [[ "${EXPECTED_RELEASE_ROWS}" -eq "${ACTUAL_RELEASE_BULLETS}" ]]; then
    assert "release bullet count matches release-targets.yml row count (${EXPECTED_RELEASE_ROWS})" pass
else
    assert "release bullet count matches release-targets.yml row count" fail \
        "expected ${EXPECTED_RELEASE_ROWS}, rendered ${ACTUAL_RELEASE_BULLETS}"
fi

# ---------------------------------------------------------------------------
# 4. Flex bullet count matches sum of php_versions across discovered flex
#    callers. Mirrors render.sh's discovery filter so the test moves
#    automatically when a new flex caller lands.
# ---------------------------------------------------------------------------
EXPECTED_FLEX_BULLETS=0
for F in "${WORKFLOWS_DIR}"/docker-build-*.yml; do
    [[ -f "${F}" ]] || continue
    USES=$(yq -r '.jobs.build.uses // ""' "${F}")
    if [[ "${USES}" == *docker-build-flex-core.yml ]]; then
        N=$(yq -r '.jobs.build.with.php_versions' "${F}" | jq -r '. | length')
        EXPECTED_FLEX_BULLETS=$((EXPECTED_FLEX_BULLETS + N))
    fi
done
ACTUAL_FLEX_BULLETS=$(grep -cE '^\* `.*\[Dockerfile\]\(https://github\.com/openemr/openemr/blob/master/docker/flex/Dockerfile\)' "${RENDERED}" || true)
if [[ "${EXPECTED_FLEX_BULLETS}" -eq "${ACTUAL_FLEX_BULLETS}" ]]; then
    assert "flex bullet count matches sum(php_versions) across discovered callers (${EXPECTED_FLEX_BULLETS})" pass
else
    assert "flex bullet count matches sum(php_versions) across discovered callers" fail \
        "expected ${EXPECTED_FLEX_BULLETS}, rendered ${ACTUAL_FLEX_BULLETS}"
fi

# ---------------------------------------------------------------------------
# 5. No leaked openemr-devops paths. The template was ported from devops;
#    a stale path slipping back in during future edits is the kind of
#    regression this test exists to catch. Exception: the kubernetes README
#    link points at openemr-devops by design (kubernetes manifests stay
#    in devops per the planning doc).
# ---------------------------------------------------------------------------
LEAKED=$(grep -oE 'openemr-devops/[^) ]*' "${RENDERED}" \
    | grep -v -E 'openemr-devops/tree/master/kubernetes' \
    | sort -u || true)
if [[ -z "${LEAKED}" ]]; then
    assert "no stale openemr-devops paths (kubernetes link exempt)" pass
else
    assert "no stale openemr-devops paths (kubernetes link exempt)" fail "$(printf '%s\n' "${LEAKED}" | tr '\n' ' ')"
fi

# ---------------------------------------------------------------------------
# 6. Headline version matches the version-number tag from the row carrying
#    `latest` in release-targets.yml. Catches the renderer using a different
#    row's version (or picking the wrong tag from a multi-tag row).
# ---------------------------------------------------------------------------
EXPECTED_LATEST=$(yq -r '.[] | select(.unreleased != true) | select(.docker_tags | split(",") | map(. == "latest") | any) | .docker_tags' "${RELEASE_TARGETS}" \
    | head -1 | tr ',' '\n' | grep -E '^[0-9]+(\.[0-9]+)+$' | head -1)
ACTUAL_LATEST=$(grep -oE 'Current production OpenEMR version is [0-9]+(\.[0-9]+)+' "${RENDERED}" \
    | sed 's/Current production OpenEMR version is //')
if [[ -n "${EXPECTED_LATEST}" ]] && [[ "${EXPECTED_LATEST}" == "${ACTUAL_LATEST}" ]]; then
    assert "headline version (${ACTUAL_LATEST}) matches release-targets.yml row carrying 'latest'" pass
else
    assert "headline version matches release-targets.yml row carrying 'latest'" fail \
        "expected '${EXPECTED_LATEST}', rendered '${ACTUAL_LATEST}'"
fi

# ---------------------------------------------------------------------------
# 7. Dated example matches LATEST-YYYY-MM-DD shape with today's UTC date.
# ---------------------------------------------------------------------------
TODAY=$(date -u +%Y-%m-%d)
EXPECTED_DATED="${EXPECTED_LATEST}-${TODAY}"
if grep -qF "${EXPECTED_DATED}" "${RENDERED}"; then
    assert "dated example uses current latest version + today's UTC date (${EXPECTED_DATED})" pass
else
    assert "dated example uses current latest version + today's UTC date" fail \
        "expected to find '${EXPECTED_DATED}' literally"
fi

# ---------------------------------------------------------------------------
# 8. Key env-var prose markers present. Catches a future template edit that
#    accidentally drops a documentation section. Not exhaustive -- just the
#    headliners.
# ---------------------------------------------------------------------------
MISSING_MARKERS=()
for marker in 'MYSQL_HOST' 'MYSQL_ROOT_PASS' 'OE_USER' 'OE_PASS' 'REDIS_SERVER' 'FLEX_REPOSITORY' 'XDEBUG_ON' 'SWARM_MODE' 'DOMAIN' 'EMAIL'; do
    grep -qF "${marker}" "${RENDERED}" || MISSING_MARKERS+=("${marker}")
done
if [[ ${#MISSING_MARKERS[@]} -eq 0 ]]; then
    assert "key env-var documentation markers present" pass
else
    assert "key env-var documentation markers present" fail \
        "missing: ${MISSING_MARKERS[*]}"
fi

# ---------------------------------------------------------------------------
# 9. Output is non-trivially long. A silent template truncation (e.g., a
#    bad sed substitution lopping off the upgrade paragraph) would slip past
#    every other check. Threshold is generous: today's output is ~41 lines.
# ---------------------------------------------------------------------------
LINES=$(wc -l < "${RENDERED}")
MIN_LINES=20
if [[ "${LINES}" -ge "${MIN_LINES}" ]]; then
    assert "rendered output >= ${MIN_LINES} lines (actual ${LINES})" pass
else
    assert "rendered output >= ${MIN_LINES} lines" fail "actual ${LINES}"
fi

# ---------------------------------------------------------------------------
# 10. Every release-targets.yml branch's Dockerfile link occurs in the
#     rendered output exactly as many times as it has non-unreleased rows.
#     A single-row branch -> 1 bullet. A multi-row branch (e.g., rel-810
#     during dev mode with both a new-dev row and a prior-stable row) ->
#     N bullets. Rows flagged `unreleased: true` don't count -- the
#     renderer skips them. Catches a row silently getting skipped (which
#     check 3 also catches via count), AND a row getting rendered against
#     the wrong branch (which check 3 wouldn't catch).
# ---------------------------------------------------------------------------
declare -A EXPECTED_BULLETS_PER_BRANCH
while read -r BRANCH; do
    EXPECTED_BULLETS_PER_BRANCH[$BRANCH]=$(( ${EXPECTED_BULLETS_PER_BRANCH[$BRANCH]:-0} + 1 ))
done < <(yq -r '.[] | select(.unreleased != true) | .branch' "${RELEASE_TARGETS}")
MISSING_BRANCHES=()
for BRANCH in "${!EXPECTED_BULLETS_PER_BRANCH[@]}"; do
    EXPECTED="${EXPECTED_BULLETS_PER_BRANCH[$BRANCH]}"
    OCCURRENCES=$(grep -cE "blob/${BRANCH}/docker/release/Dockerfile" "${RENDERED}" || true)
    if [[ "${OCCURRENCES}" -ne "${EXPECTED}" ]]; then
        MISSING_BRANCHES+=("${BRANCH}(${OCCURRENCES}/expected ${EXPECTED})")
    fi
done
if [[ ${#MISSING_BRANCHES[@]} -eq 0 ]]; then
    assert "every release-targets.yml branch appears as the expected number of release bullets" pass
else
    assert "every release-targets.yml branch appears as the expected number of release bullets" fail \
        "branch(actual/expected) mismatch: ${MISSING_BRANCHES[*]}"
fi

# ---------------------------------------------------------------------------
# 11. Release ordering -- first release bullet carries `latest` (current
#     production is at the top), and `dev`/`next` only appear in the LAST
#     release bullet (active dev is at the bottom). Mirrors the convention
#     the renderer enforces -- if the bucketing or sort flips, this catches it.
# ---------------------------------------------------------------------------
RELEASE_BULLET_LINES=$(grep -nE '^\* `.*\[Dockerfile\]\(https://github\.com/openemr/openemr/blob/[^)]+/docker/release/Dockerfile\)' "${RENDERED}" | cut -d: -f1)
FIRST_RELEASE_LINE=$(printf '%s\n' "${RELEASE_BULLET_LINES}" | head -1)
LAST_RELEASE_LINE=$(printf '%s\n' "${RELEASE_BULLET_LINES}" | tail -1)
ORDER_ERRORS=()
LATEST_PRESENT=$(yq -r '.[] | select(.unreleased != true) | select(.docker_tags | split(",") | map(. == "latest") | any) | .branch' "${RELEASE_TARGETS}" | head -1)
if [[ -n "${LATEST_PRESENT}" ]]; then
    # Backticks below are literal markdown code-span delimiters; single
    # quotes prevent shell interpretation, not expansion.
    # shellcheck disable=SC2016
    if ! sed -n "${FIRST_RELEASE_LINE}p" "${RENDERED}" | grep -q '`latest`'; then
        ORDER_ERRORS+=("first release bullet missing \`latest\`")
    fi
fi
DEV_NEXT_PRESENT=$(yq -r '.[] | select(.unreleased != true) | select(.docker_tags | split(",") | map(. == "dev" or . == "next") | any) | .branch' "${RELEASE_TARGETS}" | head -1)
if [[ -n "${DEV_NEXT_PRESENT}" ]]; then
    # shellcheck disable=SC2016
    if ! sed -n "${LAST_RELEASE_LINE}p" "${RENDERED}" | grep -qE '`(dev|next)`'; then
        ORDER_ERRORS+=("last release bullet missing \`dev\` or \`next\`")
    fi
fi
if [[ ${#ORDER_ERRORS[@]} -eq 0 ]]; then
    assert "release bullet order: latest first, dev/next last" pass
else
    assert "release bullet order: latest first, dev/next last" fail "${ORDER_ERRORS[*]}"
fi

# ---------------------------------------------------------------------------
# 12. Flex ordering -- any `flex-edge` bullet must come AFTER every non-edge
#     `flex-*` bullet. Catches the bucketing-or-sort breakage that would put
#     edge anywhere except last among the flex bullets.
# ---------------------------------------------------------------------------
FLEX_BULLET_LINES=$(grep -nE '^\* `.*\[Dockerfile\]\(https://github\.com/openemr/openemr/blob/master/docker/flex/Dockerfile\)' "${RENDERED}" | cut -d: -f1)
FIRST_EDGE_LINE=""
LAST_NON_EDGE_LINE=""
if [[ -n "${FLEX_BULLET_LINES}" ]]; then
    while read -r LINE_NO; do
        if sed -n "${LINE_NO}p" "${RENDERED}" | grep -q '`flex-edge'; then
            [[ -z "${FIRST_EDGE_LINE}" ]] && FIRST_EDGE_LINE="${LINE_NO}"
        else
            LAST_NON_EDGE_LINE="${LINE_NO}"
        fi
    done <<< "${FLEX_BULLET_LINES}"
fi
if [[ -z "${FIRST_EDGE_LINE}" ]] || [[ -z "${LAST_NON_EDGE_LINE}" ]] \
    || [[ "${FIRST_EDGE_LINE}" -gt "${LAST_NON_EDGE_LINE}" ]]; then
    assert "flex bullet order: edge follows all non-edge flex bullets" pass
else
    assert "flex bullet order: edge follows all non-edge flex bullets" fail \
        "first edge at line ${FIRST_EDGE_LINE} but last non-edge at line ${LAST_NON_EDGE_LINE}"
fi

# ---------------------------------------------------------------------------
# Report.
# ---------------------------------------------------------------------------
echo
echo "Result: ${PASS} pass, ${FAIL} fail"
if [[ "${FAIL}" -gt 0 ]]; then
    echo
    echo "Rendered output for debugging:"
    echo "---"
    cat "${RENDERED}"
    echo "---"
    exit 1
fi
