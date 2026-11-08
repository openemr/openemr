# Helpers for BATS tests of .github/scripts/create-release-tag.sh.
#
# Loaded by create-release-tag.bats via `load 'helpers'`. Installs `git`
# + `gh` mocks on PATH (see git-mock.sh, gh-mock.sh for the contracts)
# so the script under test never talks to a real remote or GitHub API.
#
# Pattern follows tests/bats/ci-scripts/dockerhub-delete-tag/helpers.bash --
# standalone bash, no bats-support / bats-assert; plain [[ ]] asserts.

# Absolute path to the script under test, captured AT LOAD TIME (before
# any @test does `cd` into its temp working dir).
__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
CREATE_RELEASE_TAG_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/create-release-tag.sh"

# Fresh working dir + git/gh mocks. Sets the following vars in the
# calling shell (BATS's setup() runs in the same shell as the @test):
#
#   CWD                          fresh temp dir; the test cd's into it
#   MOCK_CALL_LOG                exported -- both mocks append one line
#                                per invocation so tests can assert on
#                                which subcommands were called (and in
#                                what order).
#   MOCK_LS_REMOTE_EXIT          exit code the git mock returns for
#                                `git ls-remote --tags ...`. Default 2
#                                (tag-doesn't-exist happy path).
#   MOCK_LS_REMOTE_STDOUT        text the git mock emits on stdout for
#                                the ls-remote call (mirrors real git's
#                                output when the tag exists). Default
#                                empty.
#   MOCK_GH_RELEASE_VIEW_EXIT    exit code the gh mock returns for
#                                `gh release view`. Default 1 (release-
#                                doesn't-exist, so the script creates
#                                it).
#   MOCK_GH_RELEASE_CREATE_EXIT  exit code the gh mock returns for
#                                `gh release create`. Default 0.
#   MOCK_GIT_PUSH_EXIT           exit code the git mock returns for
#                                `git push`. Default 0.
#   PATH                         prepended with the mock dir.
setup_test_dir() {
    # `mktemp -d` (no `-t <template>`) — BusyBox's mktemp in the
    # bats/bats:1.13.0 Alpine image rejects the `-t` form that GNU
    # mktemp accepts.
    CWD=$(mktemp -d)

    export MOCK_CALL_LOG="${CWD}/mock-calls.log"
    : > "${MOCK_CALL_LOG}"

    # Install the mocks at the front of PATH.
    local mock_dir="${CWD}/.mocks"
    mkdir -p "${mock_dir}"
    cp "${__HELPERS_DIR}/git-mock.sh" "${mock_dir}/git"
    cp "${__HELPERS_DIR}/gh-mock.sh" "${mock_dir}/gh"
    chmod +x "${mock_dir}/git" "${mock_dir}/gh"
    export PATH="${mock_dir}:${PATH}"

    cd "${CWD}" || exit 1

    # Force plain output regardless of caller environment. The script's
    # `echo "::error::..."` lines are recognized as annotations by
    # GitHub Actions runners; the tests assert on the plain-text
    # substring form.
    unset GITHUB_ACTIONS

    # Sensible defaults for the 4 required env vars. Individual tests
    # override via `export VAR=...` before running the script.
    export RELEASE_TAG="v8_2_0"
    export RELEASE_VERSION="8.2.0"
    export VERSION_BRANCH="rel-820"
    # A real file so the notes-file arg the script passes to gh is a
    # path that would exist on the runner. The gh mock doesn't read it.
    export RELEASE_NOTES_FILE="${CWD}/changelog.md"
    printf 'test changelog body\n' > "${RELEASE_NOTES_FILE}"

    # gh CLI reads GH_TOKEN itself; not consumed by the script, but the
    # workflow-step env: block passes it. Set it so a test that greps
    # the environment sees the same shape.
    export GH_TOKEN="test-token"

    # Default mock behaviors -- happy path (tag doesn't exist, release
    # doesn't exist -> script creates both).
    export MOCK_LS_REMOTE_EXIT="2"
    export MOCK_LS_REMOTE_STDOUT=""
    export MOCK_GH_RELEASE_VIEW_EXIT="1"
    export MOCK_GH_RELEASE_CREATE_EXIT="0"
    export MOCK_GIT_PUSH_EXIT="0"
}

teardown_test_dir() {
    cd /
    # Restore PATH first so a subsequent shell command uses real
    # binaries.
    export PATH="${PATH#"${CWD}/.mocks":}"
    rm -rf "${CWD}"
    unset MOCK_CALL_LOG
    unset MOCK_LS_REMOTE_EXIT MOCK_LS_REMOTE_STDOUT
    unset MOCK_GH_RELEASE_VIEW_EXIT MOCK_GH_RELEASE_CREATE_EXIT
    unset MOCK_GIT_PUSH_EXIT
    unset RELEASE_TAG RELEASE_VERSION VERSION_BRANCH RELEASE_NOTES_FILE
    unset GH_TOKEN
}

# Return the mock call log as a single string for substring asserts.
mock_calls() {
    cat "${MOCK_CALL_LOG}" 2>/dev/null || true
}
