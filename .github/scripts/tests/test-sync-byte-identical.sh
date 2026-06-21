#!/usr/bin/env bash
#
# Tests for .github/scripts/sync-byte-identical.sh
#
# Covers the five classification cases (identical / add / update / delete /
# both-missing) plus config-bug cases (empty FILES_ALL, duplicate entries).
#
# Each test_* function is auto-discovered and run by run-tests.sh.
# Helpers in helpers.sh do the boilerplate.

set -euo pipefail

# shellcheck source=./helpers.sh
source "$(dirname "${BASH_SOURCE[0]}")/helpers.sh"

# Identical: file exists on both master and rel branch with same content.
# Expected: no changes.
test_identical_files_produce_no_changes() {
  setup_test_repo
  write_on_branch master   src/foo.txt "content-A"
  write_on_branch rel-810  src/foo.txt "content-A"
  write_files_all_config src/foo.txt

  git checkout -q rel-810
  run_sync rel-810

  assert_exit_code 0
  assert_no_changes
  teardown_test_repo
}

# Add: file on master, missing on rel branch.
# Expected: file is added to working tree with master's content.
test_add_case_copies_master_file() {
  setup_test_repo
  write_on_branch master src/new.txt "fresh-content"
  write_files_all_config src/new.txt

  git checkout -q rel-810
  run_sync rel-810

  assert_exit_code 0
  assert_file_exists src/new.txt
  assert_file_content src/new.txt "fresh-content"
  assert_changes_contains "add: src/new.txt"
  teardown_test_repo
}

# Update: file on both, content differs.
# Expected: rel-branch copy is overwritten with master's content.
test_update_case_overwrites_with_master_content() {
  setup_test_repo
  write_on_branch master   src/conf.txt "master-version"
  write_on_branch rel-810  src/conf.txt "older-version"
  write_files_all_config src/conf.txt

  git checkout -q rel-810
  run_sync rel-810

  assert_exit_code 0
  assert_file_content src/conf.txt "master-version"
  assert_changes_contains "update: src/conf.txt"
  teardown_test_repo
}

# Delete: file missing from master, present on rel branch.
# Expected: file removed from rel-branch working tree.
test_delete_case_removes_from_rel_branch() {
  setup_test_repo
  write_on_branch rel-810  src/obsolete.txt "old-stuff"
  write_files_all_config src/obsolete.txt

  git checkout -q rel-810
  run_sync rel-810

  assert_exit_code 0
  assert_file_missing src/obsolete.txt
  assert_changes_contains "delete: src/obsolete.txt"
  teardown_test_repo
}

# Both-missing: file in FILES_ALL but exists on neither master nor rel branch.
# Expected: exit 1 with a clear error message (always a config bug).
test_both_missing_fails_with_clear_error() {
  setup_test_repo
  write_files_all_config src/never-existed.txt

  git checkout -q rel-810
  run_sync rel-810

  assert_exit_code 1
  assert_stderr_contains "src/never-existed.txt"
  assert_stderr_contains "missing from both"
  teardown_test_repo
}

# Empty FILES_ALL: config file's files: list is empty.
# Expected: exit 1; would otherwise propagate "delete everything synced".
test_empty_files_all_fails() {
  setup_test_repo
  write_files_all_config  # no args -> empty list

  git checkout -q rel-810
  run_sync rel-810

  assert_exit_code 1
  assert_stderr_contains "No files in master's docker-byte-identical.yml"
  teardown_test_repo
}

# Duplicate entries in FILES_ALL.
# Expected: exit 1; idempotent in practice but indicates a bug.
test_duplicate_files_all_entries_fail() {
  setup_test_repo
  write_on_branch master src/foo.txt "x"
  write_files_all_raw 'files:
- src/foo.txt
- src/foo.txt
'

  git checkout -q rel-810
  run_sync rel-810

  assert_exit_code 1
  assert_stderr_contains "Duplicate entries"
  teardown_test_repo
}

# Nested path: file at deeper directory level (e.g. docker/library/something).
# Expected: parent dirs created in working tree when needed.
test_nested_path_add_creates_parent_dirs() {
  setup_test_repo
  write_on_branch master docker/library/deep/nested.txt "nested-content"
  write_files_all_config docker/library/deep/nested.txt

  git checkout -q rel-810
  run_sync rel-810

  assert_exit_code 0
  assert_file_exists docker/library/deep/nested.txt
  assert_file_content docker/library/deep/nested.txt "nested-content"
  assert_changes_contains "add: docker/library/deep/nested.txt"
  teardown_test_repo
}

# Mixed cases in a single run: add + update + delete + identical all together.
# Exercises the loop's correctness when multiple cases coexist.
test_mixed_cases_all_applied_correctly() {
  setup_test_repo

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
  run_sync rel-810

  assert_exit_code 0
  assert_file_content src/same.txt "same"           # unchanged
  assert_file_content src/changed.txt "new"         # updated
  assert_file_content src/added.txt "added-on-master"  # added
  assert_file_missing src/removed.txt               # deleted
  assert_changes_contains "update: src/changed.txt"
  assert_changes_contains "add: src/added.txt"
  assert_changes_contains "delete: src/removed.txt"
  # Identical entry should NOT appear in changes.txt
  if grep -qxF "identical: src/same.txt" "$OUTPUT_DIR/changes.txt" || \
     grep -qxF "update: src/same.txt" "$OUTPUT_DIR/changes.txt"; then
    echo "FAIL: src/same.txt should not be in changes.txt (it was identical)"
    return 1
  fi
  teardown_test_repo
}

# master-sha.txt output: contains the SHA the sync was sourced from.
test_master_sha_output_recorded() {
  setup_test_repo
  write_on_branch master src/foo.txt "x"
  write_files_all_config src/foo.txt

  local expected_sha
  expected_sha=$(git rev-parse master)

  git checkout -q rel-810
  run_sync rel-810

  assert_exit_code 0
  assert_file_content "$OUTPUT_DIR/master-sha.txt" "$expected_sha"
  teardown_test_repo
}
