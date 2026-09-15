#!/usr/bin/env bash
#
# Pick the recovery-path smoketest target from .github/release-targets.yml.
#
# The row whose docker_tags contains "latest" identifies the currently-
# shipped release by definition (docker-release-orchestrator.yml uses
# this same file to decide which image to tag as "latest", so if a row
# is marked latest it IS the current shipped release). Read `branch`
# for the rel branch and `openemr_version_ref` for the exact git tag.
#
# Emits to stdout on success:
#   <branch> <version_ref>
#
# e.g.:
#   rel-840 v8_4_0
#
# The caller (.github/workflows/recovery-path-smoketest.yml) then
# verifies the tag + GitHub Release actually exist on origin (safety
# gates) before dispatching any real work.
#
# Env vars:
#   RELEASE_TARGETS_FILE  (default: .github/release-targets.yml)
#
# Exit codes:
#   0  success (branch + version_ref printed on stdout)
#   1  release-targets file missing / no latest row / malformed
#      version_ref
#
# Unit-tested by tests/bats/ci-scripts/pick-smoketest-target/.

set -euo pipefail

RELEASE_TARGETS_FILE="${RELEASE_TARGETS_FILE:-.github/release-targets.yml}"

if [[ ! -f "${RELEASE_TARGETS_FILE}" ]]; then
    echo "::error::release-targets file not found: ${RELEASE_TARGETS_FILE}" >&2
    exit 1
fi

# Awk state machine: track current block's branch + docker_tags +
# version_ref; on a blank line (end of block), emit if docker_tags
# contains "latest".
#
# The `found` guard prevents double-print when `exit` triggers the
# END block.
result=$(awk '
    /^- branch:/ {
        branch=$3
        docker_tags=""
        version_ref=""
        next
    }
    /^  docker_tags:/ { docker_tags=$2; next }
    /^  openemr_version_ref:/ { version_ref=$2; next }
    /^$/ {
        if (!found && docker_tags ~ /(^|,)latest($|,)/) {
            print branch, version_ref
            found=1
            exit
        }
        branch=""; docker_tags=""; version_ref=""
    }
    END {
        # Handle file that does not end with a blank line.
        if (!found && docker_tags ~ /(^|,)latest($|,)/) {
            print branch, version_ref
        }
    }
' "${RELEASE_TARGETS_FILE}")

if [[ -z "${result}" ]]; then
    echo "::error::No row with docker_tags containing 'latest' in ${RELEASE_TARGETS_FILE} -- has the file schema changed, or has no row been marked latest?" >&2
    exit 1
fi

branch=$(echo "${result}" | awk '{print $1}')
version_ref=$(echo "${result}" | awk '{print $2}')

if [[ -z "${branch}" || -z "${version_ref}" ]]; then
    echo "::error::Latest-marked row is missing branch or openemr_version_ref (branch='${branch}' version_ref='${version_ref}')" >&2
    exit 1
fi

# openemr_version_ref is expected to be a git tag of shape
# v<major>_<minor>_<patch>. Validate before emitting so the caller
# can trust the shape when parsing version components.
if [[ ! "${version_ref}" =~ ^v[0-9]+_[0-9]+_[0-9]+$ ]]; then
    echo "::error::openemr_version_ref '${version_ref}' does not match expected v<major>_<minor>_<patch> shape" >&2
    exit 1
fi

echo "${branch} ${version_ref}"
