#!/usr/bin/env bash
#
# Validate the byte-identical file set across master and every rel branch.
#
# Reads FILES_ALL from .github/byte-identical.yml in cwd. In
# master context also reads REL_BRANCHES from .github/release-targets.yml;
# that file is master-only orchestrator config and rel branches don't
# carry it (the rel-* path doesn't need it -- it just walks FILES_ALL
# and compares each entry to master HEAD). Compares each FILES_ALL
# entry across branches via raw.githubusercontent.com fetches. Behavior
# depends on run context -- see the header of
# .github/workflows/validate-byte-identical.yml for the rationale.
#
# Inputs (env)
#   GITHUB_EVENT_NAME    "pull_request", "schedule", "push",
#                        "workflow_dispatch". Determines fail vs warn level.
#   GITHUB_BASE_REF      PR base ref (for pull_request)
#   GITHUB_REF_NAME      branch the workflow ran on (everything else)
#   GITHUB_ACTIONS       when "true", emit ::error:: / ::warning::
#                        annotations alongside plain output; when unset,
#                        plain ERROR / WARNING lines so test output stays
#                        readable
#   RAW_FETCH_BASE_URL   base URL for raw-file fetches; default is the
#                        production raw.githubusercontent.com path. Tests
#                        point this at a fixture, with curl mocked on PATH
#                        to map URLs back to local files.
#
# Behavior by context
#   master context (CONTEXT_BRANCH = master):
#     iterate REL_BRANCHES, compare each remote HEAD to LOCAL (master).
#     On a master pull_request, drift is reported as warnings (auto-sync
#     resolves it after merge). On schedule / dispatch, drift fails.
#
#   rel-* context (CONTEXT_BRANCH = rel-X):
#     compare this checkout (LOCAL = rel-X, possibly + PR diff) against
#     master HEAD. Drift fails. A FILES_ALL entry missing from the local
#     checkout fails too: auto-sync delivers config + file in a single
#     PR, so a missing file always means either a destructive PR or a
#     hand-edit config bug.
#
# Exit codes
#   0   no drift (warnings only OK)
#   1   drift detected and fail mode active, OR config bug
#   2   precondition error (missing yq, missing config files, missing
#       context env vars)

set -euo pipefail

RAW_FETCH_BASE_URL="${RAW_FETCH_BASE_URL:-https://raw.githubusercontent.com/openemr/openemr}"

emit_error() {
  local msg="$1"
  if [[ "${GITHUB_ACTIONS:-}" == "true" ]]; then
    echo "::error::${msg}" >&2
  else
    echo "ERROR: ${msg}" >&2
  fi
}

emit_error_file() {
  local file="$1" msg="$2"
  if [[ "${GITHUB_ACTIONS:-}" == "true" ]]; then
    echo "::error file=${file}::${msg}"
  else
    echo "ERROR ${file}: ${msg}"
  fi
}

emit_warning() {
  local msg="$1"
  if [[ "${GITHUB_ACTIONS:-}" == "true" ]]; then
    echo "::warning::${msg}"
  else
    echo "WARNING: ${msg}"
  fi
}

emit_warning_file() {
  local file="$1" msg="$2"
  if [[ "${GITHUB_ACTIONS:-}" == "true" ]]; then
    echo "::warning file=${file}::${msg}"
  else
    echo "WARNING ${file}: ${msg}"
  fi
}

# Source the shared glob-expansion helpers (expand_pattern,
# glob_to_regex, expand_patterns_into). Sourced AFTER emit_warning is
# defined -- expand_patterns_into calls it on stale-glob patterns.
#
# `source=/dev/null` opts out of shellcheck's static follow. The
# alternative -- pointing the directive at `lib/glob-expand.sh` --
# requires `source-path=SCRIPTDIR` in .shellcheckrc, which is not in
# the byte-identical set and would therefore stay master-only,
# breaking shellcheck on rel branches. Losing shellcheck's view into
# the sourced helpers is a smaller cost than the cross-branch
# coordination burden.
# shellcheck source=/dev/null
source "$(dirname "${BASH_SOURCE[0]}")/lib/glob-expand.sh"

# Preconditions
command -v yq >/dev/null 2>&1 || {
  emit_error "yq not found on PATH (need Mike Farah's Go-based yq)."
  exit 2
}
[[ -f .github/byte-identical.yml ]] || {
  emit_error ".github/byte-identical.yml not present in cwd."
  exit 2
}
# Note: .github/release-targets.yml presence is only required in master
# context; the file is master-side orchestrator config that rel branches
# don't carry (and don't need -- the rel-* path only walks FILES_ALL and
# fetches master's copy of each entry). Its check moves into the
# master-context branch below.

# Read the manifest as two parallel arrays: paths + per-entry exclude
# lists. String entries produce empty-string exclude, object-form
# entries produce a comma-joined list. See read_manifest_entries in
# lib/glob-expand.sh.
declare -a FILES_ALL_RAW=()
# shellcheck disable=SC2034  # nameref -- populated by read_manifest_entries
declare -a FILES_ALL_EXCLUDES=()
read_manifest_entries .github/byte-identical.yml FILES_ALL_RAW FILES_ALL_EXCLUDES

if [[ ${#FILES_ALL_RAW[@]} -eq 0 ]]; then
  # Fail closed: an empty manifest would silently disable the canary.
  emit_error "No files listed in byte-identical.yml; refusing to skip drift validation."
  exit 1
fi

# Reject duplicate paths in the raw manifest -- catches config bugs
# like listing the same path twice with different exclude lists.
dupes=$(printf '%s\n' "${FILES_ALL_RAW[@]}" | sort | uniq -d)
if [[ -n "${dupes}" ]]; then
  dupes_one_line=$(echo "${dupes}" | tr '\n' ' ')
  emit_error "Duplicate entries in byte-identical.yml files: ${dupes_one_line}"
  exit 1
fi

# expand_files_for_branch <branch> <out_expanded_var>
# Filter FILES_ALL_RAW for entries applicable to <branch>, expand
# them against HEAD, and populate <out_expanded_var> with the
# concrete paths. Per-branch empty results are fine (no fail here);
# the full-scope no-filter empty check below catches truly-empty
# manifests.
expand_files_for_branch() {
  local target="${1}" out_var="${2}"
  # shellcheck disable=SC2034  # nameref -- populated by filter_by_branch
  local -a filtered=()
  filter_by_branch "${target}" FILES_ALL_RAW FILES_ALL_EXCLUDES filtered
  # shellcheck disable=SC2178  # nameref
  local -n out_ref="${out_var}"
  # shellcheck disable=SC2034,SC2178
  out_ref=()
  expand_patterns_into HEAD filtered "${out_var}"
}

# Full-scope expansion (all raw entries, no branch filter) for
# initial fail-closed checks. Empty full expansion means every
# pattern is stale or the manifest is entirely glob-only + matched
# nothing -- a config bug worth failing on.
declare -a FULL_EXPANDED=()
expand_patterns_into HEAD FILES_ALL_RAW FULL_EXPANDED
if [[ ${#FULL_EXPANDED[@]} -eq 0 ]]; then
  emit_error "byte-identical.yml patterns expanded to zero paths on HEAD; refusing to skip drift validation."
  exit 1
fi
dupes_expanded=$(printf '%s\n' "${FULL_EXPANDED[@]}" | sort | uniq -d)
if [[ -n "${dupes_expanded}" ]]; then
  dupes_expanded_one_line=$(echo "${dupes_expanded}" | tr '\n' ' ')
  emit_error "Duplicate paths after glob expansion (glob overlaps literal or another glob): ${dupes_expanded_one_line}"
  exit 1
fi

# Determine run context. pull_request uses the PR's base ref; everything
# else (schedule, push, workflow_dispatch) uses the ref the workflow
# ran on.
case "${GITHUB_EVENT_NAME:-}" in
  pull_request) CONTEXT_BRANCH="${GITHUB_BASE_REF:-}" ;;
  *) CONTEXT_BRANCH="${GITHUB_REF_NAME:-}" ;;
esac

if [[ -z "${CONTEXT_BRANCH}" ]]; then
  emit_error "Could not determine run context (GITHUB_EVENT_NAME=${GITHUB_EVENT_NAME:-unset}, GITHUB_BASE_REF=${GITHUB_BASE_REF:-unset}, GITHUB_REF_NAME=${GITHUB_REF_NAME:-unset})."
  exit 2
fi

echo "Run context: event=${GITHUB_EVENT_NAME:-unknown} branch=${CONTEXT_BRANCH}"

TMP_REMOTE=$(mktemp)
trap 'rm -f "${TMP_REMOTE}"' EXIT

FAIL=0

# Wrap the raw-URL fetch so the curl flags live in exactly one place.
# Echoes the HTTP status; writes the body to ${TMP_REMOTE}.
fetch_status() {
  local url="$1"
  curl -sS --connect-timeout 10 --max-time 30 --retry 3 --retry-delay 2 --retry-connrefused \
    -o "${TMP_REMOTE}" -w '%{http_code}' "${url}"
}

if [[ "${CONTEXT_BRANCH}" == "master" ]]; then
  # Master context needs the rel branch list -- read release-targets.yml
  # here (not at script start) so the rel-* context doesn't require it.
  [[ -f .github/release-targets.yml ]] || {
    emit_error ".github/release-targets.yml not present in cwd (required in master context)."
    exit 2
  }
  rel_yq_output=$(yq -r '.[] | select(.branch != "master") | .branch' .github/release-targets.yml)
  mapfile -t REL_BRANCHES <<<"${rel_yq_output}"
  if [[ ${#REL_BRANCHES[@]} -eq 1 && -z "${REL_BRANCHES[0]}" ]]; then
    REL_BRANCHES=()
  fi
  if [[ ${#REL_BRANCHES[@]} -eq 0 ]]; then
    emit_warning "No rel branches found in release-targets.yml -- nothing to check."
    exit 0
  fi

  # Master context: master is canonical. Every RAW entry (regardless
  # of exclude-branches) must exist on master; missing ones are a
  # config bug. Exclusions only scope which REL BRANCHES the file is
  # checked against, not whether it's expected on master. Reuse the
  # FULL_EXPANDED set already computed above for the empty-manifest
  # check.
  missing=()
  for FILE in "${FULL_EXPANDED[@]}"; do
    [[ -f "${FILE}" ]] || missing+=("${FILE}")
  done
  if [[ ${#missing[@]} -gt 0 ]]; then
    emit_error "Files listed in byte-identical.yml but missing from master checkout: ${missing[*]}"
    exit 1
  fi

  # On a master pull_request the drift is intentional/in-progress (the
  # PR is the source of truth; auto-sync propagates after merge). Surface
  # it as warnings, don't block the PR. On cron / dispatch, drift fails.
  if [[ "${GITHUB_EVENT_NAME:-}" == "pull_request" ]]; then
    DRIFT_LEVEL="warning"
  else
    DRIFT_LEVEL="error"
  fi

  for BRANCH in "${REL_BRANCHES[@]}"; do
    echo "::group::Compare ${BRANCH} to master"
    # Per-branch expansion: skip entries whose exclude-branches list
    # contains this branch (e.g. release-mechanism files aren't
    # expected on rel-800 / rel-704).
    declare -a FILES_ALL_EXPANDED=()
    expand_files_for_branch "${BRANCH}" FILES_ALL_EXPANDED
    for FILE in "${FILES_ALL_EXPANDED[@]}"; do
      LOCAL_SHA=$(sha256sum "${FILE}" | cut -d' ' -f1)
      URL="${RAW_FETCH_BASE_URL}/${BRANCH}/${FILE}"
      STATUS=$(fetch_status "${URL}")
      if [[ "${STATUS}" != "200" ]]; then
        if [[ "${DRIFT_LEVEL}" == "error" ]]; then
          emit_error_file "${FILE}" "${BRANCH}:${FILE} not present on remote (HTTP ${STATUS}); expected per FILES_ALL."
          echo "  Either a destructive change to ${BRANCH} or an overdue auto-sync delivery."
          echo "  Fix: trigger sync-byte-identical.yml manually, or restore the file on ${BRANCH}."
          FAIL=1
        else
          emit_warning_file "${FILE}" "${BRANCH}:${FILE} not present (HTTP ${STATUS}); auto-sync will add it on the next run."
        fi
        continue
      fi
      REMOTE_SHA=$(sha256sum "${TMP_REMOTE}" | cut -d' ' -f1)
      if [[ "${LOCAL_SHA}" != "${REMOTE_SHA}" ]]; then
        if [[ "${DRIFT_LEVEL}" == "error" ]]; then
          emit_error_file "${FILE}" "Drift: ${BRANCH}:${FILE} differs from master"
          echo "  master sha:  ${LOCAL_SHA}"
          echo "  ${BRANCH} sha: ${REMOTE_SHA}"
          echo "  Fix: an auto-sync PR should already be open for ${BRANCH}; merge it, or trigger sync-byte-identical.yml manually."
          FAIL=1
        else
          emit_warning_file "${FILE}" "${BRANCH}:${FILE} will differ from master once this PR merges; auto-sync will resolve."
        fi
      else
        echo "✓ ${BRANCH}:${FILE} matches master"
      fi
    done
    echo "::endgroup::"
  done
else
  # Rel branch context: master is canonical, this branch must match it.
  # Auto-sync delivers config + file together in one PR, so a missing
  # local file is always either a destructive PR or a hand-edit config
  # bug -- either way, fail.
  echo "Comparing ${CONTEXT_BRANCH} (LOCAL) to master HEAD (REMOTE)."
  # Expand only the entries applicable to THIS rel branch, honouring
  # per-entry exclude-branches lists.
  declare -a FILES_ALL_EXPANDED=()
  expand_files_for_branch "${CONTEXT_BRANCH}" FILES_ALL_EXPANDED
  for FILE in "${FILES_ALL_EXPANDED[@]}"; do
    if [[ ! -f "${FILE}" ]]; then
      emit_error_file "${FILE}" "${FILE} listed in FILES_ALL but missing from ${CONTEXT_BRANCH}."
      echo "  Either this PR deleted a protected file, or byte-identical.yml lists a file the branch does not carry."
      echo "  Fix: restore the file (let auto-sync redeliver it from master, or revert the deletion)."
      FAIL=1
      continue
    fi
    LOCAL_SHA=$(sha256sum "${FILE}" | cut -d' ' -f1)
    URL="${RAW_FETCH_BASE_URL}/master/${FILE}"
    STATUS=$(fetch_status "${URL}")
    if [[ "${STATUS}" != "200" ]]; then
      emit_warning "master:${FILE} not present (HTTP ${STATUS}); FILES_ALL may have drifted from master."
      continue
    fi
    REMOTE_SHA=$(sha256sum "${TMP_REMOTE}" | cut -d' ' -f1)
    if [[ "${LOCAL_SHA}" != "${REMOTE_SHA}" ]]; then
      emit_error_file "${FILE}" "Drift: ${CONTEXT_BRANCH}:${FILE} differs from master"
      echo "  ${CONTEXT_BRANCH} sha: ${LOCAL_SHA}"
      echo "  master sha:          ${REMOTE_SHA}"
      echo "  Fix: rel branches must match master. Let auto-sync (sync-byte-identical.yml) propose the update, or revert this PR's change to the file."
      FAIL=1
    else
      echo "✓ ${FILE} matches master"
    fi
  done
fi

[[ ${FAIL} -eq 0 ]] || exit 1
echo
if [[ "${CONTEXT_BRANCH}" == "master" ]]; then
  echo "✓ All byte-identical files match across master and every rel branch."
else
  echo "✓ All byte-identical files on ${CONTEXT_BRANCH} match master."
fi
