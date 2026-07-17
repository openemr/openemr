# BATS tests for .github/scripts/list-manifest-paths.sh
#
# The script is the workflow-side entry point for byte-identical
# manifest parsing -- shares the same read_manifest_entries +
# filter_by_branch logic as the sync script via lib/glob-expand.sh.
# Pre-#12924, sync-byte-identical.yml had its own inline yq expression
# that duplicated (and got wrong twice: #12916 and #12920) the same
# filter. Coverage here catches the class of drift-between-workflow-
# and-script bugs that duplication invites.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

@test "usage: missing manifest arg exits 2" {
    run bash "$LIST_MANIFEST_PATHS_SCRIPT"
    [[ $status -eq 2 ]]
    [[ "$output" == *"manifest-file"* || "$output" == *"usage"* ]]
}

@test "usage: missing target branch arg exits 2" {
    write_manifest 'files:
- foo.txt
'
    run bash "$LIST_MANIFEST_PATHS_SCRIPT" "$MANIFEST"
    [[ $status -eq 2 ]]
    [[ "$output" == *"target-branch"* || "$output" == *"usage"* ]]
}

@test "precondition: missing manifest file exits 2 with clear message" {
    run bash "$LIST_MANIFEST_PATHS_SCRIPT" /does/not/exist.yml rel-820
    [[ $status -eq 2 ]]
    [[ "$output" == *"manifest not found"* ]]
}

@test "string-only manifest: all paths printed for any target" {
    write_manifest 'files:
- foo.txt
- bar/baz.yml
- nested/dir/file.sh
'
    run bash "$LIST_MANIFEST_PATHS_SCRIPT" "$MANIFEST" rel-820
    [[ $status -eq 0 ]]
    [[ "${lines[0]}" == "foo.txt" ]]
    [[ "${lines[1]}" == "bar/baz.yml" ]]
    [[ "${lines[2]}" == "nested/dir/file.sh" ]]
    [[ ${#lines[@]} -eq 3 ]]
}

@test "object-form manifest: entry excluded from target is filtered out" {
    write_manifest 'files:
- shared.txt
- path: only-for-820.txt
  exclude-branches:
  - rel-800
  - rel-704
'
    run bash "$LIST_MANIFEST_PATHS_SCRIPT" "$MANIFEST" rel-800
    [[ $status -eq 0 ]]
    [[ "${lines[0]}" == "shared.txt" ]]
    [[ ${#lines[@]} -eq 1 ]]
}

@test "object-form manifest: entry included for non-excluded target still prints" {
    write_manifest 'files:
- shared.txt
- path: only-for-820.txt
  exclude-branches:
  - rel-800
  - rel-704
'
    run bash "$LIST_MANIFEST_PATHS_SCRIPT" "$MANIFEST" rel-820
    [[ $status -eq 0 ]]
    [[ "${lines[0]}" == "shared.txt" ]]
    [[ "${lines[1]}" == "only-for-820.txt" ]]
    [[ ${#lines[@]} -eq 2 ]]
}

@test "mixed string + object entries: both shapes coexist correctly" {
    write_manifest 'files:
- string-a.txt
- path: object-a.txt
- string-b.txt
- path: object-b.txt
  exclude-branches:
  - rel-704
- string-c.txt
'
    # rel-820: all 5 entries
    run bash "$LIST_MANIFEST_PATHS_SCRIPT" "$MANIFEST" rel-820
    [[ $status -eq 0 ]]
    [[ ${#lines[@]} -eq 5 ]]

    # rel-704: 4 entries (object-b.txt filtered)
    run bash "$LIST_MANIFEST_PATHS_SCRIPT" "$MANIFEST" rel-704
    [[ $status -eq 0 ]]
    [[ ${#lines[@]} -eq 4 ]]
    ! printf '%s\n' "${lines[@]}" | grep -qxF "object-b.txt"
}

@test "empty manifest: exit 0, no output" {
    write_manifest 'files: []
'
    run bash "$LIST_MANIFEST_PATHS_SCRIPT" "$MANIFEST" rel-820
    [[ $status -eq 0 ]]
    [[ -z "$output" ]]
}

@test "all entries excluded for target: exit 0, no output" {
    write_manifest 'files:
- path: excluded-a.txt
  exclude-branches: [rel-800]
- path: excluded-b.txt
  exclude-branches: [rel-800]
'
    run bash "$LIST_MANIFEST_PATHS_SCRIPT" "$MANIFEST" rel-800
    [[ $status -eq 0 ]]
    [[ -z "$output" ]]
}

@test "target branch not in any exclude list: all entries printed" {
    write_manifest 'files:
- path: entry-1.txt
  exclude-branches: [rel-800]
- path: entry-2.txt
  exclude-branches: [rel-704]
- entry-3.txt
'
    # rel-820 isn't excluded from anything -- all 3 print.
    run bash "$LIST_MANIFEST_PATHS_SCRIPT" "$MANIFEST" rel-820
    [[ $status -eq 0 ]]
    [[ ${#lines[@]} -eq 3 ]]
}
