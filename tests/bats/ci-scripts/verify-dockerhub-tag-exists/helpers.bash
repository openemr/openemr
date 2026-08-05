# Helpers for BATS tests of .github/scripts/verify-dockerhub-tag-exists.sh.
#
# Installs a `curl` mock on PATH (see curl-mock.sh for the contract) so
# the script under test never hits Docker Hub. Fixture behavior is
# controlled per-test via MOCK_CURL_HTTP_CODE / MOCK_CURL_BODY /
# MOCK_CURL_EXIT env vars.
#
# Pattern follows tests/bats/ci-scripts/verify-oci-labels/helpers.bash --
# standalone bash, plain [[ ]] asserts, no bats-support/bats-assert.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
VERIFY_DOCKERHUB_TAG_EXISTS_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/verify-dockerhub-tag-exists.sh"

setup_test_dir() {
    # `mktemp -d` (no `-t <template>`) -- BusyBox's mktemp in the
    # bats/bats:1.13.0 Alpine image rejects the `-t` form GNU mktemp
    # accepts.
    CWD=$(mktemp -d)

    # Install the curl mock at the front of PATH.
    local mock_dir="${CWD}/.curl-mock"
    mkdir -p "${mock_dir}"
    cp "${__HELPERS_DIR}/curl-mock.sh" "${mock_dir}/curl"
    chmod +x "${mock_dir}/curl"
    export PATH="${mock_dir}:${PATH}"

    cd "${CWD}" || exit 1

    # Default: happy-path 200. Individual tests override.
    export MOCK_CURL_HTTP_CODE="200"
    export MOCK_CURL_BODY=""
    export MOCK_CURL_EXIT="0"

    # Sensible default TAG so happy-path test is a one-liner. Individual
    # tests override.
    export TAG="release-candidate-12345-1"
}

teardown_test_dir() {
    cd /
    export PATH="${PATH#"${CWD}/.curl-mock":}"
    rm -rf "${CWD}"
    unset MOCK_CURL_HTTP_CODE MOCK_CURL_BODY MOCK_CURL_EXIT
    unset TAG REPO
}
