# Helpers for BATS tests of .github/scripts/admin-approval.sh.
#
# Loaded by admin-approval.bats via `load 'helpers'`. Installs the `gh`
# mock (see gh-mock.sh for its contract) so the script never calls GitHub.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
ADMIN_APPROVAL_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/admin-approval.sh"

HEAD='1111111111111111111111111111111111111111'
OLD='0000000000000000000000000000000000000000'

setup_test_dir() {
    CWD=$(mktemp -d)
    local mock_dir="${CWD}/.mocks"
    mkdir -p "${mock_dir}"
    cp "${__HELPERS_DIR}/gh-mock.sh" "${mock_dir}/gh"
    chmod +x "${mock_dir}/gh"
    export PATH="${mock_dir}:${PATH}"
    cd "${CWD}" || exit 1
    unset GITHUB_ACTIONS
    export REPO='openemr/openemr'
    export PR_NUMBER='42'
    export HEAD_SHA="${HEAD}"
    export MOCK_PERMISSIONS='root=admin dev=write'
    unset MOCK_REVIEWS MOCK_REVIEWS_EXIT
}

teardown_test_dir() {
    cd / || exit 1
    export PATH="${PATH#"${CWD}/.mocks":}"
    rm -rf "${CWD}"
    unset REPO PR_NUMBER HEAD_SHA MOCK_PERMISSIONS MOCK_REVIEWS MOCK_REVIEWS_EXIT
}

# Print one review object: review <login> <state> <commit>.
review() {
    printf '{"user":{"login":"%s"},"state":"%s","commit_id":"%s"}' "$1" "$2" "$3"
}
