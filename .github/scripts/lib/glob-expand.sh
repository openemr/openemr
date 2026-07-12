#!/usr/bin/env bash
#
# Shared glob-expansion helpers for the byte-identical scripts.
#
# Sourced by both .github/scripts/validate-byte-identical.sh and
# .github/scripts/sync-byte-identical.sh. Not executable on its own --
# these are function definitions consumed by the caller.
#
# Callers are expected to have already defined an `emit_warning`
# function (both scripts do) before sourcing this file, so
# expand_patterns_into can surface stale-manifest warnings for globs
# that expanded to zero files.

# Expand one FILES_ALL entry against a git ref into zero-or-more
# concrete paths. Non-glob entries (no *, ?, or [ metachars) pass
# through verbatim -- callers handle existence checks in their main
# loops. Glob entries resolve against `git ls-tree -r`:
#
#   dir/**            -> `git ls-tree -r --name-only <ref> -- dir/`
#                        (fast path for whole-subtree matches)
#   any other glob    -> `git ls-tree -r --name-only <ref>` piped
#                        through `grep -E` with a regex synthesized
#                        from the glob (see glob_to_regex below)
#
# `git ls-tree`'s :(glob) pathspec magic isn't supported (unlike
# `git ls-files`), which forces the pipe-through-grep fallback for
# arbitrary globs.
expand_pattern() {
  local ref="${1}" pattern="${2}"
  case "${pattern}" in
    *'*'* | *'?'* | *'['*)
      # Fast path for the common `<dir>/**` shape
      if [[ "${pattern}" == *'/**' ]] && [[ "${pattern%/**}" != *'*'* ]] \
        && [[ "${pattern%/**}" != *'?'* ]] && [[ "${pattern%/**}" != *'['* ]]; then
        local dir="${pattern%/**}"
        git ls-tree -r --name-only "${ref}" -- "${dir}/" 2>/dev/null || true
      else
        # Generic glob: full ls-tree, filter via regex. Split the
        # pipeline so the ls-tree exit status isn't masked (SC2312).
        local regex tree
        regex="$(glob_to_regex "${pattern}")"
        tree="$(git ls-tree -r --name-only "${ref}" 2>/dev/null || true)"
        echo "${tree}" | grep -E "${regex}" || true
      fi
      ;;
    *)
      echo "${pattern}"
      ;;
  esac
}

# Convert a shell-glob pattern to an anchored ERE regex. Semantics:
#   **  -> `.*`               (matches across path separators)
#   *   -> `[^/]*`            (matches within one path component)
#   ?   -> `[^/]`             (single non-slash char)
#   [..] preserved as-is (character class)
# Regex metacharacters other than * ? [ are escaped. Result is wrapped
# in ^...$ so `grep -E` gives whole-path matches only.
glob_to_regex() {
  local p="${1}"
  # Placeholder swap so `**` is treated distinctly from `*` before we
  # escape and rewrite. `\x00` isn't valid in bash strings; use a
  # rare printable sentinel instead.
  local GLOBSTAR=$'\x1F\x1FGS\x1F\x1F'
  # 1. Substitute ** with the sentinel (must happen before single-*
  #    substitution, otherwise ** becomes [^/]*[^/]*).
  p="${p//\*\*/${GLOBSTAR}}"
  # 2. Escape regex metacharacters except: * ? [ ] (glob chars) and /
  #    (path separator, safe as-is).
  local out=""
  local i c
  for ((i = 0; i < ${#p}; i++)); do
    c="${p:i:1}"
    case "${c}" in
      '.' | '+' | '(' | ')' | '{' | '}' | '^' | '$' | '|') out+="\\${c}" ;;
      $'\\')  out+=$'\\\\' ;;
      '*')    out+='[^/]*' ;;
      '?')    out+='[^/]' ;;
      *)      out+="${c}" ;;
    esac
  done
  # 3. Restore ** placeholder as regex `.*`
  out="${out//${GLOBSTAR}/.*}"
  # 4. Convert POSIX glob negated character classes [!...] to ERE
  #    negated classes [^...]. Shell globs don't permit a literal `[`
  #    outside class context, so any `[!` in the built-up string is
  #    unambiguously a negated-class opener.
  out="${out//\[!/[^}"
  printf '^%s$\n' "${out}"
}

# Expand a list of patterns against a git ref into a sorted list of
# concrete paths. Signature:
#   expand_patterns_into <ref> <input_patterns_array_name> <output_array_name>
#
# Deliberately DOES NOT dedup: callers rely on the raw multi-set to
# detect overlap-between-patterns as a config bug (via a downstream
# `uniq -d` check on the expanded set). The sync script's own callers
# ALSO dedup via `sort -u` when they union multiple expansions, so
# leaving per-call dedup out doesn't compromise its correctness either.
#
# Glob patterns that expanded to zero files surface as warnings
# (usually a stale manifest entry) but are not fatal at this layer.
expand_patterns_into() {
  local ref="${1}" in_var="${2}" out_var="${3}"
  # shellcheck disable=SC2178  # nameref -- assigned from another array
  local -n in_arr="${in_var}"
  # shellcheck disable=SC2034,SC2178  # nameref -- referenced by caller
  local -n out_arr="${out_var}"
  local pattern p
  local -a expanded=()
  local -a empty_patterns=()
  for pattern in "${in_arr[@]}"; do
    local pattern_matched=0
    # shellcheck disable=SC2312  # expand_pattern handles its own errors
    while IFS= read -r p; do
      [[ -z "${p}" ]] && continue
      pattern_matched=1
      expanded+=("${p}")
    done < <(expand_pattern "${ref}" "${pattern}")
    case "${pattern}" in
      *'*'* | *'?'* | *'['*)
        if [[ ${pattern_matched} -eq 0 ]]; then
          empty_patterns+=("${pattern}")
        fi
        ;;
      *) ;;
    esac
  done
  # sort for stable iteration (helps golden-comparison tests too).
  # `printf '%s\n' "${arr[@]}"` on an empty array still emits one empty
  # line in bash (format applied once with no args); filter with
  # `grep -v '^$'` so out_arr comes back as a genuinely empty array
  # rather than a one-empty-string array.
  local sorted
  sorted="$(printf '%s\n' "${expanded[@]}" | grep -v '^$' | sort || true)"
  if [[ -n "${sorted}" ]]; then
    mapfile -t out_arr <<<"${sorted}"
  else
    # shellcheck disable=SC2034  # nameref -- consumed by caller
    out_arr=()
  fi
  # Warn (do not fail) on globs that matched nothing -- almost always
  # a stale manifest entry. Callers surface these via the emit_warning
  # function they define.
  local ep
  for ep in "${empty_patterns[@]}"; do
    emit_warning "Glob pattern '${ep}' expanded to zero files on ${ref}; manifest may be stale."
  done
}

# Read the two parallel arrays FILES_ALL (paths) and
# FILES_ALL_EXCLUDE_LISTS (each element is a comma-separated string of
# branch names that this entry is excluded from -- empty string when
# the entry applies to every rel branch) from a manifest file.
#
# Signature: read_manifest_entries <manifest_path> <paths_var> <excludes_var>
# Both output arrays are populated by parallel index, so the caller
# can iterate one and look up exclusions in the other.
#
# Handles both entry shapes accepted in .github/byte-identical.yml:
#   - simple string:   `- path/to/file`
#   - object form:     `- path: path/to/file`
#                      `  exclude-branches: [rel-800, rel-704]`
#
# Uses Mike Farah yq (v4+). The `.path // .` expression returns the
# object's `path` field when the entry is an object and the entry
# itself when it's a scalar string.
read_manifest_entries() {
  local manifest="${1}" paths_var="${2}" excludes_var="${3}"
  # shellcheck disable=SC2178  # nameref
  local -n paths_arr="${paths_var}"
  # shellcheck disable=SC2178  # nameref
  local -n excludes_arr="${excludes_var}"

  local paths_raw excludes_raw
  paths_raw="$(yq -r '.files[] | (.path // .)' "${manifest}")"
  excludes_raw="$(yq -r '.files[] | (.["exclude-branches"] // [] | join(","))' "${manifest}")"

  mapfile -t paths_arr <<<"${paths_raw}"
  mapfile -t excludes_arr <<<"${excludes_raw}"

  # yq emits one blank line per entry even when the array is empty;
  # normalize to a truly-empty array in that edge case.
  if [[ ${#paths_arr[@]} -eq 1 && -z "${paths_arr[0]}" ]]; then
    paths_arr=()
    # shellcheck disable=SC2034  # nameref -- consumed by caller
    excludes_arr=()
  fi
}

# Filter a (path, exclude-list) parallel pair down to just the entries
# that apply to a given target rel branch. Signature:
#   filter_by_branch <target_branch> <in_paths_var> <in_excl_var> <out_paths_var>
# Entries whose exclude-list contains target_branch are dropped from
# the output.
filter_by_branch() {
  local target="${1}" in_paths_var="${2}" in_excl_var="${3}" out_var="${4}"
  # shellcheck disable=SC2178  # nameref
  local -n in_paths="${in_paths_var}"
  # shellcheck disable=SC2178  # nameref
  local -n in_excl="${in_excl_var}"
  # shellcheck disable=SC2034,SC2178  # nameref -- consumed by caller
  local -n out_arr="${out_var}"

  # shellcheck disable=SC2034  # populated below then copied out
  local -a _fbb_out=()
  local i excl
  for i in "${!in_paths[@]}"; do
    excl="${in_excl[i]:-}"
    if [[ -n "${excl}" ]]; then
      local -a excl_arr
      IFS=',' read -ra excl_arr <<<"${excl}"
      local skip=0
      local e
      for e in "${excl_arr[@]}"; do
        if [[ "${e}" == "${target}" ]]; then
          skip=1
          break
        fi
      done
      if [[ ${skip} -eq 1 ]]; then
        continue
      fi
    fi
    _fbb_out+=("${in_paths[i]}")
  done
  # shellcheck disable=SC2034  # nameref -- consumed by caller
  out_arr=("${_fbb_out[@]}")
}
