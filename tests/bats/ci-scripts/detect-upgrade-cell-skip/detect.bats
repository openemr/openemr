# BATS tests for .github/scripts/detect-upgrade-cell-skip.sh.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# ==== Happy paths -- skip=false (cell should run) ====

@test "from=shipped, to=shipped -> skip=false (normal upgrade)" {
    export FROM_REV="v8_4_0" FROM_TAG="8.4.0"
    export TO_REV="v8_4_1"   TO_TAG="latest"
    write_release_targets "8.5.0,dev,next"
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"skip=false"* ]]
    # skip_reason NOT set when skip=false -- downstream `if:` chains
    # only need the skip=false signal.
    [[ "$(cat "${GITHUB_OUTPUT}")" != *"skip_reason"* ]]
}

@test "from=shipped, to=master, master has NO next tag -> skip=false (rel-branch in active dev)" {
    export FROM_REV="v8_4_1" FROM_TAG="latest"
    export TO_REV="master"   TO_TAG="dev"
    write_release_targets "8.5.0,dev"
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"skip=false"* ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" != *"skip_reason"* ]]
    # Reader-log line confirms the branch that fired.
    [[ "${output}" == *"cross-propagated upgrade infra to master"* ]]
}

@test "from=empty (unrecognizable), to=shipped -> skip=false (let cell run to surface actual failure)" {
    # Empty FROM_REV is a LEGITIMATE input (image missing OCI label /
    # docker inspect failed). Script must NOT treat empty as "master"
    # and skip -- it lets the cell run so a real failure surfaces.
    export FROM_REV="" FROM_TAG="mystery-tag"
    export TO_REV="v8_4_1" TO_TAG="latest"
    write_release_targets "8.5.0,dev,next"
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"skip=false"* ]]
}

# ==== Skip criterion 1: from=master ====

@test "from=master -> skip=true regardless of to (no higher version to upgrade to)" {
    export FROM_REV="master" FROM_TAG="dev"
    export TO_REV="v8_4_1"   TO_TAG="latest"
    write_release_targets "8.5.0,dev,next"
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"skip=true"* ]]
    # Reason names FROM_TAG + revision=master.
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"from_tag (dev)"* ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"revision=master"* ]]
}

@test "from=master short-circuits before criterion 2 is evaluated (no release-targets.yml read needed)" {
    # No RELEASE_TARGETS_PATH export + no fixture file -- if the script
    # got to criterion 2 it would fail with the missing-file error.
    export FROM_REV="master" FROM_TAG="dev"
    export TO_REV="master"   TO_TAG="dev"
    unset RELEASE_TARGETS_PATH
    # No release-targets.yml at the default path either.
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"skip=true"* ]]
}

# ==== Skip criterion 2: to=master AND master carries `next` ====

@test "from=shipped, to=master, master carries next -> skip=true (between-cycles state)" {
    export FROM_REV="v8_4_1" FROM_TAG="latest"
    export TO_REV="master"   TO_TAG="next"
    write_release_targets "8.5.0,dev,next"
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"skip=true"* ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"to_tag (next)"* ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"between-cycles"* ]]
}

@test "next tag is word-bounded: master has 'nextish' but NOT 'next' -> skip=false" {
    # grep pattern must not false-match on tags that contain 'next' as
    # a substring (defensive against future tag naming). Current
    # release-targets.yml has no such tags, but the guard should hold.
    export FROM_REV="v8_4_1" FROM_TAG="latest"
    export TO_REV="master"   TO_TAG="dev"
    write_release_targets "8.5.0,dev,nextish"
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"skip=false"* ]]
}

@test "next tag is word-bounded: master has 'not-next' but NOT 'next' -> skip=false" {
    export FROM_REV="v8_4_1" FROM_TAG="latest"
    export TO_REV="master"   TO_TAG="dev"
    write_release_targets "8.5.0,dev,not-next"
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"skip=false"* ]]
}

@test "next tag surrounded by other tags: master='dev,next,latest' -> skip=true" {
    # Common shape once patch-prep-automation cross-syncs -- next sits
    # between other floating tags. Word boundary must match ',next,'.
    export FROM_REV="v8_4_1" FROM_TAG="latest"
    export TO_REV="master"   TO_TAG="next"
    write_release_targets "dev,next,latest"
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"skip=true"* ]]
}

# ==== Bad-shape guards ====

@test "missing FROM_TAG -> exit 1 with usage error" {
    export FROM_REV="v8_4_1"
    export TO_REV="v8_4_1" TO_TAG="latest"
    unset FROM_TAG
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"FROM_TAG and TO_TAG env must both be non-empty"* ]]
}

@test "missing TO_TAG -> exit 1 with usage error" {
    export FROM_REV="v8_4_1" FROM_TAG="latest"
    export TO_REV="v8_4_1"
    unset TO_TAG
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"FROM_TAG and TO_TAG env must both be non-empty"* ]]
}

@test "FROM_REV completely unset (not just empty) -> exit 1 (caller bug)" {
    # Distinguish unset-var from empty-string: empty is legitimate
    # (docker inspect returned nothing); unset means caller forgot to
    # plumb the env at all.
    unset FROM_REV
    export TO_REV="v8_4_1" FROM_TAG="latest" TO_TAG="latest"
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"FROM_REV and TO_REV env must both be set"* ]]
}

@test "TO_REV completely unset -> exit 1" {
    export FROM_REV="v8_4_1" FROM_TAG="latest" TO_TAG="latest"
    unset TO_REV
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"FROM_REV and TO_REV env must both be set"* ]]
}

@test "unreadable RELEASE_TARGETS_PATH (only needed for criterion 2) -> exit 1" {
    export FROM_REV="v8_4_1" FROM_TAG="latest"
    export TO_REV="master"   TO_TAG="next"
    RELEASE_TARGETS_PATH="${CWD}/does-not-exist.yml"
    export RELEASE_TARGETS_PATH
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"cannot read RELEASE_TARGETS_PATH"* ]]
}

@test "release-targets.yml missing master row entirely -> skip=false (fail-safe: don't skip when unclear)" {
    # Broken repo state -- master row absent from release-targets.yml.
    # The awk pass finds nothing; grep sees empty input and fails to
    # match. Script treats this as "next NOT on master" and skips=false,
    # letting the cell run so the real breakage surfaces.
    export FROM_REV="v8_4_1" FROM_TAG="latest"
    export TO_REV="master"   TO_TAG="next"
    write_release_targets ""
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"skip=false"* ]]
}

# ==== Output-shape guards ====

@test "skip=true output uses REASON_EOF heredoc pattern (multi-line safe)" {
    export FROM_REV="master" FROM_TAG="dev"
    export TO_REV="v8_4_1"   TO_TAG="latest"
    write_release_targets "8.5.0,dev,next"
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    # Heredoc pattern: skip_reason<<REASON_EOF ... REASON_EOF.
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"skip_reason<<REASON_EOF"* ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"REASON_EOF"* ]]
}

@test "GITHUB_OUTPUT unset -> falls back to stdout emission" {
    unset GITHUB_OUTPUT
    export FROM_REV="v8_4_1" FROM_TAG="latest"
    export TO_REV="v8_4_1"   TO_TAG="latest"
    write_release_targets "8.5.0,dev,next"
    run bash "${DETECT_UPGRADE_CELL_SKIP_SCRIPT}"
    [[ ${status} -eq 0 ]]
    # skip=false lands on stdout when GITHUB_OUTPUT sink absent.
    [[ "${output}" == *"skip=false"* ]]
}
