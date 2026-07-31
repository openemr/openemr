# BATS tests for .github/scripts/detect-acceptance-mode.sh
#
# Runs the script against a mock `git` CLI (see helpers.bash +
# git-mock.sh) and a per-test $GITHUB_OUTPUT tempfile. Covers the
# seven emit paths + the emit_to_version validator:
#
#   - workflow_call gate happy path
#   - workflow_call gate half-set (both symmetric-input failure modes)
#   - workflow_call gate missing to_version
#   - release-prep branch detection on pull_request AND push events
#     (both with and without a parseable PR title)
#   - workflow_dispatch (build_locally=true and =false paths)
#   - non-push/pull_request event fallback (schedule)
#   - branch-creation event (BASE=000...)
#   - push/pull_request with a tarball-build-surface change
#   - push/pull_request with no relevant change
#   - emit_to_version's X.Y.Z validation
#   - DISPATCH_TO_VERSION overriding the PR-title preferred value
#
# What's NOT covered
#   - actual GitHub Actions expression rendering — the tests set the
#     env vars directly, as the workflow's `env:` block would produce
#   - the exact git CLI wire format ("git diff --name-only <range>") —
#     the mock ignores the range and emits the fixture; if the script
#     starts calling git with additional flags, the mock's coverage
#     will need updating in lockstep

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# --- workflow_call gate ---

@test "workflow_call gate happy path (both artifacts + to_version) -> build_locally=true, to_version=input" {
    export EVENT_NAME="workflow_dispatch"
    export CALLER_TARBALL_ARTIFACT="my-tarball"
    export CALLER_ZIP_ARTIFACT="my-zip"
    export DISPATCH_TO_VERSION="8.2.1"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"workflow_call gate mode (tarball=my-tarball, zip=my-zip): forcing build_locally=true"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=true"* ]]
    [[ "${emitted}" == *"to_version=8.2.1"* ]]
}

@test "workflow_call gate half-set (only tarball) -> exit 1 with both-or-neither error" {
    export EVENT_NAME="workflow_dispatch"
    export CALLER_TARBALL_ARTIFACT="my-tarball"
    export CALLER_ZIP_ARTIFACT=""
    export DISPATCH_TO_VERSION="8.2.1"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::workflow_call requires BOTH caller_tarball_artifact AND caller_zip_artifact to be set (or neither)"* ]]
    [[ "${output}" == *"tarball='my-tarball'"* ]]
    [[ "${output}" == *"zip='<empty>'"* ]]
}

@test "workflow_call gate half-set (only zip) -> exit 1 with both-or-neither error" {
    export EVENT_NAME="workflow_dispatch"
    export CALLER_TARBALL_ARTIFACT=""
    export CALLER_ZIP_ARTIFACT="my-zip"
    export DISPATCH_TO_VERSION="8.2.1"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::workflow_call requires BOTH caller_tarball_artifact AND caller_zip_artifact to be set (or neither)"* ]]
    [[ "${output}" == *"tarball='<empty>'"* ]]
    [[ "${output}" == *"zip='my-zip'"* ]]
}

@test "workflow_call gate with both artifacts but no DISPATCH_TO_VERSION -> exit 1" {
    export EVENT_NAME="workflow_dispatch"
    export CALLER_TARBALL_ARTIFACT="my-tarball"
    export CALLER_ZIP_ARTIFACT="my-zip"
    export DISPATCH_TO_VERSION=""
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::workflow_call with caller_tarball_artifact/caller_zip_artifact requires to_version to be set explicitly"* ]]
}

# --- release-prep detection ---

@test "release-prep branch on pull_request with parseable title -> build_locally=true, to_version=parsed" {
    export EVENT_NAME="pull_request"
    export PR_HEAD_REF="release-prep/rel-820"
    export PR_TITLE="chore(release): prep 8.2.1"
    export PR_BASE_SHA="aaa"
    export PR_HEAD_SHA="bbb"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"release-prep branch detected (release-prep/rel-820): forcing build_locally=true"* ]]
    [[ "${output}" == *"parsed release version from PR title: 8.2.1"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=true"* ]]
    [[ "${emitted}" == *"to_version=8.2.1"* ]]
}

@test "release-prep branch on pull_request without parseable title -> to_version=99.99.99" {
    export EVENT_NAME="pull_request"
    export PR_HEAD_REF="release-prep/rel-820"
    export PR_TITLE="some unrelated title"
    export PR_BASE_SHA="aaa"
    export PR_HEAD_SHA="bbb"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"release-prep branch detected (release-prep/rel-820): forcing build_locally=true"* ]]
    [[ "${output}" != *"parsed release version from PR title"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=true"* ]]
    [[ "${emitted}" == *"to_version=99.99.99"* ]]
}

@test "release-prep branch on push event -> build_locally=true, to_version resolved" {
    export EVENT_NAME="push"
    export PUSH_REF="refs/heads/release-prep/rel-820"
    export PR_TITLE=""
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"release-prep branch detected (release-prep/rel-820): forcing build_locally=true"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=true"* ]]
    [[ "${emitted}" == *"to_version=99.99.99"* ]]
}

# --- workflow_dispatch ---

@test "workflow_dispatch with DISPATCH_BUILD_LOCALLY=true -> build_locally=true, to_version=99.99.99" {
    export EVENT_NAME="workflow_dispatch"
    export DISPATCH_BUILD_LOCALLY="true"
    export DISPATCH_TO_VERSION=""
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"dispatch mode: build_locally=true"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=true"* ]]
    [[ "${emitted}" == *"to_version=99.99.99"* ]]
}

@test "workflow_dispatch with DISPATCH_BUILD_LOCALLY=false + DISPATCH_TO_VERSION=8.2.5 -> honors both" {
    export EVENT_NAME="workflow_dispatch"
    export DISPATCH_BUILD_LOCALLY="false"
    export DISPATCH_TO_VERSION="8.2.5"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"dispatch mode: build_locally=false"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=false"* ]]
    [[ "${emitted}" == *"to_version=8.2.5"* ]]
}

# --- non-push/pull_request fallback ---

@test "schedule event -> build_locally=false, to_version=8.2.0" {
    export EVENT_NAME="schedule"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"schedule event: defaulting build_locally=false"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=false"* ]]
    [[ "${emitted}" == *"to_version=8.2.0"* ]]
}

# --- branch-creation event (BASE=000...) ---

@test "push with BASE=000... (branch creation) -> build_locally=false, to_version=8.2.0" {
    export EVENT_NAME="push"
    export PUSH_REF="refs/heads/new-branch"
    export PUSH_BEFORE_SHA="0000000000000000000000000000000000000000"
    export PUSH_HEAD_SHA="bbb"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"branch-creation event (no base ref): defaulting build_locally=false"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=false"* ]]
    [[ "${emitted}" == *"to_version=8.2.0"* ]]
}

# --- diff-based detection ---

@test "push with tools/release/ change -> build_locally=true, to_version=99.99.99" {
    export EVENT_NAME="push"
    export PUSH_REF="refs/heads/some-branch"
    export PUSH_BEFORE_SHA="aaa"
    export PUSH_HEAD_SHA="bbb"
    export MOCK_GIT_DIFF_OUTPUT=$'tools/release/foo.php\nREADME.md'
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"tarball-build surface changed between aaa and bbb: build_locally=true"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=true"* ]]
    [[ "${emitted}" == *"to_version=99.99.99"* ]]
}

@test "push with README.md only -> build_locally=false, to_version=8.2.0" {
    export EVENT_NAME="push"
    export PUSH_REF="refs/heads/some-branch"
    export PUSH_BEFORE_SHA="aaa"
    export PUSH_HEAD_SHA="bbb"
    export MOCK_GIT_DIFF_OUTPUT="README.md"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"tarball-build surface unchanged between aaa and bbb: build_locally=false"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=false"* ]]
    [[ "${emitted}" == *"to_version=8.2.0"* ]]
}

@test "pull_request with build.xml change -> build_locally=true" {
    export EVENT_NAME="pull_request"
    export PR_BASE_SHA="aaa"
    export PR_HEAD_SHA="bbb"
    export PR_HEAD_REF="feature/xyz"
    export MOCK_GIT_DIFF_OUTPUT="build.xml"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"tarball-build surface changed"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=true"* ]]
}

@test "pull_request with .gitattributes change -> build_locally=true" {
    export EVENT_NAME="pull_request"
    export PR_BASE_SHA="aaa"
    export PR_HEAD_SHA="bbb"
    export PR_HEAD_REF="feature/xyz"
    export MOCK_GIT_DIFF_OUTPUT=".gitattributes"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"tarball-build surface changed"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=true"* ]]
}

# --- emit_to_version validator ---

@test "malformed DISPATCH_TO_VERSION (8.2) -> exit 1 with X.Y.Z error" {
    export EVENT_NAME="workflow_dispatch"
    export DISPATCH_BUILD_LOCALLY="false"
    export DISPATCH_TO_VERSION="8.2"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::resolved to_version '8.2' does not match required X.Y.Z"* ]]
}

# --- DISPATCH_TO_VERSION overrides preferred/default ---

@test "release-prep branch with DISPATCH_TO_VERSION override wins over parseable title" {
    # Both a parseable PR title (preferred=8.2.1) AND a dispatch-provided
    # to_version (8.3.0). Dispatch wins per emit_to_version's precedence:
    # DISPATCH_TO_VERSION checked first, then preferred, then defaults.
    export EVENT_NAME="pull_request"
    export PR_HEAD_REF="release-prep/rel-820"
    export PR_TITLE="chore(release): prep 8.2.1"
    export PR_BASE_SHA="aaa"
    export PR_HEAD_SHA="bbb"
    export DISPATCH_TO_VERSION="8.3.0"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"to_version=8.3.0"* ]]
    [[ "${emitted}" != *"to_version=8.2.1"* ]]
}
