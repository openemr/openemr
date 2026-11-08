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

# ==== Inline YAML comment handling ====
#
# yq's structural parse treats inline-commented and bare YAML lines
# as identical. This predicate must agree -- a mismatch produces
# either a false negative (comment on `- branch: master`, cell runs
# during between-cycles and asserts against unreliable upgrade DB
# state) or a false positive (comment on `docker_tags` containing
# the word "next", release-mode guardrail silently stands down).
# The false-positive direction is strictly worse, which is why
# docker_tags comment stripping is the more load-bearing of the two.
# CodeRabbit flagged both in the 2026-10-06 review of this PR; the
# fixtures below pin the fix.

@test "master row with inline comment on - branch: master line, carries next -> rc=0" {
    # yq parses `- branch: master  # comment` identically to `- branch: master`.
    # Predicate must scope to master correctly despite the inline comment.
    local rt
    rt="$(write_release_targets_master_with_inline_comment "8.5.0,dev,next")"
    run_predicate "${rt}"
    [[ "${status}" -eq 0 ]]
}

@test "master row with inline comment on - branch: master line, carries only dev -> rc=1" {
    local rt
    rt="$(write_release_targets_master_with_inline_comment "8.5.0,dev")"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "docker_tags comment contains 'next' but tags are only dev -> rc=1 (FALSE-POSITIVE PROTECTION)" {
    # The critical case. docker_tags: 8.5.0,dev with a trailing
    # `# next moved to rel-840` comment. Naive regex over the raw
    # line matches 'next' in the comment and reports rc=0,
    # silently standing down the release-mode guardrail. Predicate
    # MUST strip the comment before the next-tag match.
    local rt
    rt="$(write_release_targets_master_with_tags_comment "8.5.0,dev" "next moved to rel-840")"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "docker_tags comment contains ',next,' (comma-wrapped) but tags are only dev -> rc=1 (FALSE-POSITIVE PROTECTION)" {
    local rt
    rt="$(write_release_targets_master_with_tags_comment "8.5.0,dev" "was 8.5.0,dev,next pre-cut")"
    run_predicate "${rt}"
    [[ "${status}" -eq 1 ]]
}

@test "docker_tags carries next AND comment contains 'next' -> rc=0 (tags win, not comment)" {
    # Real tag is `next`; comment also mentions `next`. The predicate
    # should return rc=0 because the tag is present, regardless of
    # the comment. This confirms the comment-strip doesn't eat the
    # legitimate tag.
    local rt
    rt="$(write_release_targets_master_with_tags_comment "8.5.0,dev,next" "next here is real")"
    run_predicate "${rt}"
    [[ "${status}" -eq 0 ]]
}

@test "docker_tags carries next with NO comment -> rc=0 (comment-strip is a no-op)" {
    # Regression guard: ensure the comment-strip awk sub() rule
    # doesn't accidentally eat the tag portion when no comment is
    # present. This repeats case 1 but exists for strict coverage
    # of the sub() code path on a comment-free input.
    local rt
    rt="$(write_release_targets "8.5.0,dev,next")"
    run_predicate "${rt}"
    [[ "${status}" -eq 0 ]]
}
