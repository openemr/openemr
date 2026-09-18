#!/usr/bin/env bash
# shellcheck disable=SC2312
#   Rationale: SC2312 fires on every command substitution and pipe step under
#   `set -euo pipefail`, because shellcheck can't statically prove the return
#   value is checked. `set -e` and `set -o pipefail` already cover those
#   cases for this script's intent -- pure render-or-die, no per-step
#   recovery needed. Suppressing wholesale to keep the script readable.
#
# Render docker/dockerhub/overview.md into the markdown that ships to
# Docker Hub's repo description for openemr/openemr.
#
# Input sources:
#   * .github/release-targets.yml         -- production release rows
#   * .github/workflows/docker-build-*.yml -- discovered at runtime; the ones
#                                            whose .jobs.build.uses points at
#                                            docker-build-flex-core.yml are
#                                            the flex callers
#   * docker/dockerhub/overview.md        -- markdown template with placeholders
#
# Four placeholders substituted in the template:
#   __CURRENT_LATEST_VERSION__   -- version-number tag from the release-targets
#                                   row that carries `latest` (e.g. 8.1.0)
#   __SUPPORTED_TAGS_RELEASE__   -- bullet block, one bullet per release-targets
#                                   row in file order, each listing that row's
#                                   docker_tags + a Dockerfile/Instructions link
#                                   to that row's branch.
#   __SUPPORTED_TAGS_FLEX__      -- bullet block, one bullet per (alpine_version,
#                                   php_version) pair across the discovered
#                                   flex caller workflows; each bullet replicates
#                                   docker-build-flex-core.yml's "Build tags"
#                                   logic so the rendered tag set matches what
#                                   the build pushes.
#   __DATED_EXAMPLE__            -- example dated tag using the current "latest"
#                                   version + today's UTC date (8.1.0-2026-06-17).
#
# Usage:
#   ./docker/dockerhub/render.sh [output-path]
#
# Defaults the output to stdout. CI passes ${RUNNER_TEMP}/dockerhub-readme.md.

set -euo pipefail

# The three RENDER_*_OVERRIDE env vars exist so the Tier 2 golden test
# can drive the renderer against fixture inputs from
# docker/dockerhub/tests/fixtures/ while still exercising the production
# template. Unset in normal CI/production use; render.sh resolves the
# live paths.
ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
TEMPLATE="${ROOT_DIR}/docker/dockerhub/overview.md"
RELEASE_TARGETS="${RENDER_RELEASE_TARGETS_OVERRIDE:-${ROOT_DIR}/.github/release-targets.yml}"
WORKFLOWS_DIR="${RENDER_WORKFLOWS_DIR_OVERRIDE:-${ROOT_DIR}/.github/workflows}"

OUT="${1:-/dev/stdout}"

for f in "${TEMPLATE}" "${RELEASE_TARGETS}"; do
    [[ -f "${f}" ]] || { echo "missing input: ${f}" >&2; exit 1; }
done

TMPDIR=$(mktemp -d)
# shellcheck disable=SC2064  # Intentional early expansion -- TMPDIR is final
trap "rm -rf '${TMPDIR}'" EXIT

# ---------------------------------------------------------------------------
# Discover flex caller workflows: anything matching docker-build-*.yml whose
# .jobs.build.uses points at docker-build-flex-core.yml. Adding a
# docker-build-324.yml (or similar) later is automatically picked up; the
# release-only docker-build-release.yml and the reusable
# docker-build-flex-core.yml itself are skipped because they don't match
# the structural signature.
# ---------------------------------------------------------------------------
mapfile -t FLEX_CALLERS < <(
    for F in "${WORKFLOWS_DIR}"/docker-build-*.yml; do
        # glob expands to a literal pattern when no files match -- skip that.
        [[ -f "${F}" ]] || continue
        USES=$(yq -r '.jobs.build.uses // ""' "${F}")
        if [[ "${USES}" == *docker-build-flex-core.yml ]]; then
            printf '%s\n' "${F}"
        fi
    done | sort
)

if (( ${#FLEX_CALLERS[@]} == 0 )); then
    echo "warning: no flex caller workflows discovered under ${WORKFLOWS_DIR}/docker-build-*.yml" >&2
fi

# ---------------------------------------------------------------------------
# 1) CURRENT_LATEST_VERSION -- the version-number tag from the row carrying
#    `latest`. release-targets-validator already enforces that exactly one row
#    can publish `latest`, so the head -1 is defensive only.
# ---------------------------------------------------------------------------
LATEST_DOCKER_TAGS=$(yq -r '.[] | select(.unreleased != true) | select(.docker_tags | split(",") | map(. == "latest") | any) | .docker_tags' "${RELEASE_TARGETS}" | head -1)
if [[ -z "${LATEST_DOCKER_TAGS}" ]]; then
    echo "warning: no release-targets.yml row carries the 'latest' tag" >&2
    LATEST_VERSION="unknown"
else
    LATEST_VERSION=$(printf '%s\n' "${LATEST_DOCKER_TAGS}" | tr ',' '\n' | grep -E '^[0-9]+(\.[0-9]+)+$' | head -1)
    LATEST_VERSION="${LATEST_VERSION:-unknown}"
fi

# ---------------------------------------------------------------------------
# 2) SUPPORTED_TAGS_RELEASE -- one bullet per release-targets.yml row. Each
#    lists all docker_tags as inline code spans + a Dockerfile and
#    Instructions link to the row's branch.
#
#    Ordering (NOT release-targets.yml file order):
#      1. The row carrying `latest`     -- current production at top
#      2. Older production rows         -- desc by version (8.0.0, 7.0.4, ...)
#      3. The row carrying `next`       -- upcoming-stable
#      4. The row carrying `dev`        -- active development at bottom
#    `next` and `dev` may be carried by the same row (e.g., when master is the
#    only active-development row pre-cutover) or by separate rows (e.g., when
#    a `rel-*` branch is being readied for the next stable while master is
#    already developing the version after that). The bucketing handles both:
#    a row with both `dev` AND `next` goes into the `dev` slot (the more-
#    bleeding-edge label wins) so it renders as one combined bullet at the
#    bottom; a row with only `next` gets the `next` slot.
#    Bucketed first, sorted within the "older productions" bucket via sort -V -r
#    on the first version-number tag of each row. Matches the convention the
#    devops Twig template hand-coded -- current → older → next → dev.
# ---------------------------------------------------------------------------
RELEASE_BULLETS_FILE="${TMPDIR}/release_bullets"
: > "${RELEASE_BULLETS_FILE}"

# Live row indices = rows that consumers should process. Skip rows flagged
# `unreleased: true` (placeholder entries for the multi-row-per-branch dev
# pattern, see .github/release-targets.yml header). Preserves the original
# YAML index for each kept row so downstream yq lookups by index still work.
mapfile -t LIVE_INDICES < <(yq -r 'to_entries | .[] | select(.value.unreleased != true) | .key' "${RELEASE_TARGETS}")

# Bucket rows by their floating-tag role. A row carrying `latest` is the
# current-production row (singleton by validator). A row carrying `dev` is
# the active-development row (also singleton in practice). A row carrying
# `next` (but not `dev`) is the upcoming-stable row. A row carrying both
# `next` AND `dev` lands in the `dev` slot so it renders as a single
# combined bullet (legacy master-row pattern). Everything else is an older
# production line to be sorted descending by version.
LATEST_IDX=""
DEV_IDX=""
NEXT_IDX=""
OLDER_INDICES=()
for i in "${LIVE_INDICES[@]}"; do
    ROW_TAGS=$(yq -r ".[${i}].docker_tags" "${RELEASE_TARGETS}")
    HAS_LATEST=0; HAS_DEV=0; HAS_NEXT=0
    printf '%s\n' "${ROW_TAGS}" | tr ',' '\n' | grep -qx 'latest' && HAS_LATEST=1
    printf '%s\n' "${ROW_TAGS}" | tr ',' '\n' | grep -qx 'dev'    && HAS_DEV=1
    printf '%s\n' "${ROW_TAGS}" | tr ',' '\n' | grep -qx 'next'   && HAS_NEXT=1
    if (( HAS_LATEST )); then
        LATEST_IDX="${i}"
    elif (( HAS_DEV )); then
        # A row with both `dev` and `next` falls here too; the row's
        # combined-bullet rendering captures both floating tags.
        DEV_IDX="${i}"
    elif (( HAS_NEXT )); then
        NEXT_IDX="${i}"
    else
        OLDER_INDICES+=("${i}")
    fi
done

# Sort the OLDER bucket by descending version on the first version-number
# tag of each row. `sort -V -r` handles 8.1.0 > 8.0.0 > 7.0.4 correctly.
SORTED_OLDER=()
if (( ${#OLDER_INDICES[@]} > 0 )); then
    OLDER_KEYED="${TMPDIR}/older_keyed"
    : > "${OLDER_KEYED}"
    for IDX in "${OLDER_INDICES[@]}"; do
        ROW_TAGS=$(yq -r ".[${IDX}].docker_tags" "${RELEASE_TARGETS}")
        VER=$(printf '%s\n' "${ROW_TAGS}" | tr ',' '\n' | grep -E '^[0-9]+(\.[0-9]+)+$' | head -1)
        printf '%s\t%s\n' "${VER:-0}" "${IDX}" >> "${OLDER_KEYED}"
    done
    mapfile -t SORTED_OLDER < <(sort -V -r -k1,1 "${OLDER_KEYED}" | cut -f2)
fi

# Final emission order: latest → older (desc) → next → dev.
ORDERED_INDICES=()
[[ -n "${LATEST_IDX}" ]] && ORDERED_INDICES+=("${LATEST_IDX}")
ORDERED_INDICES+=("${SORTED_OLDER[@]}")
[[ -n "${NEXT_IDX}" ]] && ORDERED_INDICES+=("${NEXT_IDX}")
[[ -n "${DEV_IDX}" ]] && ORDERED_INDICES+=("${DEV_IDX}")

for IDX in "${ORDERED_INDICES[@]}"; do
    BRANCH=$(yq -r ".[${IDX}].branch" "${RELEASE_TARGETS}")
    TAGS=$(yq -r ".[${IDX}].docker_tags" "${RELEASE_TARGETS}")
    # Format each comma-separated tag as `tag`, joined by " ". The backticks
    # inside the sed expression are LITERAL markdown code-span delimiters; the
    # single quotes prevent shell interpretation, not expansion.
    # shellcheck disable=SC2016
    TAGS_FORMATTED=$(printf '%s\n' "${TAGS}" | tr ',' '\n' | sed 's/^/`/; s/$/`/' | paste -sd ' ' -)
    LINK_BASE="https://github.com/openemr/openemr/blob/${BRANCH}/docker/release"
    printf '* %s [Dockerfile](%s/Dockerfile) | [Instructions](%s/README.md)\n' \
        "${TAGS_FORMATTED}" "${LINK_BASE}" "${LINK_BASE}" >> "${RELEASE_BULLETS_FILE}"
done

# ---------------------------------------------------------------------------
# 3) SUPPORTED_TAGS_FLEX -- iterate the discovered flex caller workflows. For
#    each, read alpine_version, php_versions (JSON array), php_default,
#    is_default_flex from .jobs.build.with. Emit one bullet per PHP version
#    (in REVERSE list order so newest PHP appears first, matching the old
#    devops template's ordering).
#
#    IMPORTANT -- mirror of docker-build-flex-core.yml.
#    The tag list per bullet replicates that workflow's `Build tags` step:
#      base:          flex-{alpine}-php-{php}
#      + default php: flex-{alpine}
#      + default flex + default php: flex (the bare tag)
#    If flex-core's tag rules ever change (a new floating alias, a different
#    conditional, etc.), THIS block needs to follow + the Tier 2 golden
#    fixture at docker/dockerhub/tests/golden.md needs to be regenerated via
#    docker/dockerhub/tests/golden-regenerate.sh. Tier 1 catches bullet-count
#    drift; Tier 2 catches changes to this block; neither catches flex-core
#    diverging from this block silently. flex-core's Build tags step has a
#    matching pointer back here.
#
#    All bullets link to docker/flex/{Dockerfile,README.md} on master since
#    flex lives only on master.
# ---------------------------------------------------------------------------
FLEX_BULLETS_FILE="${TMPDIR}/flex_bullets"
: > "${FLEX_BULLETS_FILE}"

FLEX_LINK_BASE="https://github.com/openemr/openemr/blob/master/docker/flex"

# Reorder discovered flex callers before emission. Convention:
#   1. Non-edge alpine versions, descending  (3.23, 3.22, ...)
#   2. `edge` always last
# Matches the existing devops-template convention. Sort key is each caller's
# alpine_version value; `sort -V -r` puts 3.23 above 3.22. Edge is bucketed
# separately because version-sort against the literal string "edge" doesn't
# produce a meaningful order.
NON_EDGE_KEYED="${TMPDIR}/non_edge_callers_keyed"
: > "${NON_EDGE_KEYED}"
EDGE_CALLERS=()
for CALLER_FILE in "${FLEX_CALLERS[@]}"; do
    CALLER_ALPINE=$(yq -r '.jobs.build.with.alpine_version' "${CALLER_FILE}")
    if [[ "${CALLER_ALPINE}" == "edge" ]]; then
        EDGE_CALLERS+=("${CALLER_FILE}")
    else
        printf '%s\t%s\n' "${CALLER_ALPINE}" "${CALLER_FILE}" >> "${NON_EDGE_KEYED}"
    fi
done
mapfile -t SORTED_NON_EDGE < <(sort -V -r -k1,1 "${NON_EDGE_KEYED}" | cut -f2)
ORDERED_FLEX_CALLERS=("${SORTED_NON_EDGE[@]}" "${EDGE_CALLERS[@]}")

for CALLER_FILE in "${ORDERED_FLEX_CALLERS[@]}"; do
    ALPINE=$(yq -r '.jobs.build.with.alpine_version' "${CALLER_FILE}")
    PHP_DEFAULT=$(yq -r '.jobs.build.with.php_default' "${CALLER_FILE}")
    IS_DEFAULT_FLEX=$(yq -r '.jobs.build.with.is_default_flex' "${CALLER_FILE}")
    PHP_VERSIONS_JSON=$(yq -r '.jobs.build.with.php_versions' "${CALLER_FILE}")

    # php_versions is a JSON array stored as a string; parse + reverse so the
    # newest PHP renders first.
    mapfile -t PHP_VERSIONS < <(printf '%s\n' "${PHP_VERSIONS_JSON}" | jq -r '.[]' | tac)

    for PHP in "${PHP_VERSIONS[@]}"; do
        TAG_SET="\`flex-${ALPINE}-php-${PHP}\`"
        if [[ "${PHP}" == "${PHP_DEFAULT}" ]]; then
            TAG_SET="${TAG_SET} \`flex-${ALPINE}\`"
            if [[ "${IS_DEFAULT_FLEX}" == "true" ]]; then
                TAG_SET="${TAG_SET} \`flex\`"
            fi
        fi
        printf '* %s [Dockerfile](%s/Dockerfile) | [Instructions](%s/README.md)\n' \
            "${TAG_SET}" "${FLEX_LINK_BASE}" "${FLEX_LINK_BASE}" >> "${FLEX_BULLETS_FILE}"
    done
done

# ---------------------------------------------------------------------------
# 4) DATED_EXAMPLE -- "{latest}-{today UTC}" matches what the orchestrator's
#    nightly publish actually does (see docker-build-release.yml's
#    dated-sibling rule). RENDER_DATE_OVERRIDE lets the golden test pin a
#    fixed date so the golden file stays stable across days.
# ---------------------------------------------------------------------------
TODAY="${RENDER_DATE_OVERRIDE:-$(date -u +%Y-%m-%d)}"
DATED_EXAMPLE="${LATEST_VERSION}-${TODAY}"

# ---------------------------------------------------------------------------
# 5) Substitute placeholders. sed's `r FILE` reads file content verbatim,
#    avoiding all the escaping required for cramming bullet content through a
#    shell variable. Each placeholder must live on its own line in the
#    template (that line gets deleted; the file content is inserted before
#    the next line).
# ---------------------------------------------------------------------------
sed \
    -e "s|__CURRENT_LATEST_VERSION__|${LATEST_VERSION}|g" \
    -e "s|__DATED_EXAMPLE__|${DATED_EXAMPLE}|g" \
    -e "/__SUPPORTED_TAGS_RELEASE__/{r ${RELEASE_BULLETS_FILE}" -e "d" -e "}" \
    -e "/__SUPPORTED_TAGS_FLEX__/{r ${FLEX_BULLETS_FILE}" -e "d" -e "}" \
    "${TEMPLATE}" > "${OUT}"
