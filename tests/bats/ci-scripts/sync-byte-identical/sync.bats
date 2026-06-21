# BATS tests for .github/scripts/sync-byte-identical.sh
#
# Covers the five classification cases (identical / add / update / delete /
# both-missing) plus config-bug cases (empty FILES_ALL, duplicate entries)
# plus edge cases (nested paths, mixed cases in one run, the master-sha
# output contract).
#
# Each @test uses BATS's setup()/teardown() (auto-invoked) for a fresh
# synthetic git repo. See helpers.bash for the fixture machinery.

load 'helpers'

setup() {
    setup_test_repo
}

teardown() {
    teardown_test_repo
}

@test "identical files produce no changes" {
    write_on_branch master   src/foo.txt "content-A"
    write_on_branch rel-810  src/foo.txt "content-A"
    write_files_all_config src/foo.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ -f "$OUTPUT_DIR/changes.txt" ]]   # contract: file always created
    [[ ! -s "$OUTPUT_DIR/changes.txt" ]]
}

@test "add case copies master's file into the rel branch working tree" {
    write_on_branch master src/new.txt "fresh-content"
    write_files_all_config src/new.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ -f src/new.txt ]]
    [[ "$(cat src/new.txt)" == "fresh-content" ]]
    grep -qxF "add: src/new.txt" "$OUTPUT_DIR/changes.txt"
}

@test "update case overwrites rel-branch file with master's content" {
    write_on_branch master   src/conf.txt "master-version"
    write_on_branch rel-810  src/conf.txt "older-version"
    write_files_all_config src/conf.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ "$(cat src/conf.txt)" == "master-version" ]]
    grep -qxF "update: src/conf.txt" "$OUTPUT_DIR/changes.txt"
}

@test "delete case removes the file from the rel-branch working tree" {
    write_on_branch rel-810 src/obsolete.txt "old-stuff"
    write_files_all_config src/obsolete.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ ! -f src/obsolete.txt ]]
    grep -qxF "delete: src/obsolete.txt" "$OUTPUT_DIR/changes.txt"
}

@test "both-missing case fails with a clear error (config bug)" {
    write_files_all_config src/never-existed.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 1 ]]
    [[ "$output" == *"src/never-existed.txt"* ]]
    [[ "$output" == *"missing from both"* ]]
}

@test "empty FILES_ALL fails closed (would propagate 'delete everything synced')" {
    write_files_all_config   # no args -> empty list

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 1 ]]
    [[ "$output" == *"No files in master's docker-byte-identical.yml"* ]]
}

@test "duplicate FILES_ALL entries fail closed" {
    write_on_branch master src/foo.txt "x"
    write_files_all_raw 'files:
- src/foo.txt
- src/foo.txt
'

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 1 ]]
    [[ "$output" == *"Duplicate entries"* ]]
}

@test "add case creates missing parent directories for nested paths" {
    write_on_branch master docker/library/deep/nested.txt "nested-content"
    write_files_all_config docker/library/deep/nested.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ -f docker/library/deep/nested.txt ]]
    [[ "$(cat docker/library/deep/nested.txt)" == "nested-content" ]]
    grep -qxF "add: docker/library/deep/nested.txt" "$OUTPUT_DIR/changes.txt"
}

@test "mixed cases (identical + update + add + delete) all applied correctly in one run" {
    # Identical
    write_on_branch master  src/same.txt "same"
    write_on_branch rel-810 src/same.txt "same"
    # Update
    write_on_branch master  src/changed.txt "new"
    write_on_branch rel-810 src/changed.txt "old"
    # Add
    write_on_branch master  src/added.txt "added-on-master"
    # Delete
    write_on_branch rel-810 src/removed.txt "to-be-removed"
    write_files_all_config src/same.txt src/changed.txt src/added.txt src/removed.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ "$(cat src/same.txt)" == "same" ]]                  # unchanged
    [[ "$(cat src/changed.txt)" == "new" ]]                # updated
    [[ "$(cat src/added.txt)" == "added-on-master" ]]      # added
    [[ ! -f src/removed.txt ]]                             # deleted
    [[ -f "$OUTPUT_DIR/changes.txt" ]]   # contract: file always created
    grep -qxF "update: src/changed.txt" "$OUTPUT_DIR/changes.txt"
    grep -qxF "add: src/added.txt" "$OUTPUT_DIR/changes.txt"
    grep -qxF "delete: src/removed.txt" "$OUTPUT_DIR/changes.txt"
    # The identical entry should NOT appear in changes.txt
    ! grep -qF "src/same.txt" "$OUTPUT_DIR/changes.txt"
}

@test "master-sha.txt records the master SHA the sync was sourced from" {
    write_on_branch master src/foo.txt "x"
    write_files_all_config src/foo.txt
    local expected_sha
    expected_sha=$(git rev-parse master)

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ "$(cat "$OUTPUT_DIR/master-sha.txt")" == "$expected_sha" ]]
}

@test "rename case: master drops old path + adds new path -> sync deletes old + adds new" {
    # Initial state: both branches carry src/old.txt at the same content
    write_on_branch master  src/old.txt "shared-content"
    write_on_branch rel-810 src/old.txt "shared-content"
    # rel-810's FILES_ALL still lists the old path (set up before master's rename).
    write_files_all_config_on_branch rel-810 src/old.txt
    # Now master renames: deletes old path, adds new path, updates its FILES_ALL.
    delete_on_branch master src/old.txt
    write_on_branch master  src/new.txt "shared-content"
    write_files_all_config src/new.txt   # this targets master (default)

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ -f src/new.txt ]]                                          # added
    [[ "$(cat src/new.txt)" == "shared-content" ]]
    [[ ! -f src/old.txt ]]                                        # deleted
    grep -qxF "add: src/new.txt" "$OUTPUT_DIR/changes.txt"
    grep -qxF "delete: src/old.txt" "$OUTPUT_DIR/changes.txt"
}

@test "removed-from-config: master drops a path entirely -> sync deletes from rel branch" {
    write_on_branch master  src/keepme.txt "keep"
    write_on_branch rel-810 src/keepme.txt "keep"
    write_on_branch rel-810 src/byebye.txt "obsolete"
    # rel-810's config still has both entries (delivered before master's removal).
    write_files_all_config_on_branch rel-810 src/keepme.txt src/byebye.txt
    # master removes src/byebye.txt from both tree and config.
    write_files_all_config src/keepme.txt   # master's FILES_ALL no longer lists byebye.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ -f src/keepme.txt ]]                                       # retained
    [[ ! -f src/byebye.txt ]]                                     # deleted by rel-only sweep
    grep -qxF "delete: src/byebye.txt" "$OUTPUT_DIR/changes.txt"
    ! grep -qF "src/keepme.txt" "$OUTPUT_DIR/changes.txt"          # no change recorded for the kept file
}

@test "rel-only sweep handles rel branch with no FILES_ALL config gracefully (skip the sweep)" {
    # rel-810 has no .github/docker-byte-identical.yml (pre-PR-D state).
    # No way to know what rel had "in config" -- script must not crash.
    write_on_branch master src/foo.txt "x"
    write_files_all_config src/foo.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ -f src/foo.txt ]]
    grep -qxF "add: src/foo.txt" "$OUTPUT_DIR/changes.txt"
}

@test "rel-only sweep: path in rel's config but absent from rel tree -> no-op, no error" {
    # rel-810's FILES_ALL claims src/phantom.txt but neither branch's tree carries it.
    # Main loop won't see it (not in master's FILES_ALL). Rel-only sweep notices
    # it's missing from rel HEAD too, so nothing to delete -- silent skip.
    write_on_branch master src/real.txt "real-content"
    write_files_all_config_on_branch rel-810 src/phantom.txt
    write_files_all_config src/real.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ -f src/real.txt ]]                                         # added by main loop
    grep -qxF "add: src/real.txt" "$OUTPUT_DIR/changes.txt"
    ! grep -qF "phantom" "$OUTPUT_DIR/changes.txt"                # not recorded
}
