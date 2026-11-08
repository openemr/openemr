#!/usr/bin/env bash
#
# Fail loudly if any of the named env vars is unset or empty.
#
# Used by reusable-publish-release.yml + reusable-docker-publish.yml
# preflight steps to enforce non-empty required inputs BEFORE any
# destructive publish op runs. See G37 in
# docs/release-mechanism-gaps.md for the full rationale.
#
# workflow_call's `required: true` only validates the input is
# DECLARED at the call site, not that its value is non-empty --
# `-f artifact_name=""` satisfies `required: true` but would cause
# downstream steps to malfunction (or worse, in a destructive
# workflow, take a wrong action). This script closes that gap.
#
# Usage:
#   .github/scripts/assert-inputs-nonempty.sh <context-label> <INPUT_NAME>...
#
# For each INPUT_NAME, reads the same-named env var and asserts it
# is set + non-empty. Aggregates ALL missing names into one error
# annotation (so callers see the full list, not just the first).
#
#   context-label   Free-text label included in the error message
#                   for provenance (e.g. "reusable-publish-release.yml").
#                   Required. Cannot be empty.
#   INPUT_NAME...   One or more env-var names to check. Zero names
#                   is an error (script was called with no work to
#                   do -- caller bug worth surfacing).
#
# Exit codes:
#   0  all named env vars are non-empty
#   1  script was called incorrectly (no context label, or no
#      input names given)
#   2  one or more named env vars is unset or empty
#
# On success, echoes a one-line "Preflight OK" summary listing each
# name + a redacted-length representation (never echoes values --
# some inputs like tokens shouldn't hit CI logs even for debugging).
#
# Unit-tested by tests/bats/ci-scripts/assert-inputs-nonempty/.

set -euo pipefail

context="${1:-}"
if [[ -z "${context}" ]]; then
    echo "::error::assert-inputs-nonempty.sh: usage: <context-label> <INPUT_NAME>... (context-label was empty)" >&2
    exit 1
fi
shift

if [[ $# -eq 0 ]]; then
    echo "::error::assert-inputs-nonempty.sh (${context}): no input names supplied (script was called with nothing to check -- caller bug)" >&2
    exit 1
fi

missing=()
summary_parts=()
for name in "$@"; do
    # Indirect env-var expansion: value_of_$name.
    value="${!name:-}"
    if [[ -z "${value}" ]]; then
        missing+=("${name}")
    else
        summary_parts+=("${name} (len=${#value})")
    fi
done

if (( ${#missing[@]} > 0 )); then
    echo "::error::${context}: required input(s) missing or empty: ${missing[*]}. Caller must supply non-empty values." >&2
    exit 2
fi

echo "==> Preflight OK (${context}): ${summary_parts[*]}"
