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
#   - emit_expected_version + read_tree_version (openemr/openemr#13753):
#     build_locally=true reads version.php; build_locally=false mirrors
#     to_version; missing/malformed version.php fails loud
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
    # build_locally=true: expected_version reads version.php (seeded 8.4.99).
    [[ "${emitted}" == *"expected_version=8.4.99"* ]]
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
    # build_locally=true: expected_version reads version.php (seeded 8.4.99).
    # Independent of the PR-title-parsed to_version.
    [[ "${emitted}" == *"expected_version=8.4.99"* ]]
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
    [[ "${emitted}" == *"expected_version=8.4.99"* ]]
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
    [[ "${emitted}" == *"expected_version=8.4.99"* ]]
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
    [[ "${emitted}" == *"expected_version=8.4.99"* ]]
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
    # build_locally=false: expected_version mirrors to_version
    # (label=actual on shipped-tarball path).
    [[ "${emitted}" == *"expected_version=8.2.5"* ]]
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
    [[ "${emitted}" == *"expected_version=8.2.0"* ]]
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
    [[ "${emitted}" == *"expected_version=8.2.0"* ]]
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
    [[ "${emitted}" == *"expected_version=8.4.99"* ]]
}

@test "git diff failure surfaces loudly with ::error:: (not silently masked by grep)" {
    # Regression guard: under set -euo pipefail, a plain `git diff |
    # grep -qE` pipeline masks a git-side failure as grep's exit-1-
    # for-no-match (rightmost non-zero wins under pipefail), silently
    # reporting build_locally=false. The fix captures git diff
    # separately + checks exit before grepping. This test simulates
    # git diff exiting non-zero (unresolvable range after force-push
    # + reflog GC, etc.) and asserts we exit 1 with a clear
    # ::error:: line, NOT a quiet build_locally=false.
    export EVENT_NAME="push"
    export PUSH_REF="refs/heads/some-branch"
    export PUSH_BEFORE_SHA="deadbeef"
    export PUSH_HEAD_SHA="cafef00d"
    export MOCK_GIT_DIFF_EXIT="128"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::git diff failed for range deadbeef..cafef00d"* ]]
    # Must NOT have silently emitted build_locally=false.
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" != *"build_locally=false"* ]]
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
    [[ "${emitted}" == *"expected_version=8.2.0"* ]]
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
    [[ "${emitted}" == *"expected_version=8.4.99"* ]]
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
    [[ "${emitted}" == *"expected_version=8.4.99"* ]]
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
    # build_locally=true: expected_version tracks the checkout's
    # version.php (seeded 8.4.99), independent of the label choice.
    [[ "${emitted}" == *"expected_version=8.4.99"* ]]
}

# --- emit_expected_version + read_tree_version (openemr/openemr#13753) ---
#
# The build_locally acceptance path historically used `TO_VERSION` as
# the expected value for the post-install / post-upgrade version-display
# and version-api acceptance groups. But `TO_VERSION` on that path is a
# cosmetic 99.99.99 label — PackageAssembler does not bake it into the
# packaged codebase, so the DB `version` table (populated by
# sql_upgrade.php from what's actually in version.php) can never equal
# the label. Assertions therefore couldn't pass. `expected_version` is
# read from the checkout's version.php on build_locally=true, so the
# assertions have a chance of matching.

@test "read_tree_version reads seeded version.php (build_locally=true, dispatch)" {
    # Override the setup default (8.4.99) to prove the value flows
    # from the file, not a constant baked into the script.
    seed_version_php 9 1 2
    export EVENT_NAME="workflow_dispatch"
    export DISPATCH_BUILD_LOCALLY="true"
    export DISPATCH_TO_VERSION=""
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"to_version=99.99.99"* ]]
    [[ "${emitted}" == *"expected_version=9.1.2"* ]]
    [[ "${output}" == *"resolved expected_version=9.1.2"* ]]
}

@test "read_tree_version: missing version.php -> exit 1 (build_locally path)" {
    # Simulate a checkout where version.php is somehow absent — the
    # script must fail loud rather than emit an empty or bogus
    # expected_version.
    rm -f version.php
    export EVENT_NAME="workflow_dispatch"
    export DISPATCH_BUILD_LOCALLY="true"
    export DISPATCH_TO_VERSION=""
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::read_tree_version: cannot read version.php"* ]]
    # Must NOT have emitted expected_version.
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" != *"expected_version="* ]]
}

@test "read_tree_version: missing \$v_patch line -> exit 1 (build_locally path)" {
    # Malformed version.php: has $v_major and $v_minor but not $v_patch.
    # Must fail loud, not emit a partial value.
    cat > version.php <<'PHP'
<?php
$v_major = '8';
$v_minor = '4';
PHP
    export EVENT_NAME="workflow_dispatch"
    export DISPATCH_BUILD_LOCALLY="true"
    export DISPATCH_TO_VERSION=""
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::read_tree_version: failed to parse"* ]]
    [[ "${output}" == *"patch=''"* ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" != *"expected_version="* ]]
}

@test "read_tree_version NOT called on build_locally=false (missing version.php ok)" {
    # Build_locally=false path must not require version.php — the
    # expected_version mirrors to_version, which is either the
    # shipped-version default or a dispatch input. Removing
    # version.php then running a build_locally=false path must not
    # trip read_tree_version.
    rm -f version.php
    export EVENT_NAME="schedule"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"build_locally=false"* ]]
    [[ "${emitted}" == *"to_version=8.2.0"* ]]
    [[ "${emitted}" == *"expected_version=8.2.0"* ]]
}

# --- from_version derivation from sql/*-to-*_upgrade.sql (openemr/openemr#13573) ---

@test "empty DISPATCH_FROM_VERSION -> derives from checkout's sql/*-to-*_upgrade.sql (rel-820 shape)" {
    # rel-820 shape: upgrade files stop at 8_1_1-to-8_2_0, so the
    # max from-version is 8.1.1 -- guaranteed to be in the branch's
    # sql_upgrade.php dropdown.
    seed_sql_upgrade_fixtures \
        7_0_4-to-8_0_0 \
        8_0_0-to-8_1_0 \
        8_1_0-to-8_1_1 \
        8_1_1-to-8_2_0
    export EVENT_NAME="schedule"
    export DISPATCH_FROM_VERSION=""
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"from_version=8.1.1"* ]]
    [[ "${output}" == *"resolved from_version=8.1.1"* ]]
}

@test "empty DISPATCH_FROM_VERSION -> derives from checkout's sql/*-to-*_upgrade.sql (rel-830 shape)" {
    # rel-830 shape: upgrade files include 8_2_0-to-8_3_0, so the
    # max from-version is 8.2.0.
    seed_sql_upgrade_fixtures \
        8_1_0-to-8_1_1 \
        8_1_1-to-8_2_0 \
        8_2_0-to-8_3_0
    export EVENT_NAME="schedule"
    export DISPATCH_FROM_VERSION=""
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"from_version=8.2.0"* ]]
}

@test "empty DISPATCH_FROM_VERSION -> derives from checkout's sql/*-to-*_upgrade.sql (master shape)" {
    # Master (post-8.3.0 cut) shape: upgrade files include
    # 8_3_0-to-8_4_0 (the empty template for the next dev cycle),
    # so the max from-version is 8.3.0.
    seed_sql_upgrade_fixtures \
        8_1_1-to-8_2_0 \
        8_2_0-to-8_3_0 \
        8_3_0-to-8_4_0
    export EVENT_NAME="schedule"
    export DISPATCH_FROM_VERSION=""
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"from_version=8.3.0"* ]]
}

@test "explicit DISPATCH_FROM_VERSION overrides derivation" {
    # Operator explicitly passes a version; derivation skipped even
    # when sql/*.sql files exist and would derive something different.
    seed_sql_upgrade_fixtures 8_1_0-to-8_1_1 8_1_1-to-8_2_0
    export EVENT_NAME="workflow_dispatch"
    export DISPATCH_BUILD_LOCALLY="false"
    export DISPATCH_TO_VERSION="8.2.0"
    export DISPATCH_FROM_VERSION="7.0.4"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"from_version=7.0.4"* ]]
    [[ "${emitted}" != *"from_version=8.1.1"* ]]
}

@test "empty DISPATCH_FROM_VERSION + no sql/ dir -> exit 1 with fail-loud error" {
    # No sql/*.sql files means the checkout is either a branch older
    # than the upgrade-file convention or genuinely broken. Fail
    # loudly rather than emit an empty from_version that would
    # bypass the wizard-step assertion downstream.
    export EVENT_NAME="schedule"
    export DISPATCH_FROM_VERSION=""
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::derive_from_version: no sql/*-to-*_upgrade.sql files found in checkout"* ]]
}

@test "malformed derivation input filtered out at enumeration" {
    # Fixture with a non-X.Y.Z from-version (impossible in practice
    # but guards against a hypothetical filename-format drift). The
    # enumeration's X.Y.Z shape grep filters it out, leaving zero
    # candidates → exits 1 with the "no well-formed files" error.
    mkdir -p sql
    touch sql/8_1-to-8_2_0_upgrade.sql
    export EVENT_NAME="schedule"
    export DISPATCH_FROM_VERSION=""
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::derive_from_version: no well-formed sql/*-to-*_upgrade.sql files found in checkout"* ]]
}

# --- manifest-membership filter (openemr/openemr#13573 followup) ---

@test "manifest filter excludes unshipped max FROM (patch-prep scaffold scenario)" {
    # Simulates the concrete failure the manifest filter fixes:
    # a patch-prep run has scaffolded sql/8_3_1-to-8_4_0_upgrade.sql
    # on master BEFORE 8.3.1 has shipped. sql-only derivation would
    # return 8.3.1 (max FROM), and boot-package.sh would 404 trying
    # to download openemr-8.3.1.tar.gz from GitHub Releases.
    #
    # With the manifest filter, 8.3.1 is excluded (not in the
    # mocked shipped set), and derivation falls back to the next-
    # highest FROM candidate that IS shipped (8.3.0).
    seed_sql_upgrade_fixtures \
        8_2_0-to-8_3_0 \
        8_3_0-to-8_4_0 \
        8_3_1-to-8_4_0
    export EVENT_NAME="schedule"
    export DISPATCH_FROM_VERSION=""
    # Manifest reflects: 8.3.1 not yet shipped, everything else is.
    export MOCK_SHIPPED_VERSIONS=$'8.2.0\n8.3.0'
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" == *"from_version=8.3.0"* ]]
    [[ "${emitted}" != *"from_version=8.3.1"* ]]
}

@test "all candidates unshipped -> exit 1 with actionable no-match error" {
    # Pathological: every sql/*.sql candidate is a future/unshipped
    # version. Fail loudly with a message listing both sets + the
    # manifest URL so operators can debug without reading source.
    seed_sql_upgrade_fixtures \
        9_0_0-to-9_1_0 \
        9_1_0-to-9_2_0
    export EVENT_NAME="schedule"
    export DISPATCH_FROM_VERSION=""
    export MOCK_SHIPPED_VERSIONS=$'8.2.0\n8.3.0'
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::derive_from_version: no sql-derived from-version candidate matches the shipped-versions manifest"* ]]
    [[ "${output}" == *"Candidates from sql/: 9.0.0 9.1.0"* ]]
    [[ "${output}" == *"Shipped per manifest: 8.2.0 8.3.0"* ]]
    [[ "${output}" == *"raw.githubusercontent.com/openemr/website-openemr/master/data/releases.json"* ]]
}

@test "manifest fetch failure surfaces error (does not silently fall back)" {
    # If the manifest fetch fails (network blip, GitHub 5xx, DNS
    # failure), the derivation must exit with the fetch-error line
    # rather than silently reverting to sql-only derivation — the
    # whole point of the filter is to guarantee shipped-status.
    seed_sql_upgrade_fixtures 8_2_0-to-8_3_0
    export EVENT_NAME="schedule"
    export DISPATCH_FROM_VERSION=""
    export MOCK_SHIPPED_VERSIONS="__FAIL__"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::fetch_shipped_versions: (mocked) simulated fetch failure"* ]]
    # Must NOT have emitted a from_version.
    local emitted
    emitted="$(read_output)"
    [[ "${emitted}" != *"from_version="* ]]
}

@test "malformed DISPATCH_FROM_VERSION rejected by shape validator" {
    export EVENT_NAME="workflow_dispatch"
    export DISPATCH_BUILD_LOCALLY="false"
    export DISPATCH_TO_VERSION="8.2.0"
    export DISPATCH_FROM_VERSION="8.2"
    run bash "${DETECT_ACCEPTANCE_MODE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::resolved from_version '8.2' does not match required X.Y.Z"* ]]
}
