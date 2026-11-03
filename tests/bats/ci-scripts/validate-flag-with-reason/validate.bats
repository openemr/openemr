# BATS tests for .github/scripts/validate-flag-with-reason.sh
#
# Shared audit-trail validation for boolean flag inputs on the recovery
# workflows (acceptance-only.yml + docker-acceptance-only.yml) that
# require a non-empty reason string when the flag is `true`. Currently
# used by skip_acceptance + no_publish on both workflows.
#
# Covers:
#   - flag off + empty reason -> pass (default state)
#   - flag off + non-empty reason -> pass (defensive; reason ignored when
#     flag off)
#   - flag on + valid reason -> pass
#   - flag on + empty reason -> exit 1 with clear error (::error::
#     annotation naming both FLAG_NAME + REASON_NAME)
#   - flag on + whitespace-only reason -> exit 1 (space, tab, newline,
#     mixed)
#   - flag on + reason that starts/ends with whitespace but has content
#     in the middle -> pass (whitespace-strip is for emptiness check
#     only, not sanitization)
#   - FLAG_VALUE case sensitivity -- only "true" (lowercase) enables;
#     "True"/"TRUE"/"1"/"yes" are treated as not-enabled
#   - Required env vars: FLAG_NAME / REASON_NAME missing -> exit 1
#   - FLAG_VALUE / REASON_VALUE unset -> treated as empty (defaulting
#     rules match GitHub Actions rendering unset boolean inputs as "")

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# --- happy paths ---

@test "flag off (empty) + empty reason -> pass" {
    FLAG_NAME=skip_acceptance FLAG_VALUE="" REASON_NAME=skip_acceptance_reason REASON_VALUE="" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" != *"::error::"* ]]
}

@test "flag off (false) + empty reason -> pass" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=false REASON_NAME=skip_acceptance_reason REASON_VALUE="" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 0 ]]
}

@test "flag off + non-empty reason -> pass (reason ignored when flag off)" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=false REASON_NAME=skip_acceptance_reason REASON_VALUE="ignored" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 0 ]]
}

@test "flag on + valid reason -> pass" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=true REASON_NAME=skip_acceptance_reason REASON_VALUE="PHP 8.6 upstream flake" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" != *"::error::"* ]]
}

@test "flag on + reason with leading/trailing spaces but content in middle -> pass" {
    FLAG_NAME=no_publish FLAG_VALUE=true REASON_NAME=no_publish_reason REASON_VALUE="   recovery-path smoketest   " \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 0 ]]
}

# --- fail paths (the whole point of the script) ---

@test "flag on + empty reason -> exit 1 with error naming both flags" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=true REASON_NAME=skip_acceptance_reason REASON_VALUE="" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::"* ]]
    [[ "${output}" == *"skip_acceptance=true"* ]]
    [[ "${output}" == *"skip_acceptance_reason"* ]]
    [[ "${output}" == *"whitespace-only reasons are rejected"* ]]
}

@test "flag on + spaces-only reason -> exit 1" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=true REASON_NAME=skip_acceptance_reason REASON_VALUE="     " \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 1 ]]
}

@test "flag on + tabs-only reason -> exit 1" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=true REASON_NAME=skip_acceptance_reason REASON_VALUE=$'\t\t\t' \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 1 ]]
}

@test "flag on + newlines-only reason -> exit 1" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=true REASON_NAME=skip_acceptance_reason REASON_VALUE=$'\n\n\n' \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 1 ]]
}

@test "flag on + mixed whitespace-only reason (spaces + tabs + newlines) -> exit 1" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=true REASON_NAME=skip_acceptance_reason REASON_VALUE=$' \t\n \t' \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 1 ]]
}

# --- FLAG_NAME propagation into error messages ---

@test "error message uses the caller-supplied FLAG_NAME (no_publish)" {
    FLAG_NAME=no_publish FLAG_VALUE=true REASON_NAME=no_publish_reason REASON_VALUE="" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"no_publish=true"* ]]
    [[ "${output}" == *"no_publish_reason"* ]]
    [[ "${output}" != *"skip_acceptance"* ]]
}

# --- FLAG_VALUE case sensitivity ---

@test "flag value 'True' (capitalized) -> treated as off, empty reason passes" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=True REASON_NAME=skip_acceptance_reason REASON_VALUE="" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 0 ]]
}

@test "flag value 'TRUE' (uppercase) -> treated as off, empty reason passes" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=TRUE REASON_NAME=skip_acceptance_reason REASON_VALUE="" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 0 ]]
}

@test "flag value '1' -> treated as off (only 'true' enables), empty reason passes" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=1 REASON_NAME=skip_acceptance_reason REASON_VALUE="" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 0 ]]
}

@test "flag value 'yes' -> treated as off (only 'true' enables), empty reason passes" {
    FLAG_NAME=skip_acceptance FLAG_VALUE=yes REASON_NAME=skip_acceptance_reason REASON_VALUE="" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 0 ]]
}

# --- required env vars ---

@test "missing FLAG_NAME -> exit 1" {
    # shellcheck disable=SC2030,SC2031  # unset is intentional per-subshell
    unset FLAG_NAME
    FLAG_VALUE=true REASON_NAME=skip_acceptance_reason REASON_VALUE="foo" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"FLAG_NAME"* ]]
}

@test "missing REASON_NAME -> exit 1" {
    # shellcheck disable=SC2030,SC2031  # unset is intentional per-subshell
    unset REASON_NAME
    FLAG_NAME=skip_acceptance FLAG_VALUE=true REASON_VALUE="foo" \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"REASON_NAME"* ]]
}

@test "unset FLAG_VALUE + REASON_VALUE -> treated as empty; flag off; pass" {
    # shellcheck disable=SC2030,SC2031  # unset is intentional per-subshell
    unset FLAG_VALUE REASON_VALUE
    FLAG_NAME=skip_acceptance REASON_NAME=skip_acceptance_reason \
        run bash "${VALIDATE_FLAG_WITH_REASON_SCRIPT}"
    [[ ${status} -eq 0 ]]
}
