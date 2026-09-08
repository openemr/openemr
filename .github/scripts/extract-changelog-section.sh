#!/usr/bin/env bash
#
# Extract a single version's section from a Keep-a-Changelog-shaped
# CHANGELOG.md.
#
# Extracted from .github/workflows/release-amendment.yml's "Extract
# amended section from rel-branch CHANGELOG" step (Phase 10e-6). The
# workflow's step still owns the `env:` block that maps inputs.* into
# the env vars this script reads; the script owns the awk transform +
# empty-section guard. The `::error::` line is preserved verbatim from
# the pre-extraction inline block — operators grep on it.
#
# Matches `## [X.Y.Z]` heading (with optional link markup that
# ChangelogGenerator emits — a link-form heading like
# `## [8.2.0](compare-url) - 2026-07-08` still matches because the
# pattern only anchors on the `## [X.Y.Z]` prefix and takes everything
# up to the next `## ` heading). Dots in the version literal are
# escaped so awk doesn't treat them as regex any-char — which also
# means a version with dashes / rc suffixes (e.g. `8.2.0-rc1`) matches
# on the exact literal, no regex-injection surface.
#
# Inputs (env)
#   CHANGELOG_FILE   Path to CHANGELOG.md.
#   VERSION          Release version (e.g. "8.2.0"), matched literally
#                    against the `## [VERSION]` heading.
#
# Outputs
#   stdout           Extracted section content (heading + body up to
#                    the next `## ` heading or EOF).
#
# Exit codes
#   0   Section extracted successfully (non-empty output).
#   1   CHANGELOG_FILE missing, or no matching section found / section
#       empty. Emits an `::error::` annotation on stderr before exit.

set -euo pipefail

: "${CHANGELOG_FILE:?CHANGELOG_FILE must be set}"
: "${VERSION:?VERSION must be set}"

if [[ ! -f "${CHANGELOG_FILE}" ]]; then
    echo "::error::CHANGELOG_FILE '${CHANGELOG_FILE}' does not exist" >&2
    exit 1
fi

# Escape dots in the version literal so awk doesn't treat them as
# regex any-char. See the header comment for the regex-injection
# rationale — this also makes special-char versions like `8.2.0-rc1`
# match on the exact literal.
escaped_version=$(printf '%s' "${VERSION}" | sed 's/\./\\./g')

# Section body goes to a tempfile so we can size-check for the empty-
# section guard before emitting to stdout. awk's `exit` on the next
# `## ` heading matches the pre-extraction inline block's semantics.
section_file=$(mktemp)
trap 'rm -f "${section_file}"' EXIT

awk -v ver="${escaped_version}" '
    BEGIN { in_section = 0 }
    $0 ~ "^## \\[" ver "\\]" { in_section = 1; print; next }
    in_section && /^## / { exit }
    in_section { print }
' "${CHANGELOG_FILE}" > "${section_file}"

if [[ ! -s "${section_file}" ]]; then
    echo "::error::extracted section is empty; ${CHANGELOG_FILE} may not contain a ## [${VERSION}] heading" >&2
    exit 1
fi

cat "${section_file}"
