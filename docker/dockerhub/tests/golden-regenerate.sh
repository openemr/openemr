#!/usr/bin/env bash
# shellcheck disable=SC2312
#   Same rationale as render.sh: under `set -euo pipefail`, command
#   substitution failures propagate; the per-pipeline subprocess-status
#   nag adds noise without catching anything `set -e` doesn't already.
#
# Regenerate docker/dockerhub/tests/golden.md by running render.sh
# against the fixture inputs. Use ONLY when you've intentionally changed
# render.sh, the production template (docker/dockerhub/overview.md), or
# a fixture file under tests/fixtures/.
#
# Workflow:
#   1. Make the intentional change.
#   2. Run docker/dockerhub/tests/golden-test.sh -- expect it to fail.
#   3. Run THIS script.
#   4. Review the resulting `git diff golden.md` -- the diff should
#      match what you expect from the change you made. If something
#      else moves, investigate before committing.
#   5. Commit the golden.md change in the SAME PR as the renderer/
#      template/fixture change.
#
# The fixed-date pin (RENDER_DATE_OVERRIDE) keeps golden.md stable across
# days; the same value is set in golden-test.sh.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
DOCKERHUB_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
RENDER_SCRIPT="${DOCKERHUB_DIR}/render.sh"
FIXTURE_RELEASE_TARGETS="${SCRIPT_DIR}/fixtures/release-targets.yml"
FIXTURE_WORKFLOWS_DIR="${SCRIPT_DIR}/fixtures/workflows"
GOLDEN="${SCRIPT_DIR}/golden.md"
FIXED_DATE="2026-01-01"

RENDER_RELEASE_TARGETS_OVERRIDE="${FIXTURE_RELEASE_TARGETS}" \
  RENDER_WORKFLOWS_DIR_OVERRIDE="${FIXTURE_WORKFLOWS_DIR}" \
  RENDER_DATE_OVERRIDE="${FIXED_DATE}" \
  "${RENDER_SCRIPT}" "${GOLDEN}"

echo "✓ regenerated ${GOLDEN}"
echo
echo "Review with: git diff $(realpath --relative-to="$(git -C "${SCRIPT_DIR}" rev-parse --show-toplevel 2>/dev/null || pwd)" "${GOLDEN}")"
