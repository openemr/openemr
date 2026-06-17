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

ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
TEMPLATE="${ROOT_DIR}/docker/dockerhub/overview.md"
RELEASE_TARGETS="${ROOT_DIR}/.github/release-targets.yml"
WORKFLOWS_DIR="${ROOT_DIR}/.github/workflows"

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
LATEST_DOCKER_TAGS=$(yq -r '.[] | select(.docker_tags | split(",") | map(. == "latest") | any) | .docker_tags' "${RELEASE_TARGETS}" | head -1)
if [[ -z "${LATEST_DOCKER_TAGS}" ]]; then
    echo "warning: no release-targets.yml row carries the 'latest' tag" >&2
    LATEST_VERSION="unknown"
else
    LATEST_VERSION=$(printf '%s\n' "${LATEST_DOCKER_TAGS}" | tr ',' '\n' | grep -E '^[0-9]+(\.[0-9]+)+$' | head -1)
    LATEST_VERSION="${LATEST_VERSION:-unknown}"
fi

# ---------------------------------------------------------------------------
# 2) SUPPORTED_TAGS_RELEASE -- one bullet per release-targets.yml row in file
#    order. Each lists all docker_tags as inline code spans + a Dockerfile and
#    Instructions link to the row's branch.
# ---------------------------------------------------------------------------
RELEASE_BULLETS_FILE="${TMPDIR}/release_bullets"
: > "${RELEASE_BULLETS_FILE}"

ROW_COUNT=$(yq -r '. | length' "${RELEASE_TARGETS}")
for ((i = 0; i < ROW_COUNT; i++)); do
    BRANCH=$(yq -r ".[${i}].branch" "${RELEASE_TARGETS}")
    TAGS=$(yq -r ".[${i}].docker_tags" "${RELEASE_TARGETS}")
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
#    devops template's ordering). The tag list per bullet replicates
#    docker-build-flex-core.yml's Build tags step:
#      base:          flex-{alpine}-php-{php}
#      + default php: flex-{alpine}
#      + default flex + default php: flex (the bare tag)
#    All bullets link to docker/flex/{Dockerfile,README.md} on master since
#    flex lives only on master.
# ---------------------------------------------------------------------------
FLEX_BULLETS_FILE="${TMPDIR}/flex_bullets"
: > "${FLEX_BULLETS_FILE}"

FLEX_LINK_BASE="https://github.com/openemr/openemr/blob/master/docker/flex"

for CALLER_FILE in "${FLEX_CALLERS[@]}"; do
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
#    dated-sibling rule).
# ---------------------------------------------------------------------------
TODAY=$(date -u +%Y-%m-%d)
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
