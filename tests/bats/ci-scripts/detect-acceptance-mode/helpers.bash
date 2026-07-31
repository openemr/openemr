# Helpers for BATS tests of .github/scripts/detect-acceptance-mode.sh.
#
# Loaded by detect.bats via `load 'helpers'`. Installs a `git` mock on
# PATH (see git-mock.sh for the contract) so the script under test
# never touches a real git repo. Also creates a fresh $GITHUB_OUTPUT
# tempfile per test so assertions can read what the script wrote.
#
# Pattern follows tests/bats/ci-scripts/verify-oci-labels/helpers.bash --
# standalone bash, no bats-support / bats-assert; plain [[ ]] asserts.

# Absolute path to the script under test, captured AT LOAD TIME (before
# any @test does `cd` into its temp working dir).
__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
DETECT_ACCEPTANCE_MODE_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/detect-acceptance-mode.sh"

# Fresh working dir + git mock + GITHUB_OUTPUT tempfile. Sets the
# following vars in the calling shell (BATS's setup() runs in the same
# shell as the @test):
#
#   CWD             fresh temp dir; the test cd's into it
#   GITHUB_OUTPUT   exported -- tempfile the script appends to;
#                   tests grep it to assert emitted values
#   PATH            prepended with the git mock dir
#
# All 11 env vars the workflow's `env:` block would set are pre-
# cleared to empty strings so individual tests can override only
# the subset they care about (matches the workflow's behavior: every
# env is always set, may just be empty).
setup_test_dir() {
    # `mktemp -d` (no `-t <template>`) — BusyBox's mktemp in the
    # bats/bats:1.13.0 Alpine image rejects the `-t` form that GNU
    # mktemp accepts.
    CWD=$(mktemp -d)

    export GITHUB_OUTPUT="${CWD}/github_output"
    : > "${GITHUB_OUTPUT}"

    # Install the git mock at the front of PATH.
    local mock_dir="${CWD}/.git-mock"
    mkdir -p "${mock_dir}"
    cp "${__HELPERS_DIR}/git-mock.sh" "${mock_dir}/git"
    chmod +x "${mock_dir}/git"
    export PATH="${mock_dir}:${PATH}"

    cd "${CWD}" || exit 1

    # Force plain output regardless of caller environment. The script's
    # `echo "::error::..."` lines are recognized as annotations by GitHub
    # Actions runners (which set GITHUB_ACTIONS=true globally); the tests
    # assert on the plain-text substring form, which is what the runner
    # collapses those lines to anyway when GITHUB_ACTIONS is unset.
    unset GITHUB_ACTIONS

    # Empty defaults for every env var the workflow's `env:` block sets.
    # The workflow always sets each ${{ ... }} — an unavailable context
    # value renders as empty string, not unset — so the script relies on
    # every var being defined (even if empty).
    export EVENT_NAME=""
    export DISPATCH_BUILD_LOCALLY=""
    export DISPATCH_TO_VERSION=""
    export CALLER_TARBALL_ARTIFACT=""
    export CALLER_ZIP_ARTIFACT=""
    export PR_BASE_SHA=""
    export PR_HEAD_SHA=""
    export PR_HEAD_REF=""
    export PR_TITLE=""
    export PUSH_BEFORE_SHA=""
    export PUSH_HEAD_SHA=""
    export PUSH_REF=""
    export MOCK_GIT_DIFF_OUTPUT=""
}

teardown_test_dir() {
    cd /
    # Restore PATH first so a subsequent shell command uses real binaries.
    export PATH="${PATH#"${CWD}/.git-mock":}"
    rm -rf "${CWD}"
    unset GITHUB_OUTPUT
    unset EVENT_NAME DISPATCH_BUILD_LOCALLY DISPATCH_TO_VERSION
    unset CALLER_TARBALL_ARTIFACT CALLER_ZIP_ARTIFACT
    unset PR_BASE_SHA PR_HEAD_SHA PR_HEAD_REF PR_TITLE
    unset PUSH_BEFORE_SHA PUSH_HEAD_SHA PUSH_REF
    unset MOCK_GIT_DIFF_OUTPUT
}

# Convenience: read the current $GITHUB_OUTPUT file into a single string
# (newline-separated), suitable for `[[ "$(read_output)" == *"..."* ]]`.
read_output() {
    cat "${GITHUB_OUTPUT}"
}
