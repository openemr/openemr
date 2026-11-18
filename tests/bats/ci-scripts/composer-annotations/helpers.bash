# Helpers for BATS tests of .github/scripts/composer-annotations.sh.
#
# Loaded by composer-annotations.bats via `load 'helpers'`. Each test writes
# a fixture of the tool's output into a scratch directory and runs the script
# against it.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
COMPOSER_ANNOTATIONS_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/composer-annotations.sh"

setup_scratch() {
    # `mktemp -d` (no `-t <template>`): BusyBox's mktemp rejects the `-t` form.
    SCRATCH=$(mktemp -d)
    # Plain output regardless of caller environment; the annotation and
    # summary forms are asserted explicitly where they matter.
    unset GITHUB_ACTIONS GITHUB_STEP_SUMMARY
}

teardown_scratch() {
    rm -rf "${SCRATCH}"
}
