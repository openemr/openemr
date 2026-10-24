#!/usr/bin/env bash
#
# Parse the OpenEMR artifact's self-reported version into a validated
# X.Y.Z string for ACCEPTANCE_EXPECTED_VERSION. Two-tier resolution:
# OCI label first, fallback to a caller-supplied version.php read.
# Extracted from acceptance-docker.yml's inline logic so BATS tests
# can pin parse/validate behavior without needing a live docker
# daemon.
#
# Two-tier rationale:
#
#   * OCI label `org.opencontainers.image.version` is set at docker-
#     BUILD time by --build-arg IMAGE_VERSION baked into the release
#     pipeline. Independent source from both version.php (in the
#     image's code tree) and the DB `version` table (populated by
#     sql_upgrade.php). Using it as expected_version gives a genuine
#     3-source cross-check when version-display + version-api tests
#     run: OCI label vs version.php (via About page render pipe) vs
#     DB (via /api/version). Catches the "mislabeled image" bug
#     class. Applies to every shipped-tag image (`latest`, `next`,
#     `dev`, `8.4.1`, ...).
#
#   * version.php fallback is the safety net for `pr-built`. The PR
#     workflow's build-image job builds without --build-arg
#     IMAGE_VERSION, so the OCI label is empty. In pr-built context
#     the PR IS the version source of truth (may contain version.php
#     changes itself), so cross-signal isn't available anyway --
#     fall back + accept the weakened signal (still tests the render
#     pipeline). Mirrors detect-acceptance-mode.sh's read_tree_version
#     pattern on the tarball build_locally path.
#
# The workflow separately reads the OCI label + optionally invokes
# read-version-php.php against the running container, then passes the
# raw strings to this script via env. This script owns ONLY the
# strip/validate/decide-which-to-use logic.
#
# Inputs (env):
#
#   OCI_VERSION_LABEL     Raw value of
#                         org.opencontainers.image.version OCI label
#                         from `docker inspect --format=...`. May be
#                         empty (pr-built case) or contain a pre-
#                         release suffix (e.g., '8.5.0-dev' for
#                         master builds). This script extracts the
#                         leading X.Y.Z prefix via regex.
#   FALLBACK_VERSION      (optional) X.Y.Z string emitted by
#                         read-version-php.php against the running
#                         container's version.php. Consulted when
#                         OCI_VERSION_LABEL yields no valid prefix.
#                         Callers pass empty when they haven't
#                         attempted the version.php read (e.g., the
#                         script is being reused in a context where
#                         no container is running).
#
# Outputs (to $GITHUB_OUTPUT, or stdout if unset):
#
#   resolved=X.Y.Z
#
# Exit codes:
#
#   0  Emitted a valid X.Y.Z.
#   1  Neither source yielded a valid X.Y.Z. Callers should treat
#      this as a workflow-side bug (both OCI label AND version.php
#      failed to provide a version -- extremely unusual, indicates
#      broken image or broken container).
#
# Unit-tested by tests/bats/ci-scripts/parse-artifact-version/.

set -euo pipefail

emit() {
    if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
        printf '%s\n' "$1" >> "${GITHUB_OUTPUT}"
    else
        printf '%s\n' "$1"
    fi
}

OCI_VERSION_LABEL="${OCI_VERSION_LABEL:-}"
FALLBACK_VERSION="${FALLBACK_VERSION:-}"

RESOLVED=""
if [[ "${OCI_VERSION_LABEL}" =~ ^([0-9]+\.[0-9]+\.[0-9]+) ]]; then
    RESOLVED="${BASH_REMATCH[1]}"
    echo "==> OCI label 'org.opencontainers.image.version' = '${OCI_VERSION_LABEL}' -> resolved X.Y.Z prefix = '${RESOLVED}'"
elif [[ -n "${FALLBACK_VERSION}" ]]; then
    RESOLVED="${FALLBACK_VERSION}"
    echo "==> OCI label empty or malformed ('${OCI_VERSION_LABEL}') -- using version.php fallback = '${RESOLVED}'"
else
    echo "::error::parse-artifact-version.sh: neither OCI label nor version.php fallback yielded a version string. OCI_VERSION_LABEL='${OCI_VERSION_LABEL}' FALLBACK_VERSION='${FALLBACK_VERSION}'" >&2
    exit 1
fi

if [[ ! "${RESOLVED}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "::error::parse-artifact-version.sh: resolved version '${RESOLVED}' does not match X.Y.Z shape after strip -- OCI label '${OCI_VERSION_LABEL}', fallback '${FALLBACK_VERSION}'" >&2
    exit 1
fi

emit "resolved=${RESOLVED}"
