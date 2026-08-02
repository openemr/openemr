#!/usr/bin/env bash
#
# Expand a comma-separated list of docker tag labels into a newline-
# separated list of full `openemr/openemr:<tag>` refs, adding a
# `<tag>-<BUILD_DATE>` dated sibling for version-number tags.
#
# Rule (unchanged from the pre-extraction inline sites):
#   * version-number tags (digits and dots only, e.g. "8.1.0") get a
#     dated sibling pushed alongside: "8.1.0" -> "8.1.0" + "8.1.0-<date>"
#   * floating tags (dev, next, latest, manual-test, anything not
#     matching the version-number regex) get no dated variant.
#
# Whitespace inside a tag entry is stripped ("  8.2.0 " -> "8.2.0").
# Empty entries (comma-only input, doubled commas, trailing comma) are
# skipped silently.
#
# Called from two workflows:
#   1. .github/workflows/docker-build-release.yml (primary release path)
#   2. .github/workflows/docker-acceptance-only.yml (Phase 10c recovery)
#
# Both previously carried the same expansion loop inline. Extracted here
# so the version-number regex and the dated-variant rule live in one
# place -- a future change (e.g. a new floating-tag family, a different
# date format) lands once instead of two.
#
# Inputs (env, all required)
#   INPUT_TAGS   comma-separated list of tag labels (no `openemr/openemr:`
#                prefix -- the script adds it). Whitespace tolerated.
#                Empty entries skipped.
#                Example: "8.2.0,latest" or "next" or "  8.1.0 , latest "
#   BUILD_DATE   YYYY-MM-DD used as the dated-variant suffix. In the
#                primary path this is `date +%Y-%m-%d` at build time;
#                in the recovery path it's the candidate image's
#                org.opencontainers.image.created OCI label so the
#                dated-variant tags match what the source build would
#                have produced.
#
# Stdout
#   Newline-separated `openemr/openemr:<tag>` refs, one per line, in
#   input order (each version-number tag immediately followed by its
#   dated sibling). Callers capture this into their $GITHUB_OUTPUT via
#   a heredoc form.
#
# Exit codes
#   0  always (empty input produces empty output; not an error).
#   Non-zero only on `set -euo pipefail` breakage (missing env var,
#   command failure) -- the two required env vars are checked below.

set -euo pipefail

: "${INPUT_TAGS:?INPUT_TAGS must be set (comma-separated tag labels)}"
: "${BUILD_DATE:?BUILD_DATE must be set (YYYY-MM-DD)}"

IFS=',' read -ra TAGS <<< "${INPUT_TAGS}"
for t in "${TAGS[@]}"; do
    t="${t// /}"   # strip whitespace
    [[ -z "${t}" ]] && continue
    echo "openemr/openemr:${t}"
    # Rule: version-number tags (digits and dots only) also get a dated sibling.
    # "8.1.0" -> push "8.1.0" + "8.1.0-2026-06-13"
    # "next" / "dev" / "latest" / "manual-test" -> no dated variant.
    if [[ "${t}" =~ ^[0-9]+(\.[0-9]+)+$ ]]; then
        echo "openemr/openemr:${t}-${BUILD_DATE}"
    fi
done
