#!/usr/bin/env bash
#
# Sync byte-identical files from master into a rel branch's working tree.
#
# Reads the FILES_ALL list from master's .github/byte-identical.yml
# and, for each entry, classifies the rel branch's state relative to master
# (identical / add / update / delete / both-missing) and applies the right
# operation to the current working tree. Then, after the main loop, also
# walks the rel branch's own copy of byte-identical.yml to find
# entries that are in rel's FILES_ALL but not master's -- those are paths
# master removed from the managed set (either renamed to a new path, or
# dropped entirely), and any rel-branch file still sitting at the old path
# is queued for delete (the "rename / removed-from-config" sweep).
#
# The caller (typically .github/workflows/sync-byte-identical.yml) handles
# the git push + PR open via peter-evans/create-pull-request.
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
  if [[ "${GITHUB_ACTIONS:-}" == "true" ]]; then
    echo "::error::${msg}" >&2
  else
    echo "ERROR: ${msg}" >&2
  fi
}

# expand_patterns_into (sourced below) surfaces stale-glob patterns via
# emit_warning; provide the passthrough so those warnings show up in
# the sync workflow summary. Matches the shape of emit_error above.
emit_warning() {
  local msg="$1"
  if [[ "${GITHUB_ACTIONS:-}" == "true" ]]; then
    echo "::warning::${msg}"
  else
    echo "WARNING: ${msg}"
  fi
}

# Source the shared glob-expansion helpers (expand_pattern,
# glob_to_regex, expand_patterns_into). Sourced AFTER emit_warning is
# defined -- expand_patterns_into calls it on stale-glob patterns.
# See validate-byte-identical.sh's source line for the /dev/null
# rationale (avoids requiring source-path=SCRIPTDIR in .shellcheckrc,
# which isn't byte-identical enforced and would stay master-only).
# shellcheck source=/dev/null
source "$(dirname "${BASH_SOURCE[0]}")/lib/glob-expand.sh"

# Preconditions
command -v yq >/dev/null 2>&1 || {
  emit_error "yq not found on PATH (need Mike Farah's Go-based yq)."
  exit 2
}

git rev-parse --verify master >/dev/null 2>&1 || {
  emit_error "master ref not present in the local repo (need 'git fetch origin master:master' first)."
  exit 2
}

mkdir -p "${output_dir}"

# Read the FILES_ALL set from master's config file. Even if the rel branch
# has its own copy, master is the source of truth -- we never sync FROM a
# rel branch's view. Each subprocess invocation is captured separately to
# avoid SC2312 (pipeline return values being masked by mapfile process
# substitution).
config_contents=$(git show master:.github/byte-identical.yml)
files_yq_output=$(echo "${config_contents}" | yq -r '.files[]')
mapfile -t FILES_ALL <<<"${files_yq_output}"
# When the yq output is empty, mapfile produces a single empty element;
# normalize to an empty array so the length-zero check below is honest.
if [[ ${#FILES_ALL[@]} -eq 1 && -z "${FILES_ALL[0]}" ]]; then
  FILES_ALL=()
fi

if [[ ${#FILES_ALL[@]} -eq 0 ]]; then
  emit_error "No files in master's byte-identical.yml; refusing to sync (an empty list would propagate 'delete every synced file' to every rel branch)."
  exit 1
fi

# Reject duplicates -- idempotent in practice, but a config bug worth surfacing.
dupes=$(printf '%s\n' "${FILES_ALL[@]}" | sort | uniq -d)
if [[ -n "${dupes}" ]]; then
  dupes_one_line=$(echo "${dupes}" | tr '\n' ' ')
  emit_error "Duplicate entries in byte-identical.yml files: ${dupes_one_line}"
  exit 1
fi

master_sha=$(git rev-parse master)
echo "Syncing master (${master_sha}) -> ${target_branch}"
echo "Patterns in master's FILES_ALL: ${#FILES_ALL[@]}"

# Expand master's FILES_ALL against BOTH refs. A path may exist only on
# master (add), only on rel (delete), on both (identical/update), or --
# for a stale glob pattern -- on neither (config bug, caught in the
# main loop's both-missing branch).
declare -a MASTER_PATHS=()
declare -a REL_PATHS_UNDER_MASTER_CONFIG=()
expand_patterns_into master FILES_ALL MASTER_PATHS
expand_patterns_into HEAD   FILES_ALL REL_PATHS_UNDER_MASTER_CONFIG

# Union of the two expansions -- everything master's config wants us to
# consider on this rel branch. Dedup + sort for stable iteration order.
# `grep -v '^$'` filters the empty-line printf-with-empty-array leaves.
# Split into two steps so pipe stages' exit statuses aren't masked
# (SC2312 -- printf/grep/sort are all no-fail here in practice, but
# make it explicit).
declare -a FILES_TO_CHECK=()
FILES_TO_CHECK_INPUT="$(printf '%s\n' "${MASTER_PATHS[@]}" "${REL_PATHS_UNDER_MASTER_CONFIG[@]}" | grep -v '^$' | sort -u || true)"
if [[ -n "${FILES_TO_CHECK_INPUT}" ]]; then
  mapfile -t FILES_TO_CHECK <<<"${FILES_TO_CHECK_INPUT}"
fi
unset FILES_TO_CHECK_INPUT
echo "Concrete paths after glob expansion (master + rel union): ${#FILES_TO_CHECK[@]}"

CHANGES=()
for FILE in "${FILES_TO_CHECK[@]}"; do
  master_has=n
  branch_has=n
  if git cat-file -e "master:${FILE}" 2>/dev/null; then master_has=y; fi
  if git cat-file -e "HEAD:${FILE}" 2>/dev/null; then branch_has=y; fi

  if [[ "${master_has}" == "n" ]] && [[ "${branch_has}" == "n" ]]; then
    # File is in FILES_ALL but absent from both branches -- always a config
    # bug (someone removed the file but forgot to update the list). With
    # glob expansion this should be unreachable (union of master + rel
    # expansions only includes files present on at least one side), but
    # kept as a defense in depth.
    emit_error "${FILE}: listed in byte-identical.yml but missing from both master and ${target_branch}"
    exit 1
  fi

  if [[ "${master_has}" == "y" ]] && [[ "${branch_has}" == "n" ]]; then
    echo "  + ${FILE}  (add)"
    dir=$(dirname "${FILE}")
    mkdir -p "${dir}"
    git show "master:${FILE}" > "${FILE}"
    CHANGES+=("add: ${FILE}")
    continue
  fi

  if [[ "${master_has}" == "n" ]] && [[ "${branch_has}" == "y" ]]; then
    echo "  - ${FILE}  (delete)"
    git rm -- "${FILE}" >/dev/null
    CHANGES+=("delete: ${FILE}")
    continue
  fi

  # Both present -- compare blob hashes
  master_hash=$(git rev-parse "master:${FILE}")
  branch_hash=$(git rev-parse "HEAD:${FILE}")
  if [[ "${master_hash}" == "${branch_hash}" ]]; then
    continue
  fi

  echo "  ~ ${FILE}  (update from master)"
  git show "master:${FILE}" > "${FILE}"
  CHANGES+=("update: ${FILE}")
done

# Rename / removed-from-config sweep: walk the rel branch's own
# byte-identical.yml (if it has one) and expand its patterns against
# rel HEAD to get every path rel's config says it wants to manage.
# Diff that concrete-path set against MASTER_PATHS (master's own
# expansion). Paths in rel's set but not master's are paths master
# dropped from the managed set. Two sub-cases, distinguished by whether
# master still carries the file at that path:
#
#   1. Master doesn't carry the path (git cat-file -e master:PATH fails).
#      True removal (or rename -- the new path appears in master's
#      config and was already handled as `add` by the main loop above).
#      The old path lingering on the rel branch is orphaned -- delete it.
#
#   2. Master still carries the path but dropped it from FILES_ALL.
#      Deliberate "demote to per-branch divergence" -- the file is no
#      longer byte-identity-enforced. Leave the rel-branch copy alone.
#
# Case 3 (rel doesn't have the file either) survives for literal-path
# entries -- expand_pattern echoes literals verbatim regardless of
# existence, so a literal in rel's config that rel HEAD lacks lands
# in the diff and needs a silent skip. Glob entries only emit
# existing paths, so case 3 doesn't apply to them.
if git cat-file -e "HEAD:.github/byte-identical.yml" 2>/dev/null; then
  rel_config_contents=$(git show "HEAD:.github/byte-identical.yml")
  rel_files_yq_output=$(echo "${rel_config_contents}" | yq -r '.files[]')
  mapfile -t REL_FILES_ALL <<<"${rel_files_yq_output}"
  if [[ ${#REL_FILES_ALL[@]} -eq 1 && -z "${REL_FILES_ALL[0]}" ]]; then
    REL_FILES_ALL=()
  fi

  if [[ ${#REL_FILES_ALL[@]} -gt 0 ]]; then
    # Expand rel's own patterns against rel HEAD to get concrete paths
    # rel intends to manage.
    declare -a REL_PATHS_UNDER_REL_CONFIG=()
    expand_patterns_into HEAD REL_FILES_ALL REL_PATHS_UNDER_REL_CONFIG

    # Set difference (rel-managed - master-managed). Both are already
    # sorted by expand_patterns_into.
    rel_only=$(comm -23 \
      <(printf '%s\n' "${REL_PATHS_UNDER_REL_CONFIG[@]}") \
      <(printf '%s\n' "${MASTER_PATHS[@]}"))

    if [[ -n "${rel_only}" ]]; then
      while IFS= read -r STALE; do
        [[ -z "${STALE}" ]] && continue
        if ! git cat-file -e "HEAD:${STALE}" 2>/dev/null; then
          # case 3: rel doesn't have the file either. Reachable only
          # for literal-path entries (globs would have filtered this
          # out at expansion time). Silent skip -- config-bug surfacing
          # is the canary's job, not sync's.
          continue
        fi
        if git cat-file -e "master:${STALE}" 2>/dev/null; then
          # case 2: master still carries the file, just stopped enforcing
          # byte-identity. Leave rel's copy alone.
          echo "  ${STALE}  (skip: dropped from FILES_ALL but master still carries the file -- per-branch divergence now allowed)"
          continue
        fi
        # case 1: true removal (or rename's old-path side).
        echo "  - ${STALE}  (delete: master removed this path entirely)"
        git rm -- "${STALE}" >/dev/null
        CHANGES+=("delete: ${STALE}")
      done <<< "${rel_only}"
    fi
  fi
fi

# Write structured outputs
echo "${master_sha}" > "${output_dir}/master-sha.txt"
if [[ ${#CHANGES[@]} -eq 0 ]]; then
  : > "${output_dir}/changes.txt"  # empty file
  echo "No changes needed for ${target_branch}"
else
  printf '%s\n' "${CHANGES[@]}" > "${output_dir}/changes.txt"
fi
