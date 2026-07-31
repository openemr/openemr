# Helpers for BATS tests of .github/scripts/dockerhub-delete-tag.sh.
#
# Loaded by delete-tag.bats via `load 'helpers'`. Installs a `curl` mock
# on PATH (see curl-mock.sh for the contract) so the script under test
# never touches Docker Hub.
#
# Pattern follows tests/bats/ci-scripts/verify-oci-labels/helpers.bash --
# standalone bash, no bats-support / bats-assert; plain [[ ]] asserts.

# Absolute path to the script under test, captured AT LOAD TIME (before
# any @test does `cd` into its temp working dir).
__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
DOCKERHUB_DELETE_TAG_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/dockerhub-delete-tag.sh"

# Fresh working dir + curl mock. Sets the following vars in the calling
# shell (BATS's setup() runs in the same shell as the @test):
#
#   CWD                          fresh temp dir; the test cd's into it
#   MOCK_LOGIN_RESPONSE_FILE     exported -- fixture the curl mock reads
#                                to build the login POST response body.
#                                Tests populate this via write_login_body().
#   MOCK_DELETE_RESPONSE_FILE    exported -- fixture the curl mock reads
#                                to build the DELETE response body.
#                                Tests populate via write_delete_body().
#   PATH                         prepended with the curl mock dir.
setup_test_dir() {
    # `mktemp -d` (no `-t <template>`) — BusyBox's mktemp in the
    # bats/bats:1.13.0 Alpine image rejects the `-t` form that GNU
    # mktemp accepts.
    CWD=$(mktemp -d)

    export MOCK_LOGIN_RESPONSE_FILE="${CWD}/login-body.txt"
    export MOCK_DELETE_RESPONSE_FILE="${CWD}/delete-body.txt"

    # Install the curl mock at the front of PATH.
    local mock_dir="${CWD}/.curl-mock"
    mkdir -p "${mock_dir}"
    cp "${__HELPERS_DIR}/curl-mock.sh" "${mock_dir}/curl"
    chmod +x "${mock_dir}/curl"
    export PATH="${mock_dir}:${PATH}"

    cd "${CWD}" || exit 1

    # Force plain output regardless of caller environment. The script's
    # `echo "::error::..."` lines are recognized as annotations by GitHub
    # Actions runners; the tests assert on the plain-text substring form.
    unset GITHUB_ACTIONS

    # Sensible defaults for the 3 required env vars. Individual tests
    # override via `export VAR=...` before running the script.
    export DOCKERHUB_USERNAME="test-user"
    export DOCKERHUB_TOKEN="test-token"
    export TAG="release-candidate-12345-1"
    # REPO defaults to openemr/openemr in the script itself, so leave it
    # unset here to exercise that default path.
    unset REPO

    # Default mock behaviors — happy path.
    write_login_body_valid_jwt
    export MOCK_DELETE_HTTP_CODE="204"
    export MOCK_LOGIN_EXIT="0"
    export MOCK_DELETE_EXIT="0"
    # Fresh empty DELETE response body (204 has no body typically).
    : > "${MOCK_DELETE_RESPONSE_FILE}"
}

teardown_test_dir() {
    cd /
    # Restore PATH first so a subsequent shell command uses real binaries.
    export PATH="${PATH#"${CWD}/.curl-mock":}"
    rm -rf "${CWD}"
    unset MOCK_LOGIN_RESPONSE_FILE MOCK_DELETE_RESPONSE_FILE
    unset MOCK_LOGIN_EXIT MOCK_DELETE_EXIT MOCK_DELETE_HTTP_CODE
    unset DOCKERHUB_USERNAME DOCKERHUB_TOKEN TAG REPO
}

# Write a login response containing a valid-looking JWT. This is the
# default; tests exercising failure modes overwrite via the more
# targeted helpers below.
write_login_body_valid_jwt() {
    printf '%s' '{"token":"eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ0ZXN0In0.sig"}' \
        > "${MOCK_LOGIN_RESPONSE_FILE}"
}

# Write a login response where .token is JSON null. jq -r '.token' emits
# the literal string "null" for this shape; the script must treat that
# as an obtain-JWT failure.
write_login_body_null_token() {
    printf '%s' '{"token":null,"detail":"invalid credentials"}' \
        > "${MOCK_LOGIN_RESPONSE_FILE}"
}

# Write a login response where the .token field is absent entirely
# (e.g. Docker Hub returned an error envelope instead of a login
# response). jq -r '.token // ""' emits "" for this shape; the script
# must treat that as an obtain-JWT failure too.
write_login_body_missing_token() {
    printf '%s' '{"detail":"account is disabled"}' \
        > "${MOCK_LOGIN_RESPONSE_FILE}"
}

# Write an arbitrary body into the DELETE response fixture — used by
# tests that assert the script surfaces the body on unexpected HTTP.
write_delete_body() {
    printf '%s' "$1" > "${MOCK_DELETE_RESPONSE_FILE}"
}
