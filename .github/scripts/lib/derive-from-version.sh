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
    # prefix via sed `s|^.*/||`; strip the suffix ONLY when the
    # destination is itself a strict X_Y_Z shape; swap underscores
    # to dots; filter to strict X.Y.Z shape on the from-side too as
    # defense against convention drift (a stray `8_1-to-8_2_0`
    # two-segment left-side would otherwise silently vanish into
    # the next check with a confusing error).
    #
    # Both-sides X_Y_Z enforcement (fix for Rabbit review on the
    # G50 PR): the previous suffix regex `-to-[0-9_]+_upgrade.sql`
    # over-matched malformed destinations like
    # `8_5_0-to-8_6_upgrade.sql` (two-component right-side) — the
    # sed would strip and emit `8.5.0` as a candidate even though
    # the upgrade file itself was malformed. Tightening the suffix
    # to require `-to-X_Y_Z_upgrade.sql` means malformed
    # destinations fail the sed match, the full basename
    # falls through, the subsequent X.Y.Z grep on the full basename
    # rejects it, and the empty-check surfaces "no well-formed
    # files" cleanly.
    #
    # `|| true` on the pipeline because grep exits 1 when nothing
    # matches; the empty-check below handles that path with a
    # specific error.
    local candidates
    candidates=$(find "${sql_dir}" -maxdepth 1 -name '*-to-*_upgrade.sql' -type f 2>/dev/null \
        | sed -E 's|^.*/||; s|-to-[0-9]+_[0-9]+_[0-9]+_upgrade\.sql$||' \
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

# derive_from_version_fsupgrade [checkout_dir] [shipped_versions]
#
# Docker-side upgrade-cell FROM derivation. Reads docker-version from
# the checkout, looks up the matching fsupgrade-N.sh, extracts its
# priorOpenemrVersion, and (via shipped-versions-manifest intersect)
# returns a FROM that both EXISTS on Docker Hub as an immutable
# shipped tag AND matches sql_upgrade.php's strcmp-based --from
# semantics.
#
# Why fsupgrade-based for docker (not sql-based like the package side):
# the docker entrypoint's `check_upgrade` gates on docker-version
# delta (fsupgrade-N chain) and $v_database delta
# (check_schema_upgrade fallback). When BOTH match between FROM and
# TO, the entrypoint correctly no-ops — no real upgrade work is
# exercised. The sql-derived FROM (= "latest from-side in checkout's
# sql files") on master during active-dev-cycle resolves to the
# floating pre-release tag that happens to be byte-identical-scaffolded
# with master (same docker-version, same $v_database at cut), making
# the upgrade cell vacuous. The fsupgrade-based rule's fast path
# targets the docker-version=TO-1 boundary (fsupgrade-N.priorOpenemrVersion
# is the version shipped at docker-version=N-1), so the entrypoint
# has a real fsupgrade walk to perform. The fallback path (when
# fsupgrade-N.priorOpenemrVersion is itself unshipped — e.g. rel-820
# pointing at the abandoned 8.1.1) may start FURTHER back than N-1
# by walking the manifest for the highest shipped X.Y.Z below it;
# the entrypoint's fsupgrade loop still traverses every intermediate
# fsupgrade-K.sh (`#IfX`-guarded idempotent), just over a longer
# chain than N-1 → N. See G53/G56 in docs/release-mechanism-gaps.md
# for the full forensic.
#
# Manifest intersect with X.Y.Z-only filter: legacy branches like
# rel-820 carry fsupgrade-N.sh with priorOpenemrVersion pointing at
# versions that were cut but never shipped (rel-810's abandoned
# 8.1.1 line). The fallback walks the shipped-versions manifest for
# the highest X.Y.Z-shaped shipped version strictly less than the
# fsupgrade-named prior. Patch-shaped X.Y.Z.N entries are excluded
# because sql_upgrade.php's foreach uses `strcmp($file_from,
# $cli_from) < 0` to filter upgrade files, and a patch-shaped
# --from like 8.0.0.3 lex-compares as > "8.0.0" / "8.1.0" / etc.,
# which causes sql_upgrade.php to skip upgrade files it should
# apply. Only X.Y.Z-shaped --from values are safe.
#
# Arguments:
#   $1  (optional) Path to checkout root. Defaults to current working
#       directory. The function reads <checkout_dir>/docker-version
#       and <checkout_dir>/docker/release/upgrade/fsupgrade-N.sh.
#   $2  (required) Newline-separated list of shipped versions
#       (typically from website-openemr's data/releases.json). The
#       caller fetches this and injects it so the function stays
#       pure / testable without network.
#
# Stdout: A single X.Y.Z version string. Matches both an immutable
# Docker Hub tag AND a shipped-versions-manifest entry.
#
# Stderr: ::error:: line when the function returns non-zero.
# Diagnostic note line when the manifest-intersect fallback fires.
#
# Returns:
#   0  Derivation succeeded; one version printed.
#   1  Any of: docker-version missing or malformed, fsupgrade-N.sh
#      missing or has no parseable priorOpenemrVersion, shipped-
#      versions arg empty, OR the full walk found no X.Y.Z-shaped
#      shipped version candidate (fall-back-empty). Operator needs
#      to pass from_tag override.
#
# Portability: same as derive_from_version_sql_candidates — GNU
# coreutils (`sort -V` in particular). BSD/macOS via coreutils works.
derive_from_version_fsupgrade() {
    local checkout_dir="${1:-.}"
    local shipped="${2:-}"

    if [[ -z "${shipped}" ]]; then
        echo "::error::derive_from_version_fsupgrade: shipped-versions list (arg 2) is empty; caller must fetch + inject" >&2
        return 1
    fi

    local docker_version_file="${checkout_dir}/docker-version"
    if [[ ! -f "${docker_version_file}" ]]; then
        echo "::error::derive_from_version_fsupgrade: ${docker_version_file} not found" >&2
        return 1
    fi

    local docker_version
    docker_version=$(tr -d '[:space:]' < "${docker_version_file}")
    if [[ ! "${docker_version}" =~ ^[0-9]+$ ]]; then
        echo "::error::derive_from_version_fsupgrade: ${docker_version_file} content '${docker_version}' is not numeric" >&2
        return 1
    fi

    local fsupgrade="${checkout_dir}/docker/release/upgrade/fsupgrade-${docker_version}.sh"
    if [[ ! -f "${fsupgrade}" ]]; then
        echo "::error::derive_from_version_fsupgrade: ${fsupgrade} not found. Legacy branches that pre-date the fsupgrade convention need an explicit from_tag/from_version override." >&2
        return 1
    fi

    # Format expected in fsupgrade-N.sh: priorOpenemrVersion="X.Y.Z".
    # `|| true` on the pipeline mirrors the existing
    # derive_from_version_sql_candidates helper's pattern -- grep exits
    # 1 when nothing matches, head/sed propagate; the explicit shape
    # check below handles empty candidates with an actionable error.
    local candidate
    candidate=$({ grep -E '^priorOpenemrVersion=' "${fsupgrade}" \
                  | head -1 \
                  | sed -E 's/^priorOpenemrVersion="([^"]+)"$/\1/'; } || true)
    if [[ ! "${candidate}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "::error::derive_from_version_fsupgrade: could not extract X.Y.Z priorOpenemrVersion from ${fsupgrade} (got '${candidate}')" >&2
        return 1
    fi

    # Filter manifest to X.Y.Z-shaped only (strip X.Y.Z.N patch
    # overlays). sql_upgrade.php's --from filter uses strcmp against
    # X_Y_Z-shaped file names, and patch-shaped --from lex-compares
    # incorrectly against X.Y.Z files (skips files it should apply).
    local shipped_xyz
    shipped_xyz=$(grep -E '^[0-9]+\.[0-9]+\.[0-9]+$' <<< "${shipped}" || true)
    if [[ -z "${shipped_xyz}" ]]; then
        echo "::error::derive_from_version_fsupgrade: shipped-versions manifest contains no X.Y.Z-shaped entries after patch-shape filter" >&2
        return 1
    fi

    # Fast path: candidate itself is shipped. Return as-is.
    if grep -Fxq "${candidate}" <<< "${shipped_xyz}"; then
        printf '%s\n' "${candidate}"
        return 0
    fi

    # Fallback: walk the manifest for the highest X.Y.Z-shaped
    # shipped version strictly less than candidate. sort -uV
    # ascending; awk cuts at the first >= boundary; tail -1 picks
    # the max remaining. `|| true` guards the pipeline against
    # awk/sort returning non-zero on edge cases (empty intermediate
    # result emits empty fallback, handled by the empty-check below).
    local fallback
    fallback=$({ printf '%s\n%s\n' "${shipped_xyz}" "${candidate}" \
                 | sort -uV \
                 | awk -v ref="${candidate}" '$0 == ref { exit } { print }' \
                 | tail -1; } || true)
    if [[ -n "${fallback}" ]]; then
        echo "derive_from_version_fsupgrade: candidate '${candidate}' (from ${fsupgrade}) not in shipped-versions manifest; falling back to highest X.Y.Z-shaped shipped version < candidate: '${fallback}'" >&2
        printf '%s\n' "${fallback}"
        return 0
    fi

    echo "::error::derive_from_version_fsupgrade: candidate '${candidate}' not shipped AND no X.Y.Z-shaped shipped version below it; need explicit from_tag override" >&2
    return 1
}
