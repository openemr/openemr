#!/usr/bin/env bash
# shellcheck disable=SC2312
#   Rationale: SC2312 fires on every command substitution and pipe step under
#   `set -euo pipefail`, because shellcheck can't statically prove the return
#   value is checked. `set -e` and `set -o pipefail` already cover those
#   cases for this script's intent -- pure render-or-die, no per-step
#   recovery needed. Suppressing wholesale to keep the script readable.
#
# Render docker/dockerhub-fpm/overview.md into the markdown that ships to
# Docker Hub's repo description for openemr/dev-php-fpm.
#
# Input sources:
#   * .github/workflows/weekly-build-php-fpm-dockers.yml -- one `build_*` job
#                                                           per version-tag
#   * .github/workflows/build-dev-php-fpm-docker.yml     -- the nightly
#                                                           pre-build tag
#   * docker/dockerhub-fpm/overview.md                   -- template
#
# One placeholder:
#   __SUPPORTED_TAGS__ -- bullet list, one bullet per built tag. Order:
#                         version-number tags descending (8.6, 8.5, ...),
#                         then any non-version tags at the bottom (currently
#                         just `pre-build-dev-86`).
#
# Usage:
#   ./docker/dockerhub-fpm/render.sh [output-path]
#
# Defaults the output to stdout. CI passes ${RUNNER_TEMP}/dockerhub-readme.md.

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
TEMPLATE="${ROOT_DIR}/docker/dockerhub-fpm/overview.md"
WEEKLY_WORKFLOW="${ROOT_DIR}/.github/workflows/weekly-build-php-fpm-dockers.yml"
NIGHTLY_WORKFLOW="${ROOT_DIR}/.github/workflows/build-dev-php-fpm-docker.yml"

OUT="${1:-/dev/stdout}"

for f in "${TEMPLATE}" "${WEEKLY_WORKFLOW}" "${NIGHTLY_WORKFLOW}"; do
    [[ -f "${f}" ]] || { echo "missing input: ${f}" >&2; exit 1; }
done

# Extract every `tags: openemr/dev-php-fpm:<TAG>` value from a workflow file.
# The `tags:` line under each docker/build-push-action `with:` block is the
# authoritative record of what gets pushed. Grep is faster and more portable
# than walking the YAML tree with yq flavors that differ across environments.
extract_tags() {
    local wf="$1"
    grep -oE 'openemr/dev-php-fpm:[A-Za-z0-9._-]+' "${wf}" \
        | sed 's|^openemr/dev-php-fpm:||' \
        | grep -v '^$' \
        | sort -u
}

# Version-number tags (e.g., 8.2, 8.6) come from the weekly workflow.
mapfile -t VERSION_TAGS < <(
    extract_tags "${WEEKLY_WORKFLOW}" \
        | grep -E '^[0-9]+\.[0-9]+$' \
        | sort -V -r
)

# Non-version tags (e.g., pre-build-dev-86) come from the nightly workflow.
mapfile -t OTHER_TAGS < <(
    extract_tags "${NIGHTLY_WORKFLOW}" \
        | grep -vE '^[0-9]+\.[0-9]+$' \
        | sort
)

if (( ${#VERSION_TAGS[@]} == 0 )); then
    echo "error: no version-number tags discovered in ${WEEKLY_WORKFLOW}" >&2
    echo "       aborting to preserve the existing Docker Hub description" >&2
    exit 1
fi

# Derive a human description for a non-version tag. Currently understands
# the `pre-build-dev-<XY>` convention (two-digit PHP version suffix, e.g.
# `pre-build-dev-86` -> PHP 8.6). Any tag that doesn't match a known
# convention renders without a description so nothing goes stale silently.
describe_tag() {
    local tag="$1"
    if [[ "${tag}" =~ ^pre-build-dev-([0-9])([0-9])$ ]]; then
        printf '(foundational base for PHP %s.%s development images)' \
            "${BASH_REMATCH[1]}" "${BASH_REMATCH[2]}"
        return
    fi
}

# Build the bullet block.
BULLETS=""
for t in "${VERSION_TAGS[@]}"; do
    BULLETS+="- \`${t}\`"$'\n'
done
for t in "${OTHER_TAGS[@]}"; do
    desc=$(describe_tag "${t}")
    if [[ -n "${desc}" ]]; then
        BULLETS+="- \`${t}\` ${desc}"$'\n'
    else
        BULLETS+="- \`${t}\`"$'\n'
    fi
done
# Strip trailing newline for clean substitution.
BULLETS="${BULLETS%$'\n'}"

# Substitute. Use awk since the bullet block contains newlines that would
# break naive sed. Passing via a file avoids arg-length issues.
BULLETS_FILE=$(mktemp)
# shellcheck disable=SC2064  # Intentional early expansion.
trap "rm -f '${BULLETS_FILE}'" EXIT
printf '%s\n' "${BULLETS}" > "${BULLETS_FILE}"

awk -v marker="__SUPPORTED_TAGS__" -v file="${BULLETS_FILE}" '
    $0 == marker {
        while ((getline line < file) > 0) print line
        close(file)
        next
    }
    { print }
' "${TEMPLATE}" > "${OUT}"
