#!/usr/bin/env bash
#
# Shared predicate helpers for querying .github/release-targets.yml.
#
# Sourced (not executed) by callers that need read-only answers about
# the current shape of release-targets.yml. Not executable on its own
# -- these are function definitions consumed by the caller.
#
# The functions here are PURE: no side effects, no stdout chatter, no
# $GITHUB_OUTPUT writes, no `::error::` emissions. Callers interpret the
# function's exit code and decide how to narrate the result (an upgrade-
# cell skip-reason heredoc, an orchestrator step summary line, a BATS
# assertion). Keeping the predicate dumb lets different callers compose
# their own context around the same underlying signal.
#
# Why a lib and not an inline block in each caller: multiple workflows
# need to answer the same question ("is master row carrying `next`?")
# from release-targets.yml. The first caller
# (.github/scripts/detect-upgrade-cell-skip.sh) uses it to decide
# whether the docker acceptance workflow's upgrade cell should skip.
# The second caller (.github/workflows/docker-release-orchestrator.yml)
# uses it to decide whether a daily master-row dispatch should pass
# `require_upgrade_cell=true` (real ship moment, loud-fail on skip) or
# `require_upgrade_cell=false` (between-cycles floating-tag refresh,
# tolerant-skip is the correct answer). Both reach the same awk+grep
# predicate -- extracting it here keeps the signal single-sourced so
# a future change to the predicate (e.g., a wider match pattern, a
# different row-identity rule) propagates to every consumer at once.

# Predicate: does the master row's `docker_tags:` line in
# release-targets.yml contain the floating `next` tag?
#
# Arguments:
#   $1  Path to release-targets.yml. Required. Caller MUST pass the
#       MASTER-authoritative copy.
#
#       release-targets.yml is master-only -- it is NOT in the
#       .github/byte-identical.yml manifest. Rel branches carry only
#       the frozen snapshot captured at cut time; master then adds
#       rows (branch-cut), promotes rows (release-finalize), and
#       strips `next` from master's row (patch-prep). The rel-branch
#       copy diverges from master's live state and reading it gives
#       the wrong answer for every consumer of this predicate.
#
#       Callers that MIGHT fire from a non-master ref (anything
#       byte-identical-synced -- acceptance-docker.yml, this lib
#       itself) must fetch master's copy explicitly:
#         git fetch --depth=1 origin master
#         git show origin/master:.github/release-targets.yml > /tmp/rt.yml
#       then pass /tmp/rt.yml here. See acceptance-docker.yml:604-617
#       for the shape (comment there is kept in sync with this one).
#
#       Callers that ALWAYS fire from master (docker-release-
#       orchestrator.yml -- gated by `if: github.ref ==
#       refs/heads/master`) can pass .github/release-targets.yml
#       directly from a sparse checkout.
#
# Exit codes:
#   0   master row carries `next` (between-cycles state active)
#   1   master row does NOT carry `next` (rel-XXX branch owns the dev
#       cycle; master has upgrade infra for its current version.php)
#   2   bad shape -- RELEASE_TARGETS_PATH missing/unreadable. Callers
#       should fail loudly rather than treating this as a "no" answer.
#
# Match semantics: `grep -qE '(^|,| )next(,| |$)'` anchors `next` as a
# whole token so `next-dev` or `nothing-next` don't false-match. The
# awk filter scopes to the master row only -- the `- branch:` sentinel
# on any other row closes the master scope, so an absent `docker_tags:`
# line in master won't leak into a later row's tags being read.
#
# YAML inline comment handling:
#   - The `- branch: master` line match allows an optional `# ...`
#     trailer so `- branch: master  # daily floating tags` still
#     scopes correctly (yq's structural parse treats both forms as
#     the same row; this predicate must agree).
#   - The `docker_tags:` line match strips any trailing `# ...`
#     comment before the next-tag match so a comment like
#     `docker_tags: 8.5.0,dev  # next moved to rel-840` cannot
#     count the commented-out `next` as a tag. This is the more
#     important of the two anchors because the failure direction
#     is a FALSE POSITIVE (predicate reports between-cycles when
#     it isn't), which silently stands down the release-mode
#     guardrail.
#
# BATS coverage lives at tests/bats/ci-scripts/release-targets-queries/
# and must include negative cases (master without `next`, master with
# `next-dev` only, no master row) to guarantee we never flip to a
# false-positive answer. The caller side of this predicate makes
# release-mode guardrails stand down when it returns 0, so a false
# positive would silently skip real signal and ship an un-validated
# artifact. Keep the test suite protective.
master_row_carries_next() {
    local release_targets_path="${1:-}"
    if [[ -z "${release_targets_path}" ]] || [[ ! -r "${release_targets_path}" ]]; then
        return 2
    fi
    local master_tags_line
    master_tags_line=$(awk '
        /^- branch: master([[:space:]]+#.*)?$/ { in_master=1; next }
        /^- branch:/         { if (in_master) exit; next }
        in_master && /^  docker_tags:/ {
            sub(/[[:space:]]+#.*/, "")
            print
            exit
        }
    ' "${release_targets_path}")
    if printf '%s' "${master_tags_line}" | grep -qE '(^|,| )next(,| |$)'; then
        return 0
    fi
    return 1
}
