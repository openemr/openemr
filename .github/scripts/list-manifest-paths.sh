#!/usr/bin/env bash
#
# Print the byte-identical manifest paths applicable to a given rel
# branch, one per line. Object-form entries whose `exclude-branches:`
# list contains the target branch are filtered out.
#
# Usage:
#   list-manifest-paths.sh <manifest-file> <target-branch>
#
# Example:
#   list-manifest-paths.sh .github/byte-identical.yml rel-820
#
# Rationale: sync-byte-identical.yml's "Compute add-paths input for
# peter-evans" step needs the same filter the sync script itself uses
# (`read_manifest_entries` + `filter_by_branch` in lib/glob-expand.sh).
# Before this script existed the workflow had its own inline yq
# expression that got the filter wrong twice (#12916 then #12920).
# Sharing the logic through the lib eliminates that class of bug.
#
# This script is master-only-firing (only invoked from
# sync-byte-identical.yml, which is master-only). Not in the byte-
# identical set. If a rel-branch copy of this script drifts from
# master's, no consumer cares -- master's version is materialized
# fresh into RUNNER_TEMP at workflow run time.
#
# Exit codes
#   0   normal completion (with or without paths printed)
#   2   precondition error: missing args, missing yq, missing manifest

set -euo pipefail

if [[ $# -lt 2 ]]; then
  echo "ERROR: usage: list-manifest-paths.sh <manifest-file> <target-branch>" >&2
  exit 2
fi
manifest="$1"
target="$2"

command -v yq >/dev/null 2>&1 || {
  echo "ERROR: yq not found on PATH (need Mike Farah's Go-based yq)." >&2
  exit 2
}
[[ -f "${manifest}" ]] || {
  echo "ERROR: manifest not found: ${manifest}" >&2
  exit 2
}

# lib/glob-expand.sh's expand_patterns_into calls emit_warning on
# stale-glob patterns. This script doesn't expand globs (it only
# reads + filters the raw entries), so emit_warning is never called
# here -- but the lib file expects the symbol to exist at source
# time, so provide a silent stub.
emit_warning() { :; }

# shellcheck source=lib/glob-expand.sh
# shellcheck disable=SC1091
source "$(dirname "${BASH_SOURCE[0]}")/lib/glob-expand.sh"

# shellcheck disable=SC2034  # populated via nameref by read_manifest_entries
declare -a paths=()
# shellcheck disable=SC2034  # populated via nameref by read_manifest_entries
declare -a excludes=()
read_manifest_entries "${manifest}" paths excludes

declare -a filtered=()
filter_by_branch "${target}" paths excludes filtered

if [[ ${#filtered[@]} -gt 0 ]]; then
  printf '%s\n' "${filtered[@]}"
fi
