#!/usr/bin/env bash
#
# Validate a boolean-flag input that requires a non-empty reason when true.
#
# Both `acceptance-only.yml` and `docker-acceptance-only.yml` carry two
# flags each (`skip_acceptance` + `no_publish`) that follow the same
# audit-trail shape: if the flag is `true`, a matching reason input must
# be non-empty (whitespace stripped) so the run's *why* gets recorded in
# the run-name, workflow summary, and (for `skip_acceptance`) release
# description. Extracted here so the fail-loud rule + the exact error
# message shape land once instead of four times -- and so the rule gets
# BATS coverage independent of the workflow YAML.
#
# The script does ONLY the fail-loud validation. Warning annotations +
# GITHUB_STEP_SUMMARY blocks are emitted by the calling workflow step
# because their body varies per flag (skip_acceptance's summary text
# differs from no_publish's, etc.).
#
# Inputs (env)
#   FLAG_NAME     required. The flag input's name (used in the error
#                 message), e.g. "skip_acceptance" or "no_publish".
#   FLAG_VALUE    required-to-be-set (may be empty). The flag input's
#                 value; only "true" (lowercase) is treated as enabled.
#                 GitHub Actions boolean inputs render as "true"/"false".
#   REASON_NAME   required. The reason input's name (used in the error
#                 message), e.g. "skip_acceptance_reason".
#   REASON_VALUE  required-to-be-set (may be empty). The reason input's
#                 value.
#
# Exit codes
#   0  validation passed (either FLAG_VALUE != "true", or FLAG_VALUE =
#      "true" and REASON_VALUE non-empty after whitespace-strip)
#   1  validation failed (FLAG_VALUE = "true" and REASON_VALUE empty or
#      whitespace-only) OR a required env var missing

set -euo pipefail

: "${FLAG_NAME:?FLAG_NAME must be set (e.g. 'skip_acceptance')}"
: "${REASON_NAME:?REASON_NAME must be set (e.g. 'skip_acceptance_reason')}"

# FLAG_VALUE and REASON_VALUE may be legitimately empty (GitHub Actions
# renders unset boolean inputs as empty string, not "false"). Default to
# empty for the checks below.
FLAG_VALUE="${FLAG_VALUE:-}"
REASON_VALUE="${REASON_VALUE:-}"

# Strip all whitespace before length check -- a reason that is only
# spaces / tabs / newlines is not a real audit-trail entry, so treat
# it as empty.
STRIPPED_REASON="${REASON_VALUE//[[:space:]]/}"

if [[ "${FLAG_VALUE}" == "true" ]] && [[ -z "${STRIPPED_REASON}" ]]; then
    echo "::error::${FLAG_NAME}=true requires a non-empty ${REASON_NAME} (whitespace-only reasons are rejected). The reason appears in the run-name and workflow summary for audit-trail visibility. Re-dispatch with an explanation."
    exit 1
fi
