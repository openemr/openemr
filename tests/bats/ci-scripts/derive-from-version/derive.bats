# BATS tests for the derive_from_version_sql_candidates() predicate
# in .github/scripts/lib/derive-from-version.sh.
#
# This predicate is the single source of truth for "which prior
# versions does this checkout know how to upgrade from, per its own
# sql_upgrade.php wizard?" and is consumed by:
#   - .github/scripts/detect-acceptance-mode.sh (package side,
#     wraps the output with a shipped-versions manifest intersect)
#   - .github/workflows/acceptance-docker.yml (docker side, uses
#     the sql-only output directly; falls through to docker pull
#     which fails loud if the derived version isn't on Hub)
#
# **Primary invariant under test: upgrade MUST run when it should
# run.** A false "no derivation" result (return=1 when a valid
# upgrade path exists) causes the acceptance workflow to skip or
# fail an upgrade cell that would have produced real signal. This
# test suite is heavily weighted toward negative-case coverage so
# future predicate changes that would introduce false-skip
# regressions trip a visible test flip BEFORE they reach
# production. In particular, every "rel-XXX branch during a release
# cycle" shape and every "master during active dev cycle" shape
# gets a positive case here so the PR-triggered acceptance
# dispatches on rel branches keep running the upgrade cell.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# ==== Positive cases: upgrade MUST run when it should run ====
#
# Each row in release-targets.yml during a release cycle gets a
# fixture here. A regression that silently flips any of these to
# return=1 would cause that row's acceptance gate to skip the
# upgrade cell on every subsequent PR / dispatch -- the exact
# failure mode this suite exists to catch.

@test "rel-820 shape (candidates asc) -> returns 8.0.0 8.1.0 8.1.1" {
    # rel-820's checkout has upgrade sql files for 8.0.0->8.1.0,
    # 8.1.0->8.1.1, 8.1.1->8.2.0. From-side candidates: 8.0.0,
    # 8.1.0, 8.1.1 (not 8.2.0 which is to=rel-820's own version).
    local dir
    dir="$(write_sql_fixture rel-820 \
        "8_0_0-to-8_1_0" \
        "8_1_0-to-8_1_1" \
        "8_1_1-to-8_2_0")"
    run_candidates "${dir}"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == $'8.0.0\n8.1.0\n8.1.1' ]]
}

@test "rel-820 shape max via tail -1 -> 8.1.1 (upgrade from=8.1.1, to=8.2.0)" {
    local dir
    dir="$(write_sql_fixture rel-820 \
        "8_0_0-to-8_1_0" \
        "8_1_0-to-8_1_1" \
        "8_1_1-to-8_2_0")"
    run bash -c "source '${DERIVE_FROM_VERSION_LIB}' && derive_from_version_sql_candidates '${dir}' | tail -1"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.1.1" ]]
}

@test "rel-830 shape max -> 8.2.0 (upgrade from=8.2.0, to=8.3.0)" {
    local dir
    dir="$(write_sql_fixture rel-830 \
        "8_1_1-to-8_2_0" \
        "8_2_0-to-8_3_0")"
    run bash -c "source '${DERIVE_FROM_VERSION_LIB}' && derive_from_version_sql_candidates '${dir}' | tail -1"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.2.0" ]]
}

@test "rel-840 shape max -> 8.3.0 (replaces today's degenerate self-upgrade)" {
    local dir
    dir="$(write_sql_fixture rel-840 \
        "8_2_0-to-8_3_0" \
        "8_3_0-to-8_4_0")"
    run bash -c "source '${DERIVE_FROM_VERSION_LIB}' && derive_from_version_sql_candidates '${dir}' | tail -1"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.3.0" ]]
}

@test "master active-cycle shape max (next on rel-XXX) -> 8.5.0" {
    # During active dev cycle (next on rel-850), master's checkout
    # has 8_5_0-to-8_6_0_upgrade.sql cross-propagated by branch-cut.
    # The derivation picks 8.5.0 -> upgrade cell exercises latest
    # (shipped 8.5.0) -> master-dev (8.6.0), a real test.
    local dir
    dir="$(write_sql_fixture master-active \
        "8_4_0-to-8_5_0" \
        "8_4_1-to-8_5_0" \
        "8_5_0-to-8_6_0")"
    run bash -c "source '${DERIVE_FROM_VERSION_LIB}' && derive_from_version_sql_candidates '${dir}' | tail -1"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.5.0" ]]
}

@test "master between-cycles shape max (next on master) -> 8.4.1" {
    # During between-cycles (next on master, no rel-XXX cut yet),
    # master's checkout has 8_4_0-to-8_5_0_upgrade.sql etc. but
    # the fsupgrade-N machinery for 8.4.1 -> 8.5.0 isn't scaffolded
    # yet. The lib returns 8.4.1 (max from-candidate); the docker-
    # orchestrator between-cycles check (criterion 2 in
    # detect-upgrade-cell-skip.sh) is what actually gates the test
    # from running in this window. This test pins that the lib
    # itself continues to return a sensible value regardless --
    # the orchestrator-level skip is a separate concern.
    local dir
    dir="$(write_sql_fixture master-between \
        "8_4_0-to-8_5_0" \
        "8_4_1-to-8_5_0")"
    run bash -c "source '${DERIVE_FROM_VERSION_LIB}' && derive_from_version_sql_candidates '${dir}' | tail -1"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.4.1" ]]
}

@test "single candidate -> returned verbatim" {
    local dir
    dir="$(write_sql_fixture single \
        "8_1_0-to-8_2_0")"
    run_candidates "${dir}"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.1.0" ]]
}

@test "duplicate from-versions are de-duplicated" {
    # Two files with same from-side (e.g., 8.4.1 upgrading to both
    # 8.5.0 and 8.5.1-dev) collapse to one candidate.
    local dir
    dir="$(write_sql_fixture dupes \
        "8_4_1-to-8_5_0" \
        "8_4_1-to-8_5_1")"
    run_candidates "${dir}"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.4.1" ]]
}

@test "semver sort respects multi-digit minors (10.0.0 > 2.0.0)" {
    # Guard against lexicographic sort regression. If sort -V
    # drops to sort -u (no -V), 10.0.0 sorts BEFORE 2.0.0.
    local dir
    dir="$(write_sql_fixture semver \
        "2_0_0-to-3_0_0" \
        "10_0_0-to-11_0_0")"
    run bash -c "source '${DERIVE_FROM_VERSION_LIB}' && derive_from_version_sql_candidates '${dir}' | tail -1"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "10.0.0" ]]
}

@test "distractor .sql files (database.sql etc) are ignored" {
    # The glob pattern -name '*-to-*_upgrade.sql' must scope to
    # upgrade files specifically. If it over-matches, random .sql
    # files in sql/ could emit garbage candidates that fail the
    # X.Y.Z shape filter -- but the empty-check would then
    # incorrectly report no files. Test that only well-formed
    # upgrade files are enumerated.
    local dir
    dir="$(write_sql_fixture_with_distractors with-distractors)"
    run_candidates "${dir}"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.1.1" ]]
}

# ==== Exclude-version cases (recovery-mode / to-equals-max) ====

@test "exclude max candidate -> next-highest returned" {
    local dir
    dir="$(write_sql_fixture exclude-max \
        "8_1_0-to-8_2_0" \
        "8_1_1-to-8_2_0" \
        "8_2_0-to-8_3_0")"
    run bash -c "source '${DERIVE_FROM_VERSION_LIB}' && derive_from_version_sql_candidates '${dir}' '8.2.0' | tail -1"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.1.1" ]]
}

@test "exclude middle candidate -> max unchanged" {
    local dir
    dir="$(write_sql_fixture exclude-middle \
        "8_1_0-to-8_2_0" \
        "8_1_1-to-8_2_0" \
        "8_2_0-to-8_3_0")"
    run bash -c "source '${DERIVE_FROM_VERSION_LIB}' && derive_from_version_sql_candidates '${dir}' '8.1.1' | tail -1"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.2.0" ]]
}

@test "exclude version NOT in candidates -> max unchanged, no error" {
    # Operator passed an exclude that happens not to apply. Safe
    # no-op; max is just the sql-derived max.
    local dir
    dir="$(write_sql_fixture exclude-nonexistent \
        "8_1_0-to-8_2_0")"
    run bash -c "source '${DERIVE_FROM_VERSION_LIB}' && derive_from_version_sql_candidates '${dir}' '9.9.9' | tail -1"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.1.0" ]]
}

@test "exclude the only candidate -> rc=1 fail-loud with actionable error" {
    # Real "no valid upgrade path" state. Caller must see this.
    local dir
    dir="$(write_sql_fixture exclude-only \
        "8_1_0-to-8_2_0")"
    run_candidates "${dir}" "8.1.0"
    [[ "${status}" -eq 1 ]]
    [[ "${output}" == *"after excluding"* ]]
    [[ "${output}" == *"'8.1.0'"* ]]
    [[ "${output}" == *"no from-version remains"* ]]
}

@test "empty-string exclude -> treated as no-exclude (safe no-op)" {
    local dir
    dir="$(write_sql_fixture empty-exclude \
        "8_1_0-to-8_2_0")"
    run_candidates "${dir}" ""
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.1.0" ]]
}

# ==== Negative cases: FALSE-SKIP regression protection ====
#
# These cases pin failure modes to return=1 with a specific error
# shape. A silent return=0 with empty output here would propagate
# as an upgrade cell skip downstream (fail-safe for "unknown
# state", but hiding real breakage). Prefer loud failure over
# silent skip when the input is genuinely bad.

@test "sql/ directory missing -> rc=1 with actionable error" {
    local dir="${CWD}/no-sql"
    mkdir -p "${dir}"  # dir exists but has no sql/ subdir
    run_candidates "${dir}"
    [[ "${status}" -eq 1 ]]
    [[ "${output}" == *"no sql/ directory in checkout"* ]]
    [[ "${output}" == *"'${dir}'"* ]]
}

@test "checkout_dir itself missing -> rc=1" {
    run_candidates "/path/that/does/not/exist"
    [[ "${status}" -eq 1 ]]
    [[ "${output}" == *"no sql/ directory in checkout"* ]]
}

@test "sql/ exists but empty -> rc=1 with 'no well-formed' error" {
    local dir="${CWD}/empty-sql"
    mkdir -p "${dir}/sql"
    run_candidates "${dir}"
    [[ "${status}" -eq 1 ]]
    [[ "${output}" == *"no well-formed sql/*-to-*_upgrade.sql files found"* ]]
}

@test "sql/ has only non-upgrade files -> rc=1" {
    local dir="${CWD}/non-upgrade-only"
    mkdir -p "${dir}/sql"
    touch "${dir}/sql/database.sql" "${dir}/sql/schema.sql"
    run_candidates "${dir}"
    [[ "${status}" -eq 1 ]]
    [[ "${output}" == *"no well-formed sql/*-to-*_upgrade.sql files found"* ]]
}

@test "malformed X_Y_Z shape (two-segment left: 8_1-to-8_2_0) -> rc=1" {
    # Guards against convention drift. A hypothetical two-segment
    # from-side would otherwise emit `8.1` which fails the X.Y.Z
    # grep and silently vanishes. The X.Y.Z shape filter catches
    # it at enumeration; empty-check then returns 1 with the
    # "no well-formed" error.
    local dir="${CWD}/malformed"
    mkdir -p "${dir}/sql"
    touch "${dir}/sql/8_1-to-8_2_0_upgrade.sql"
    run_candidates "${dir}"
    [[ "${status}" -eq 1 ]]
    [[ "${output}" == *"no well-formed"* ]]
}

@test "mix of valid + malformed -> only valid returned (no silent-skip)" {
    local dir="${CWD}/mixed"
    mkdir -p "${dir}/sql"
    touch "${dir}/sql/8_1_1-to-8_2_0_upgrade.sql"   # valid
    touch "${dir}/sql/8_1-to-8_2_0_upgrade.sql"     # malformed (two-segment)
    touch "${dir}/sql/foo-to-bar_upgrade.sql"       # malformed (non-numeric)
    run_candidates "${dir}"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.1.1" ]]
}

@test "checkout_dir arg omitted entirely -> defaults to cwd" {
    # Function default is cwd. Create sql/ in CWD and confirm the
    # cwd-based call works. Guards against a signature change that
    # would make the arg required and silently fail CI jobs that
    # relied on the default.
    mkdir -p "${CWD}/sql"
    touch "${CWD}/sql/8_1_0-to-8_2_0_upgrade.sql"
    run bash -c "cd '${CWD}' && source '${DERIVE_FROM_VERSION_LIB}' && derive_from_version_sql_candidates"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.1.0" ]]
}

@test "checkout_dir is a FILE (not a directory) -> rc=1" {
    # Weird input shape — operator passed a filename by mistake.
    # The <dir>/sql path doesn't resolve as a directory, so the
    # existing guard fires correctly.
    local file="${CWD}/not-a-dir.txt"
    touch "${file}"
    run_candidates "${file}"
    [[ "${status}" -eq 1 ]]
    [[ "${output}" == *"no sql/ directory in checkout"* ]]
}
