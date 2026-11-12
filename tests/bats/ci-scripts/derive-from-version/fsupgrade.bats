# BATS tests for the derive_from_version_fsupgrade() predicate in
# .github/scripts/lib/derive-from-version.sh.
#
# This predicate is the docker side's FROM-version selector. It reads
# the checkout's own docker-version + fsupgrade-N.sh scaffolding and
# intersects with a caller-supplied shipped-versions manifest to
# return a FROM that:
#   - EXISTS on Docker Hub as an immutable shipped tag (manifest
#     intersect guarantees this)
#   - MATCHES sql_upgrade.php's strcmp-based --from semantics
#     (X.Y.Z-only filter guarantees this; patch-shaped X.Y.Z.N
#     entries from the manifest are filtered out of the fallback
#     walk)
#   - Fast path targets docker-version=TO-1 (the fsupgrade-N scaffolding
#     naturally encodes this). Fallback walk may start earlier when the
#     fsupgrade-named prior is itself unshipped (e.g., rel-820's
#     fsupgrade-12 points at the never-shipped 8.1.1 from rel-810's
#     abandoned cycle; fallback walks further back to the highest
#     X.Y.Z-shaped shipped version below it).
#
# Primary invariant: a well-scaffolded rel branch must always produce
# a working FROM. Negative cases cover the "legacy branch without
# fsupgrade", "manifest missing candidate", and "no shipped version
# below candidate" paths — each returns distinct error codes/messages
# so operators can diagnose + override.
#
# Helper note: `run_fsupgrade` uses `run --separate-stderr`, so
# `${output}` carries only stdout (the resolved version string) and
# `${stderr}` carries diagnostics (fallback-firing note) + errors.
# Keep assertions pointed at the right stream — stderr for error-case
# message checks, stdout for the resolved version.
#
# See docs/release-mechanism-gaps.md G56 for the design rationale.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# ==== Positive cases — real rel-branch shapes that MUST work ====

@test "rel-850 shape: docker-version=16, fsupgrade-16.prior=8.4.1, shipped manifest contains 8.4.1 -> returns 8.4.1" {
    local dir
    dir="$(write_fsupgrade_fixture rel-850 16 "8.4.1")"
    local shipped
    shipped=$(printf '%s\n' "8.0.0" "8.1.1" "8.2.0" "8.3.0" "8.4.0" "8.4.1")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.4.1" ]]
}

@test "rel-840 shape: docker-version=15, fsupgrade-15.prior=8.4.0, shipped manifest contains 8.4.0 -> returns 8.4.0" {
    local dir
    dir="$(write_fsupgrade_fixture rel-840 15 "8.4.0")"
    local shipped
    shipped=$(printf '%s\n' "8.0.0" "8.1.1" "8.2.0" "8.3.0" "8.4.0")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.4.0" ]]
}

@test "master shape (same as rel-850 at cut time): docker-version=16, fsupgrade-16.prior=8.4.1 -> returns 8.4.1" {
    local dir
    dir="$(write_fsupgrade_fixture master 16 "8.4.1")"
    local shipped
    shipped=$(printf '%s\n' "8.0.0" "8.1.1" "8.2.0" "8.3.0" "8.4.0" "8.4.1")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.4.1" ]]
}

# ==== Manifest-intersect fallback: legacy branches with skipped-version scaffolding ====

@test "rel-820 shape: fsupgrade-12.prior=8.1.1 (NOT in manifest, rel-810 abandoned) -> fallback walks to highest X.Y.Z-shaped < 8.1.1 = 8.0.0" {
    local dir
    dir="$(write_fsupgrade_fixture rel-820 12 "8.1.1")"
    # Manifest reflects today's shipped state: 8.0.0 shipped, 8.1.0
    # and 8.1.1 NOT shipped (rel-810 was cut but abandoned).
    local shipped
    shipped=$(printf '%s\n' "8.0.0" "8.2.0" "8.3.0" "8.4.0" "8.4.1")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 0 ]]
    # stdout: the resolved version (stdout-only via --separate-stderr).
    [[ "${output}" == "8.0.0" ]]
    # stderr: the diagnostic note about fallback firing.
    [[ "${stderr}" == *"candidate '8.1.1'"* ]]
}

@test "patch-shape filter: fallback skips 8.0.0.3 even when present in manifest, picks X.Y.Z-only 8.0.0" {
    # sql_upgrade.php's strcmp filter mis-handles X.Y.Z.N --from
    # values; the fallback must skip patch entries.
    local dir
    dir="$(write_fsupgrade_fixture rel-820 12 "8.1.1")"
    local shipped
    shipped=$(printf '%s\n' "8.0.0" "8.0.0.1" "8.0.0.2" "8.0.0.3" "8.2.0")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.0.0" ]]
}

@test "fast path: candidate IS in manifest, returned as-is (no fallback walk, no diagnostic)" {
    local dir
    dir="$(write_fsupgrade_fixture rel-840 15 "8.4.0")"
    local shipped
    shipped=$(printf '%s\n' "8.4.0" "8.3.0" "8.2.0")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 0 ]]
    [[ "${output}" == "8.4.0" ]]
    # Fast path emits no stderr diagnostic (distinguishable from the
    # fallback path for log-grep tooling).
    [[ "${stderr}" != *"falling back"* ]]
}

# ==== Negative cases — distinct error paths; errors go to stderr ====

@test "missing docker-version file -> return 1 with actionable error on stderr" {
    local dir="${CWD}/no-docker-version"
    mkdir -p "${dir}/docker/release/upgrade"
    local shipped
    shipped=$(printf '%s\n' "8.4.1")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 1 ]]
    [[ "${stderr}" == *"docker-version"* ]]
    [[ "${stderr}" == *"not found"* ]]
}

@test "non-numeric docker-version -> return 1" {
    local dir="${CWD}/bad-docker-version"
    mkdir -p "${dir}/docker/release/upgrade"
    printf 'notanumber\n' > "${dir}/docker-version"
    local shipped
    shipped=$(printf '%s\n' "8.4.1")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 1 ]]
    [[ "${stderr}" == *"not numeric"* ]]
}

@test "missing fsupgrade-N.sh (legacy branch without scaffolding) -> return 1 with override hint" {
    local dir="${CWD}/legacy-no-fsupgrade"
    mkdir -p "${dir}/docker/release/upgrade"
    printf '9\n' > "${dir}/docker-version"
    local shipped
    shipped=$(printf '%s\n' "8.0.0" "8.4.1")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 1 ]]
    [[ "${stderr}" == *"fsupgrade-9.sh"* ]]
    [[ "${stderr}" == *"override"* ]]
}

@test "fsupgrade.sh missing priorOpenemrVersion line -> return 1" {
    local dir
    dir="$(write_fsupgrade_fixture no-prior 16 "")"
    local shipped
    shipped=$(printf '%s\n' "8.4.1")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 1 ]]
    [[ "${stderr}" == *"priorOpenemrVersion"* ]]
}

@test "empty shipped-versions arg -> return 1 with caller-contract error" {
    local dir
    dir="$(write_fsupgrade_fixture rel-850 16 "8.4.1")"
    run_fsupgrade "${dir}" ""
    [[ "${status}" -eq 1 ]]
    [[ "${stderr}" == *"shipped-versions list"* ]]
}

@test "no X.Y.Z shipped version below candidate AND candidate not shipped -> return 1" {
    # Candidate = 8.1.1; manifest has only patch versions below it
    # (no X.Y.Z), so fallback walk has nothing to pick.
    local dir
    dir="$(write_fsupgrade_fixture rel-820 12 "8.1.1")"
    local shipped
    shipped=$(printf '%s\n' "8.0.0.3" "8.2.0" "8.3.0")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 1 ]]
    [[ "${stderr}" == *"not shipped"* ]]
    [[ "${stderr}" == *"override"* ]]
}

@test "manifest contains only patch-shaped versions -> return 1 (no X.Y.Z-shaped candidates to walk)" {
    local dir
    dir="$(write_fsupgrade_fixture rel-850 16 "8.4.1")"
    local shipped
    shipped=$(printf '%s\n' "8.0.0.1" "8.0.0.2" "8.0.0.3")
    run_fsupgrade "${dir}" "${shipped}"
    [[ "${status}" -eq 1 ]]
    [[ "${stderr}" == *"X.Y.Z-shaped"* ]]
}
