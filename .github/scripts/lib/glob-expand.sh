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
