#!/usr/bin/env bash
#
# Tier 2 golden-file test for the Docker Hub readme renderer.
#
# Runs render.sh against the fixture inputs in fixtures/ + the production
# template at docker/dockerhub/overview.md, then diffs the result against
# the checked-in golden.md. Any divergence fails the test.
#
# The point: catch ANY change in renderer output -- bullet ordering, link
# format, whitespace, tag synthesis, prose substitution -- not just the
# structural properties Tier 1 (sanity.sh) covers. Intentional changes
# require explicit re-acknowledgement by regenerating the golden file
# (see golden-regenerate.sh) and committing the diff alongside the
# renderer or template change in the same PR. Reviewers see both diffs
# together and can spot unintentional ripples.
#
# Date handling: the renderer's dated-example uses today's UTC date by
# default, which would make the golden go stale every day. RENDER_DATE_-
# OVERRIDE pins it to a fixed value for the test; same env var is set
# by golden-regenerate.sh so the regen and the test agree.
#
# Usage:
#   ./docker/dockerhub/tests/golden-test.sh
#
# Exits 0 if rendered output matches golden.md; non-zero with a unified
# diff dumped to stdout otherwise.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
DOCKERHUB_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
RENDER_SCRIPT="${DOCKERHUB_DIR}/render.sh"
FIXTURE_RELEASE_TARGETS="${SCRIPT_DIR}/fixtures/release-targets.yml"
FIXTURE_WORKFLOWS_DIR="${SCRIPT_DIR}/fixtures/workflows"
GOLDEN="${SCRIPT_DIR}/golden.md"
FIXED_DATE="2026-01-01"

TMPDIR=$(mktemp -d)
# shellcheck disable=SC2064  # Intentional early expansion -- TMPDIR is final
trap "rm -rf '${TMPDIR}'" EXIT
ACTUAL="${TMPDIR}/actual.md"

RENDER_RELEASE_TARGETS_OVERRIDE="${FIXTURE_RELEASE_TARGETS}" \
  RENDER_WORKFLOWS_DIR_OVERRIDE="${FIXTURE_WORKFLOWS_DIR}" \
  RENDER_DATE_OVERRIDE="${FIXED_DATE}" \
  "${RENDER_SCRIPT}" "${ACTUAL}"

if diff -u "${GOLDEN}" "${ACTUAL}"; then
    echo "✓ rendered output matches golden.md"
    exit 0
else
    echo
    echo "✗ rendered output diverged from golden.md"
    echo
    echo "If this divergence is intentional (you changed render.sh, the"
    echo "production template, or a fixture), regenerate the golden:"
    echo
    echo "    ${SCRIPT_DIR}/golden-regenerate.sh"
    echo
    echo "...then review the resulting golden.md diff before committing"
    echo "it alongside the renderer/template/fixture change."
    exit 1
fi
