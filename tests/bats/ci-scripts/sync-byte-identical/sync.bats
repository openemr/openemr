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
    [[ "$output" == *"No files in master's byte-identical.yml"* ]]
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
    # rel-810 has no .github/byte-identical.yml (pre-PR-D state).
    # No way to know what rel had "in config" -- script must not crash.
    write_on_branch master src/foo.txt "x"
    write_files_all_config src/foo.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ -f src/foo.txt ]]
    grep -qxF "add: src/foo.txt" "$OUTPUT_DIR/changes.txt"
}

@test "demote case: master keeps file but drops from FILES_ALL -> sync leaves rel alone (per-branch divergence now allowed)" {
    # File exists on both branches AND on rel's FILES_ALL, but master
    # dropped the entry from its FILES_ALL while keeping the file in its
    # tree. That's a deliberate "demote to per-branch divergence" --
    # the sweep must NOT delete it from rel.
    write_on_branch master  src/demoted.txt "master-content"
    write_on_branch rel-810 src/demoted.txt "rel-existing-content"
    write_files_all_config_on_branch rel-810 src/demoted.txt
    # master's FILES_ALL no longer lists demoted.txt (but master tree still has it).
    write_files_all_config   # empty list intentionally? No -- need a non-empty list to avoid the fail-closed branch
    # Add a placeholder file so master's FILES_ALL has at least one entry
    # that triggers a no-op (avoids the "refusing to sync empty list" fail).
    write_on_branch master  src/keepme.txt "shared"
    write_on_branch rel-810 src/keepme.txt "shared"
    write_files_all_config src/keepme.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ -f src/demoted.txt ]]                                       # NOT deleted
    [[ "$(cat src/demoted.txt)" == "rel-existing-content" ]]       # rel's version preserved
    [[ "$output" == *"skip: dropped from FILES_ALL but master still carries the file"* ]]
    ! grep -qF "demoted.txt" "$OUTPUT_DIR/changes.txt"             # no change recorded
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

# --- glob-pattern support (PR 2 of the changelog-surface migration slice) ---
#
# The manifest can now list glob patterns like `tools/release/**`.
# Expansion happens at script-run time via `git ls-tree`, so master and
# rel branches see whichever concrete paths they actually carry.

@test "glob: pattern matches multiple files, all identical -> no changes" {
    write_on_branch master   src/a.txt "content-A"
    write_on_branch master   src/b.txt "content-B"
    write_on_branch master   src/c.txt "content-C"
    write_on_branch rel-810  src/a.txt "content-A"
    write_on_branch rel-810  src/b.txt "content-B"
    write_on_branch rel-810  src/c.txt "content-C"
    write_files_all_config 'src/**'

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ ! -s "$OUTPUT_DIR/changes.txt" ]]
}

@test "glob: pattern expands to master-only files -> all added to rel" {
    write_on_branch master src/a.txt "content-A"
    write_on_branch master src/b.txt "content-B"
    write_files_all_config 'src/**'

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ -f src/a.txt ]]
    [[ -f src/b.txt ]]
    grep -qxF "add: src/a.txt" "$OUTPUT_DIR/changes.txt"
    grep -qxF "add: src/b.txt" "$OUTPUT_DIR/changes.txt"
}

@test "glob: pattern-matched file on rel but not master -> delete" {
    write_on_branch master  src/keeper.txt "keep-content"
    write_on_branch rel-810 src/keeper.txt "keep-content"
    write_on_branch rel-810 src/orphan.txt "orphan-on-rel-only"
    write_files_all_config 'src/**'

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ ! -f src/orphan.txt ]]                              # deleted
    [[ -f src/keeper.txt ]]                                # untouched (identical)
    grep -qxF "delete: src/orphan.txt" "$OUTPUT_DIR/changes.txt"
    ! grep -qF "keeper" "$OUTPUT_DIR/changes.txt"
}

@test "glob: mixed add + update + delete under one pattern all applied in one run" {
    write_on_branch master  src/added.txt   "new"
    write_on_branch master  src/changed.txt "master-version"
    write_on_branch rel-810 src/changed.txt "older-version"
    write_on_branch rel-810 src/removed.txt "to-remove"
    write_files_all_config 'src/**'

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ -f src/added.txt ]]
    [[ "$(cat src/changed.txt)" == "master-version" ]]
    [[ ! -f src/removed.txt ]]
    grep -qxF "add: src/added.txt" "$OUTPUT_DIR/changes.txt"
    grep -qxF "update: src/changed.txt" "$OUTPUT_DIR/changes.txt"
    grep -qxF "delete: src/removed.txt" "$OUTPUT_DIR/changes.txt"
}

@test "glob: pattern matches nothing on either side -> no error, no changes" {
    write_on_branch master  src/other.txt "other"
    write_on_branch rel-810 src/other.txt "other"
    write_files_all_config 'nonexistent/**' src/other.txt

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    [[ $status -eq 0 ]]
    [[ ! -s "$OUTPUT_DIR/changes.txt" ]]
}

@test "glob: mixed literal + glob entries expand and dedupe cleanly" {
    write_on_branch master  src/lit.txt  "literal-content"
    write_on_branch master  src/glob.txt "glob-content"
    write_files_all_config src/lit.txt 'src/**'

    git checkout -q rel-810
    OUTPUT_DIR="$OUTPUT_DIR" run bash "$SYNC_BYTE_IDENTICAL_SCRIPT" rel-810

    # src/lit.txt appears from both the literal entry AND the glob
    # expansion; the union+sort+dedupe upstream keeps the main loop
    # from processing it twice.
    [[ $status -eq 0 ]]
    [[ -f src/lit.txt ]]
    [[ -f src/glob.txt ]]
    # exactly one "add" line each
    [[ $(grep -c "^add: src/lit.txt$" "$OUTPUT_DIR/changes.txt") -eq 1 ]]
    [[ $(grep -c "^add: src/glob.txt$" "$OUTPUT_DIR/changes.txt") -eq 1 ]]
}
