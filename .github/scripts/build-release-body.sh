#!/usr/bin/env bash
#
# Assemble a GitHub Release body from an extracted CHANGELOG section,
# substituting a short pointer body when the section exceeds GitHub's
# 125,000-character Release-body limit.
#
# Extracted from .github/workflows/release-amendment.yml's "Prepare
# Release body (truncate if over 125K limit)" step (Phase 10e-6). The
# workflow's step still owns the `env:` block that maps inputs.* into
# the env vars this script reads; the script owns the size decision +
# pointer-body composition. The pointer-body markdown is preserved
# verbatim from the pre-extraction inline block — release-body readers
# see the exact same warning wording.
#
# Anchor construction mirrors GitHub's markdown-heading slug
# algorithm on `## [X.Y.Z] - YYYY-MM-DD`: strip brackets + dots from
# the version, join to the ISO date with `---`. Example:
# `[8.2.0] - 2026-07-08` -> `820---2026-07-08`. This is NOT a general
# GitHub-slug implementation — it exploits knowledge of the exact
# heading shape ChangelogGenerator emits. If the heading shape ever
# changes (e.g. drops the brackets, adds trailing content after the
# date), the anchor construction here needs to change in lockstep.
#
# Inputs (env)
#   SECTION_FILE     Path to a file containing the extracted section
#                    (from extract-changelog-section.sh). Alternately,
#                    if SECTION_FILE is empty or unset, section content
#                    is read from stdin.
#   VERSION          Release version (e.g. "8.2.0"), used both for the
#                    anchor slug and the pointer-body prose.
#   DATE             ISO date the release shipped (e.g. "2026-07-08"),
#                    used for the anchor slug.
#   CHANGELOG_URL    Base URL to the live CHANGELOG.md this pointer
#                    body should link to (e.g.
#                    `https://github.com/openemr/openemr/blob/rel-820/CHANGELOG.md`).
#                    Required only when truncation triggers — a short
#                    section that fits in the body doesn't need it.
#   REL_BRANCH       Optional. Only used for the pointer-body prose
#                    (the "on the live `<branch>` branch" line). If
#                    empty, that phrase omits the branch name.
#
# Outputs
#   stdout           Final Release-body markdown (either the full
#                    section unchanged, or the pointer body).
#
# Exit codes
#   0   Body emitted successfully.
#   1   Missing required input (SECTION_FILE not readable when set;
#       neither SECTION_FILE nor stdin readable; VERSION / DATE
#       missing; truncation triggered without CHANGELOG_URL set).

set -euo pipefail

: "${VERSION:?VERSION must be set}"
: "${DATE:?DATE must be set}"

# 124000 not 125000 -- leave a safety margin under the hard cap.
# GitHub rejects Release bodies over 125,000 chars with `body is too
# long`; the 1000-char margin covers post-body edits the API might
# apply (auto-appended metadata, etc.) and future creep in the pointer
# body itself.
LIMIT=124000

# Read the section into a tempfile so we can size-check + re-emit
# regardless of whether it arrived via SECTION_FILE or stdin.
section_file=$(mktemp)
trap 'rm -f "${section_file}"' EXIT

if [[ -n "${SECTION_FILE:-}" ]]; then
    if [[ ! -f "${SECTION_FILE}" ]]; then
        echo "::error::SECTION_FILE '${SECTION_FILE}' does not exist" >&2
        exit 1
    fi
    cp "${SECTION_FILE}" "${section_file}"
else
    # No SECTION_FILE — read from stdin. Fine when the pipeline is
    # `extract-changelog-section.sh | build-release-body.sh`.
    cat > "${section_file}"
fi

size=$(wc -c < "${section_file}")
echo "Extracted section size: ${size} bytes (limit ${LIMIT})" >&2

if [[ "${size}" -le "${LIMIT}" ]]; then
    echo "Section fits in Release body; using full content." >&2
    cat "${section_file}"
    exit 0
fi

echo "Section exceeds Release body limit; substituting pointer body." >&2

if [[ -z "${CHANGELOG_URL:-}" ]]; then
    echo "::error::section exceeds ${LIMIT}-byte Release body limit but CHANGELOG_URL is not set; cannot construct pointer body" >&2
    exit 1
fi

# Anchor: strip dots from version, join to date with `---`. See the
# header comment for the "not a general slug" caveat.
version_slug=$(printf '%s' "${VERSION}" | tr -d '.')
anchor="${version_slug}---${DATE}"
section_url="${CHANGELOG_URL}#${anchor}"
echo "Anchor URL: ${section_url}" >&2

# Pointer-body markdown — preserved verbatim from the pre-extraction
# inline block. The `${REL_BRANCH:-…}` phrasing keeps the prose
# sensible when REL_BRANCH is empty (drops "on the live `<branch>`
# branch" to just "on the live branch").
if [[ -n "${REL_BRANCH:-}" ]]; then
    branch_phrase="on the live \`${REL_BRANCH}\` branch"
    attachment_line="Or download the \`changelog.md\` attachment on this Release for the full amended section (including all Security advisories and the complete PR list)."
else
    branch_phrase="on the live branch"
    attachment_line="Or download the \`changelog.md\` attachment on this Release for the full amended section (including all Security advisories and the complete PR list)."
fi

cat <<EOF
> :warning: **The full changelog for ${VERSION} exceeds GitHub's 125,000-character Release body limit.**
>
> View the complete ${VERSION} section ${branch_phrase}:
> ${section_url}
>
> ${attachment_line}

_This Release body was regenerated by \`.github/workflows/release-amendment.yml\` after post-release GHSA publication. The attachment on this Release carries the full amended content._
EOF
