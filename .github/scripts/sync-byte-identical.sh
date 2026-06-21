#!/usr/bin/env bash
#
# Sync byte-identical files from master into a rel branch's working tree.
#
# Reads the FILES_ALL list from master's .github/docker-byte-identical.yml
# and, for each entry, classifies the rel branch's state relative to master
# (identical / add / update / delete / both-missing) and applies the right
# operation to the current working tree. The caller (typically
# .github/workflows/sync-byte-identical.yml) handles the git push + PR
# open via peter-evans/create-pull-request.
#
# Inputs
#   $1                  rel branch name (e.g. rel-810). Used in log output.
#   OUTPUT_DIR  (env)   directory to write structured output files into.
#                       Defaults to a process-local temp dir if unset.
#   GITHUB_ACTIONS env  if set, ::error:: annotations get emitted alongside
#                       regular error output (so the workflow run summary
#                       shows them). When unset, plain stderr is used --
#                       useful for the test suite.
#
# Preconditions
#   - cwd is a git checkout of the rel branch (or any branch -- the script
#     reads HEAD: for the "rel branch" side and master: for the "master"
#     side, both via git refs)
#   - master is a fetched local ref (the workflow does
#     `git fetch origin master:master`; tests can set it up directly)
#   - yq (Mike Farah's, the Go binary, supports `-r` flag) is on PATH
#
# Outputs (in $OUTPUT_DIR)
#   changes.txt         one line per change in `add: <path>` / `update: <path>`
#                       / `delete: <path>` format. Empty file if no changes.
#                       Suitable for inclusion in a PR body's code block.
#   master-sha.txt      the master SHA that sourced this sync (40-hex string).
#                       Used by the workflow's commit message + PR body so
#                       reviewers can trace what was synced from where.
#
# Side effects
#   Modifies the working tree -- adds, updates, and removes files per the
#   classification. Does NOT commit, push, or open PRs. Does NOT touch
#   any file outside the FILES_ALL set.
#
# Exit codes
#   0   normal completion (with or without changes)
#   1   config-bug error: empty FILES_ALL, duplicate entries, or a file
#       in FILES_ALL that's missing from both master and the rel branch
#   2   precondition error: missing yq, missing master ref, etc.

set -euo pipefail

target_branch="${1:?usage: sync-byte-identical.sh <rel-branch>}"
output_dir="${OUTPUT_DIR:-$(mktemp -d)}"

emit_error() {
  local msg="$1"
  if [ "${GITHUB_ACTIONS:-}" = "true" ]; then
    echo "::error::${msg}" >&2
  else
    echo "ERROR: ${msg}" >&2
  fi
}

# Preconditions
command -v yq >/dev/null 2>&1 || {
  emit_error "yq not found on PATH (need Mike Farah's Go-based yq)."
  exit 2
}

git rev-parse --verify master >/dev/null 2>&1 || {
  emit_error "master ref not present in the local repo (need 'git fetch origin master:master' first)."
  exit 2
}

mkdir -p "$output_dir"

# Read the FILES_ALL set from master's config file. Even if the rel branch
# has its own copy, master is the source of truth -- we never sync FROM a
# rel branch's view.
mapfile -t FILES_ALL < <(git show master:.github/docker-byte-identical.yml | yq -r '.files[]')

if [ ${#FILES_ALL[@]} -eq 0 ]; then
  emit_error "No files in master's docker-byte-identical.yml; refusing to sync (an empty list would propagate 'delete every synced file' to every rel branch)."
  exit 1
fi

# Reject duplicates -- idempotent in practice, but a config bug worth surfacing.
DUPES=$(printf '%s\n' "${FILES_ALL[@]}" | sort | uniq -d)
if [ -n "$DUPES" ]; then
  emit_error "Duplicate entries in docker-byte-identical.yml files: $(echo "$DUPES" | tr '\n' ' ')"
  exit 1
fi

master_sha=$(git rev-parse master)
echo "Syncing master ($master_sha) -> ${target_branch}"
echo "Files in FILES_ALL: ${#FILES_ALL[@]}"

CHANGES=()
for FILE in "${FILES_ALL[@]}"; do
  master_has=$(git cat-file -e "master:$FILE" 2>/dev/null && echo y || echo n)
  branch_has=$(git cat-file -e "HEAD:$FILE" 2>/dev/null && echo y || echo n)

  if [ "$master_has" = "n" ] && [ "$branch_has" = "n" ]; then
    # File is in FILES_ALL but absent from both branches -- always a config
    # bug (someone removed the file but forgot to update the list).
    emit_error "${FILE}: listed in docker-byte-identical.yml but missing from both master and ${target_branch}"
    exit 1
  fi

  if [ "$master_has" = "y" ] && [ "$branch_has" = "n" ]; then
    echo "  + ${FILE}  (add)"
    mkdir -p "$(dirname "$FILE")"
    git show "master:$FILE" > "$FILE"
    CHANGES+=("add: $FILE")
    continue
  fi

  if [ "$master_has" = "n" ] && [ "$branch_has" = "y" ]; then
    echo "  - ${FILE}  (delete)"
    git rm "$FILE" >/dev/null
    CHANGES+=("delete: $FILE")
    continue
  fi

  # Both present -- compare blob hashes
  master_hash=$(git rev-parse "master:$FILE")
  branch_hash=$(git rev-parse "HEAD:$FILE")
  if [ "$master_hash" = "$branch_hash" ]; then
    continue
  fi

  echo "  ~ ${FILE}  (update from master)"
  git show "master:$FILE" > "$FILE"
  CHANGES+=("update: $FILE")
done

# Write structured outputs
echo "${master_sha}" > "${output_dir}/master-sha.txt"
if [ ${#CHANGES[@]} -eq 0 ]; then
  : > "${output_dir}/changes.txt"  # empty file
  echo "No changes needed for ${target_branch}"
else
  printf '%s\n' "${CHANGES[@]}" > "${output_dir}/changes.txt"
fi
