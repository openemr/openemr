# BATS tests for .github/scripts/validate-byte-identical.sh
#
# Covers the master-context and rel-context branches, drift / no-drift /
# 404 / missing-local outcomes, the warning-vs-error split based on
# event type, plus config-bug paths (empty / dupes / missing master
# files / missing config / missing context env vars).

load 'helpers'

setup() {
    setup_test_dir
    write_release_targets rel-810 rel-800
}

teardown() {
    teardown_test_dir
}

# -----------------------------------------------------------------------
# Master context -- schedule (drift -> error)
# -----------------------------------------------------------------------

@test "master schedule: identical across all rel branches -> success" {
    write_files_all_config foo.txt
    write_local_file       foo.txt        "v1"
    write_remote_file rel-810 foo.txt     "v1"
    write_remote_file rel-800 foo.txt     "v1"

    set_schedule_context master
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 0 ]]
    [[ "$output" == *"matches master"* ]]
}

@test "master schedule: drift on one rel branch -> error, fail" {
    write_files_all_config foo.txt
    write_local_file       foo.txt        "v1"
    write_remote_file rel-810 foo.txt     "v1"
    write_remote_file rel-800 foo.txt     "v2-stale"

    set_schedule_context master
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 1 ]]
    [[ "$output" == *"ERROR foo.txt: Drift: rel-800:foo.txt"* ]]
    [[ "$output" == *"rel-810:foo.txt matches master"* ]]
}

@test "master schedule: file missing on rel branch (404) -> error, fail" {
    write_files_all_config foo.txt
    write_local_file       foo.txt        "v1"
    write_remote_file rel-810 foo.txt     "v1"
    # rel-800 deliberately not seeded -> simulates 404 on raw URL.

    set_schedule_context master
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 1 ]]
    [[ "$output" == *"rel-800:foo.txt not present on remote (HTTP 404)"* ]]
}

# -----------------------------------------------------------------------
# Master context -- pull_request (drift -> warning, no fail)
# -----------------------------------------------------------------------

@test "master PR: drift on rel branch -> warning, no fail" {
    write_files_all_config foo.txt
    write_local_file       foo.txt        "v2-master-pr"
    write_remote_file rel-810 foo.txt     "v1-old"
    write_remote_file rel-800 foo.txt     "v1-old"

    set_pr_context master
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 0 ]]
    [[ "$output" == *"WARNING foo.txt: rel-810:foo.txt will differ from master"* ]]
}

@test "master PR: file missing on rel branch (404) -> warning, no fail" {
    write_files_all_config new-file.txt
    write_local_file       new-file.txt   "freshly-added"
    # No rel-* fixtures -> both 404, but on a master PR that's the
    # expected state pre-auto-sync.

    set_pr_context master
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 0 ]]
    [[ "$output" == *"WARNING new-file.txt: rel-810:new-file.txt not present (HTTP 404)"* ]]
    [[ "$output" == *"WARNING new-file.txt: rel-800:new-file.txt not present (HTTP 404)"* ]]
}

# -----------------------------------------------------------------------
# Master context -- config bugs
# -----------------------------------------------------------------------

@test "master schedule: FILES_ALL entry missing from master checkout -> fail" {
    write_files_all_config foo.txt missing-locally.txt
    write_local_file       foo.txt        "v1"
    # missing-locally.txt deliberately not written.

    set_schedule_context master
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 1 ]]
    [[ "$output" == *"missing from master checkout: missing-locally.txt"* ]]
}

@test "empty FILES_ALL fails closed (would silently disable the canary)" {
    write_files_all_config   # no args -> empty list

    set_schedule_context master
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 1 ]]
    [[ "$output" == *"refusing to skip drift validation"* ]]
}

@test "duplicate FILES_ALL entries fail closed (config bug)" {
    write_files_all_raw 'files:
- foo.txt
- foo.txt
'
    write_local_file foo.txt "v1"
    write_remote_file rel-810 foo.txt "v1"
    write_remote_file rel-800 foo.txt "v1"

    set_schedule_context master
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 1 ]]
    [[ "$output" == *"Duplicate entries"* ]]
    [[ "$output" == *"foo.txt"* ]]
}

# -----------------------------------------------------------------------
# Rel branch context
# -----------------------------------------------------------------------

@test "rel-810 PR: matches master -> success" {
    write_files_all_config foo.txt
    write_local_file       foo.txt   "in-sync"
    write_remote_file master foo.txt "in-sync"

    set_pr_context rel-810
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 0 ]]
    [[ "$output" == *"foo.txt matches master"* ]]
}

@test "rel-810 PR: drift vs master -> error, fail" {
    write_files_all_config foo.txt
    write_local_file       foo.txt   "rel-edit"
    write_remote_file master foo.txt "master-version"

    set_pr_context rel-810
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 1 ]]
    [[ "$output" == *"ERROR foo.txt: Drift: rel-810:foo.txt differs from master"* ]]
}

@test "rel-810 PR: destructive delete of a FILES_ALL file -> error, fail" {
    write_files_all_config foo.txt
    # foo.txt deliberately not written to local checkout (= deleted by PR)
    write_remote_file master foo.txt "still-on-master"

    set_pr_context rel-810
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 1 ]]
    [[ "$output" == *"foo.txt listed in FILES_ALL but missing from rel-810"* ]]
    [[ "$output" == *"deleted a protected file"* ]]
}

@test "rel-810 PR: file in FILES_ALL but missing from master (404) -> warning, no fail" {
    write_files_all_config foo.txt
    write_local_file foo.txt "exists-locally"
    # master fixture absent -> 404 on master raw URL. Surfaces drift-vs-
    # master-side, but doesn't fail the rel PR (it's a master-config
    # problem to fix).

    set_pr_context rel-810
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 0 ]]
    [[ "$output" == *"WARNING: master:foo.txt not present (HTTP 404)"* ]]
}

@test "rel-810 dispatch: drift vs master -> error, fail (same path as PR)" {
    write_files_all_config foo.txt
    write_local_file       foo.txt   "rel-edit"
    write_remote_file master foo.txt "master-version"

    set_dispatch_context rel-810
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 1 ]]
    [[ "$output" == *"ERROR foo.txt: Drift: rel-810:foo.txt"* ]]
}

# -----------------------------------------------------------------------
# Mixed -- multiple files, mixed outcomes in a single run
# -----------------------------------------------------------------------

@test "master schedule: mix of match + drift + 404 across two rel branches" {
    write_files_all_config a.txt b.txt c.txt

    write_local_file a.txt "v1"
    write_local_file b.txt "v1"
    write_local_file c.txt "v1"

    # rel-810: a matches, b drifts, c 404
    write_remote_file rel-810 a.txt "v1"
    write_remote_file rel-810 b.txt "v2-drift"
    # c.txt deliberately missing from rel-810

    # rel-800: all match
    write_remote_file rel-800 a.txt "v1"
    write_remote_file rel-800 b.txt "v1"
    write_remote_file rel-800 c.txt "v1"

    set_schedule_context master
    run bash "$VALIDATE_BYTE_IDENTICAL_SCRIPT"

    [[ $status -eq 1 ]]
    [[ "$output" == *"rel-810:a.txt matches master"* ]]
    [[ "$output" == *"ERROR b.txt: Drift: rel-810:b.txt"* ]]
    [[ "$output" == *"rel-810:c.txt not present on remote (HTTP 404)"* ]]
    [[ "$output" == *"rel-800:a.txt matches master"* ]]
    [[ "$output" == *"rel-800:b.txt matches master"* ]]
    [[ "$output" == *"rel-800:c.txt matches master"* ]]
}
