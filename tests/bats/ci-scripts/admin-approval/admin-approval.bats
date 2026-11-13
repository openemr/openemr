# BATS tests for .github/scripts/admin-approval.sh
#
# Runs the script against a mock `gh` (see helpers.bash + gh-mock.sh).
# Covers:
#   - admin approval -> exit 0, prints the login, whatever commit it was on
#   - approval by a non-admin -> exit 1
#   - a later request-changes or dismissal supersedes the approval
#   - a later plain comment does not
#   - reviews spread across pages
#   - unreadable reviews or permissions -> exit 1 with a warning on stderr
#   - missing required env -> error

bats_require_minimum_version 1.5.0

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

@test "admin approved the head commit -> exit 0 with the login" {
    MOCK_REVIEWS="[[$(review root APPROVED "${HEAD}")]]" run bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ ${output} = root ]]
}

@test "no reviews -> exit 1" {
    run bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ -z ${output} ]]
}

@test "non-admin approval -> exit 1" {
    MOCK_REVIEWS="[[$(review dev APPROVED "${HEAD}")]]" run bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -eq 1 ]]
}

@test "admin approved an older commit -> exit 0, the approval stands" {
    MOCK_REVIEWS="[[$(review root APPROVED "${OLD}")]]" run bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ ${output} = root ]]
}

@test "later request for changes supersedes the approval -> exit 1" {
    MOCK_REVIEWS="[[$(review root APPROVED "${HEAD}"),$(review root CHANGES_REQUESTED "${HEAD}")]]" \
        run bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -eq 1 ]]
}

@test "dismissed approval -> exit 1" {
    MOCK_REVIEWS="[[$(review root DISMISSED "${HEAD}")]]" run bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -eq 1 ]]
}

@test "later comment does not supersede the approval -> exit 0" {
    MOCK_REVIEWS="[[$(review root APPROVED "${HEAD}"),$(review root COMMENTED "${HEAD}")]]" \
        run bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ ${output} = root ]]
}

@test "approval on a later page supersedes a request on an earlier one -> exit 0" {
    MOCK_REVIEWS="[[$(review root CHANGES_REQUESTED "${OLD}")],[$(review dev APPROVED "${HEAD}"),$(review root APPROVED "${HEAD}")]]" \
        run bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ ${output} = root ]]
}

@test "approver without collaborator access -> exit 1" {
    MOCK_REVIEWS="[[$(review stranger APPROVED "${HEAD}")]]" run bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ ${output} == *'Could not read the repository permission of stranger'* ]]
}

# The action captures stdout as the approver's login, so in Actions the
# warning for an earlier approver must not land there.
@test "unreadable permission before an admin approval -> stdout is only the login" {
    MOCK_REVIEWS="[[$(review stranger APPROVED "${HEAD}"),$(review root APPROVED "${HEAD}")]]" \
        GITHUB_ACTIONS=true run --separate-stderr bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ ${output} = root ]]
    [[ ${stderr} == *'::warning::Could not read the repository permission of stranger.'* ]]
}

@test "reviews unreadable -> exit 1 with a warning annotation on stderr" {
    GITHUB_ACTIONS=true MOCK_REVIEWS_EXIT=1 run --separate-stderr bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ -z ${output} ]]
    [[ ${stderr} == *'::warning::Could not read the reviews of pull request #42.'* ]]
}

@test "missing PR_NUMBER -> error" {
    unset PR_NUMBER
    run bash "${ADMIN_APPROVAL_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ ${output} == *'PR_NUMBER must be set'* ]]
}
