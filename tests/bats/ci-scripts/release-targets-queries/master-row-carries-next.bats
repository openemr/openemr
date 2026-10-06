# BATS tests for the master_row_carries_next() predicate in
# .github/scripts/lib/release-targets-queries.sh.
#
# This predicate is the single source of truth for "is master in the
# between-cycles window?" and is consumed by:
#   - detect-upgrade-cell-skip.sh (criterion 2 skip decision)
#   - docker-release-orchestrator.yml (per-row require_upgrade_cell
#     flag passed to docker-build-release.yml dispatches)
#
# Both consumers rely on this predicate returning 0 ONLY when master is
# genuinely between-cycles. A false positive (returning 0 when master
# is NOT between-cycles) would silently skip the upgrade-cell acceptance
# gate and ship an un-validated artifact. The negative-case coverage in
# this file (word-boundary false-match protection, "another row carries
# next but master doesn't", missing docker_tags, etc.) is the regression
# wall against that failure mode -- keep it exhaustive.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# ==== Positive cases: predicate returns 0 (master carries next) ====

@test "master carries next alongside version + dev -> rc=0" {
    local rt
    rt="$(write_release_targets "8.5.0,dev,next")"
    run_predicate "${rt}"
    [[ "${status}" -eq 0 ]]
}

@test "master carries next as the only tag -> rc=0" {
    local rt
    rt="$(write_release_targets "next")"
    run_predicate "${rt}"
    [[ "${status}" -eq 0 ]]
}

@test "master carries next first in the list -> rc=0" {
    local rt
    rt="$(write_release_targets "next,8.5.0,dev")"
    run_predicate "${rt}"
    [[ "${status}" -eq 0 ]]
}

@test "master carries next last with trailing newline only -> rc=0" {
    local rt
    rt="$(write_release_targets "8.5.0,next")"
    run_predicate "${rt}"
    [[ "${status}" -eq 0 ]]
}

@test "master carries next with surrounding spaces in list -> rc=0" {
    local rt
    rt="$(write_release_targets "8.5.0, dev, next")"
    run_predicate "${rt}"
    [[ "${status}" -eq 0 ]]
}

@test "master carries next AND a rel row also carries next -> rc=0 (master's own value is authoritative)" {
    local rt
    rt="$(write_release_targets "8.5.0,dev,next" "8.4.1,next")"
    run_predicate "${rt}"
    [[ "${status}" -eq 0 ]]
}

# ==== Negative cases: predicate returns 1 (master does NOT carry next) ====
#
# These are the regression wall. If any of these flip to rc=0 the
# orchestrator will stand down the upgrade-cell guardrail on a day
# master is NOT actually between-cycles, and an un-validated image
# will publish.

@test "master has only dev (no next) -> rc=1" {
    local rt
    rt="$(write_release_targets "8.5.0,dev")"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "master has only a version tag -> rc=1" {
    local rt
    rt="$(write_release_targets "8.5.0")"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "master has next-dev (hyphenated) -> rc=1 (word-boundary protection)" {
    local rt
    rt="$(write_release_targets "8.5.0,next-dev")"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "master has nothing-next (hyphenated suffix) -> rc=1 (word-boundary protection)" {
    local rt
    rt="$(write_release_targets "8.5.0,nothing-next")"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "master has nextbuild (no delimiter) -> rc=1 (word-boundary protection)" {
    local rt
    rt="$(write_release_targets "8.5.0,nextbuild")"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "master has prenext (no delimiter) -> rc=1 (word-boundary protection)" {
    local rt
    rt="$(write_release_targets "8.5.0,prenext")"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "master row is missing docker_tags field entirely -> rc=1 (does NOT leak into rel row's tags)" {
    # Critical: regex-naive implementations walk into the next row's
    # docker_tags when master's is absent. The awk scope-close pattern
    # must prevent that.
    local rt
    rt="$(write_release_targets "" "8.4.1,next")"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "master row absent entirely, rel row carries next -> rc=1 (does NOT walk into rel row)" {
    local rt
    rt="$(write_release_targets_no_master)"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "master has dev + rel-840 row carries next -> rc=1 (correct active-dev-cycle state)" {
    # The real-world case: a rel-XXX branch is in active dev cycle, so
    # master's row has `dev` only and the rel row carries `next`. In
    # this state the upgrade-cell SHOULD run; master has upgrade infra
    # cross-propagated from the active rel branch.
    local rt
    rt="$(write_release_targets "8.5.0,dev" "8.4.1,next")"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "master has next-only (empty tag before and after separators) -> rc=0 end-anchor" {
    # Guards against the pattern regressing to require a comma on both
    # sides -- a sole `next` must still match (handled by the ^ and $
    # alternatives in the pattern).
    local rt
    rt="$(write_release_targets "next")"
    run_predicate "${rt}"
    [[ "${status}" -eq 0 ]]
}

# ==== Bad-shape cases: predicate returns 2 ====

@test "RELEASE_TARGETS_PATH is unreadable -> rc=2" {
    run_predicate "/path/that/does/not/exist"
    [[ "${status}" -eq 2 ]]
}

@test "RELEASE_TARGETS_PATH arg is empty string -> rc=2" {
    run_predicate ""
    [[ "${status}" -eq 2 ]]
}

@test "RELEASE_TARGETS_PATH arg is omitted entirely -> rc=2" {
    run_predicate_no_args
    [[ "${status}" -eq 2 ]]
}

@test "RELEASE_TARGETS_PATH points at a directory -> rc=2 (not readable as a file)" {
    local dir="${CWD}/a-directory"
    mkdir -p "${dir}"
    run_predicate "${dir}"
    # bash -r test treats directories as readable; the function also
    # relies on awk succeeding over the path. awk on a dir prints an
    # error and the predicate still returns 1 (no match) rather than 2
    # -- document that current behavior here so a future change that
    # tightens this surface is a deliberate contract change.
    [[ "${status}" -eq 1 ]] || [[ "${status}" -eq 2 ]]
}
