#!/usr/bin/env bash
#
# Shared helper: enumerate the "from-version" candidates for an
# upgrade acceptance cell from a checkout's sql/*-to-*_upgrade.sql
# filenames.
#
# Sourced (not executed) by callers that need to know "which prior
# versions does this checkout know how to upgrade from, per its own
# sql_upgrade.php wizard?" without hard-coding a version. The
# derivation uses only the checkout's own sql/ directory -- no
# network, no manifest fetch, no state outside the file tree. Pure
# function in / newline-separated list out.
#
# Why extract this: two callers share the same signal.
#   1. .github/scripts/detect-acceptance-mode.sh (package side)
#      wraps the output with a shipped-versions-manifest intersect
#      so a tarball download of the derived version is guaranteed
#      to exist on GitHub Releases.
#   2. .github/workflows/acceptance-docker.yml (docker side) uses
#      the sql-only output directly. If the max-candidate happens
#      not to be on Docker Hub (rare: a rel branch that cut but
#      never published its version tag), the downstream `docker
#      pull openemr/openemr:<version>` step will fail LOUD naming
#      the exact tag -- not silent-skip. The "nothing-breaks-on-
#      unreleased-version" invariant depends on that loud-pull-
#      fail; preserve it when integrating.
#
# Why sql-only is enough for docker (no manifest filter needed on
# that side): Docker Hub carries pre-production tags (e.g.
# `openemr/openemr:8.5.0` currently points at master's dev build)
# in addition to shipped releases. A manifest intersect on the
# docker side wouldn't distinguish those from truly-shipped
# versions anyway. Package land doesn't have that problem
# (tarballs only exist on GitHub Releases after publish), so it
# keeps the manifest filter.
#
# BATS coverage lives at tests/bats/ci-scripts/derive-from-version/.
# Negative-case ratio is intentionally weighted: upgrade MUST run
# when it should run, so false "no derivation" answers that would
# skip an otherwise-valid upgrade test are the regression this
# test suite is designed to catch.

# derive_from_version_sql_candidates [checkout_dir] [exclude_version]
#
# Enumerates <checkout_dir>/sql/*-to-*_upgrade.sql filenames, extracts
# each file's "from" segment, filters to strict X.Y.Z shape, de-
# duplicates, sorts version-ascending, and prints the result as a
# newline-separated list on stdout.
#
# Arguments:
#   $1  (optional) Path to checkout root. Defaults to current working
#       directory. The function reads <checkout_dir>/sql/.
#   $2  (optional) A specific X.Y.Z version to exclude from
#       candidates. Use for the recovery-mode case where to_version
#       is already a shipped version and happens to match a sql-
#       derived candidate -- excluding it preserves a real upgrade
#       transition (from=next-highest, not from=to). Empty string =
#       no exclude.
#
# Stdout: Newline-separated candidate list, sorted ascending by
# semver. Caller takes `tail -1` for the highest, or filters further
# before picking (as the package side does with a manifest intersect).
# Empty stdout only on return=1; on success at least one candidate
# is always emitted.
#
# Stderr: ::error:: line when the function returns non-zero.
#
# Returns:
#   0  Derivation succeeded; one-or-more candidates printed.
#   1  Any of: sql/ missing, no sql/*-to-*_upgrade.sql files found,
#      no well-formed X_Y_Z-to-A_B_C filename shape, OR exclude-
#      version filter emptied the candidate set. Each returns with
#      its own actionable ::error:: on stderr.
#
# Caller contract:
#   - Function is pure: no network, no state writes, no error
#     output beyond stderr ::error:: lines on failure.
#   - Caller interprets the result (emit to $GITHUB_OUTPUT, resolve
#     to a docker tag, intersect with a manifest, etc.).
#
# Portability: uses only POSIX utilities (find, sed, tr, grep, sort,
# printf). The `sed -E` + `grep -E` ERE syntax works on both GNU and
# BSD; `find -maxdepth` is also widely supported (not strictly POSIX
# but universal in practice). `sort -V` version-sort is a GNU
# extension; the openemr BATS runners on CI use ubuntu-24.04 (GNU),
# and macOS/BSD environments typically have `sort -V` via coreutils.
derive_from_version_sql_candidates() {
    local checkout_dir="${1:-.}"
    local exclude_version="${2:-}"

    local sql_dir="${checkout_dir}/sql"
    if [[ ! -d "${sql_dir}" ]]; then
        echo "::error::derive_from_version_sql_candidates: no sql/ directory in checkout at '${checkout_dir}'" >&2
        return 1
    fi

    # Enumerate sql/*-to-*_upgrade.sql (filenames like
    # `sql/8_1_0-to-8_1_1_upgrade.sql`) via find; strip directory
    # prefix via sed `s|^.*/||`; strip `-to-<version>_upgrade.sql`
    # suffix; swap underscores to dots; filter to strict X.Y.Z shape
    # as defense against convention drift (a stray `8_1-to-8_2_0`
    # two-segment left-side would otherwise silently vanish into
    # the next check with a confusing error). `|| true` on the
    # pipeline because grep exits 1 when nothing matches; the
    # empty-check below handles that path with a specific error.
    local candidates
    candidates=$(find "${sql_dir}" -maxdepth 1 -name '*-to-*_upgrade.sql' -type f 2>/dev/null \
        | sed -E 's|^.*/||; s|-to-[0-9_]+_upgrade\.sql$||' \
        | tr '_' '.' \
        | grep -E '^[0-9]+\.[0-9]+\.[0-9]+$' \
        | sort -uV || true)
    if [[ -z "${candidates}" ]]; then
        echo "::error::derive_from_version_sql_candidates: no well-formed sql/*-to-*_upgrade.sql files found in '${sql_dir}' (expected X_Y_Z-to-A_B_C filename shape)" >&2
        return 1
    fi

    # Optional exclude-version filter. Fails loud when the filter
    # would empty the set -- that's a real "no valid upgrade path
    # other than to-itself" state the caller must know about.
    if [[ -n "${exclude_version}" ]]; then
        local filtered
        filtered=$(grep -v -x -F "${exclude_version}" <<< "${candidates}" || true)
        if [[ -z "${filtered}" ]]; then
            local candidates_line
            candidates_line=$(tr '\n' ' ' <<< "${candidates}" || true)
            echo "::error::derive_from_version_sql_candidates: after excluding '${exclude_version}', no from-version remains. Candidates from '${sql_dir}': ${candidates_line}." >&2
            return 1
        fi
        candidates="${filtered}"
    fi

    # candidates is already X.Y.Z-shape (per the grep filter), de-
    # duplicated, and sorted ascending (sort -uV above). Emit as-is.
    printf '%s\n' "${candidates}"
}
