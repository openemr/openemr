# BATS tests for .github/scripts/dockerhub-delete-tag.sh
#
# Runs the script against a mock `curl` (see helpers.bash + curl-mock.sh).
# Covers the failure modes called out in the Phase 10e coverage audit:
#   - JWT ok / DELETE 204                    (happy path)
#   - JWT null (login response .token=null)  (JWT-parse failure)
#   - JWT missing (login response has no .token) (JWT-parse failure)
#   - DELETE 404                              (already-gone → success)
#   - DELETE 401                              (auth rejected → failure)
#   - DELETE 5xx                              (Docker Hub error → failure)
#   - curl connect-fail on login              (network error, no response)
#   - curl connect-fail on delete             (network error, no response)
#
# What's NOT covered
#   - the exact wire format of curl flags (--connect-timeout ordering,
#     etc.) — the mock accepts the shape the current script uses.
#   - jq's behavior on invalid JSON — jq itself is trusted; we exercise
#     the two JSON shapes the script actually differentiates
#     (null-token, missing-token).

load 'helpers'

# Install jq if missing. The script under test uses jq to build the
# login body (safe JSON escaping) and to parse the login response for
# .token. GHA ubuntu-24.04 runners ship jq pre-installed. The
# bats/bats:1.13.0 Alpine base image (natural way to iterate locally)
# does not — same setup_file pattern extract-zip uses for `zip`. Runs
# once per test file.
setup_file() {
    if ! command -v jq >/dev/null 2>&1; then
        if command -v apk >/dev/null 2>&1; then
            apk add --no-cache jq >/dev/null 2>&1 || true
        elif command -v apt-get >/dev/null 2>&1; then
            apt-get update >/dev/null 2>&1 && apt-get install -y jq >/dev/null 2>&1 || true
        fi
    fi
    if ! command -v jq >/dev/null 2>&1; then
        echo "# skip-reason: 'jq' binary not installable in this environment" >&3
        skip "jq binary required to build/parse login JSON"
    fi
}

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# --- happy path ---

@test "valid JWT + DELETE 204 -> exit 0 with success message" {
    # Defaults from setup_test_dir already configure this. Just run.
    run bash "${DOCKERHUB_DELETE_TAG_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"POST https://hub.docker.com/v2/users/login/"* ]]
    [[ "${output}" == *"DELETE https://hub.docker.com/v2/repositories/openemr/openemr/tags/release-candidate-12345-1/"* ]]
    [[ "${output}" == *"cleanup OK: openemr/openemr:release-candidate-12345-1 deleted (HTTP 204)"* ]]
    [[ "${output}" != *"::error::"* ]]
}

# --- JWT failure modes ---

@test "login response has .token=null -> exit 1 with clear error" {
    write_login_body_null_token
    run bash "${DOCKERHUB_DELETE_TAG_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Could not obtain Docker Hub JWT"* ]]
    # Response body is surfaced so the operator can diagnose (e.g.
    # "invalid credentials" would show up here).
    [[ "${output}" == *"invalid credentials"* ]]
    # Script must NOT have proceeded to the DELETE call.
    [[ "${output}" != *"DELETE https://hub.docker.com"* ]]
}

@test "login response missing .token field -> exit 1 with clear error" {
    write_login_body_missing_token
    run bash "${DOCKERHUB_DELETE_TAG_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Could not obtain Docker Hub JWT"* ]]
    [[ "${output}" == *"account is disabled"* ]]
    [[ "${output}" != *"DELETE https://hub.docker.com"* ]]
}

# --- DELETE status-code interpretation ---

@test "DELETE 404 -> exit 0 (already-gone counts as success)" {
    export MOCK_DELETE_HTTP_CODE="404"
    run bash "${DOCKERHUB_DELETE_TAG_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"cleanup OK: openemr/openemr:release-candidate-12345-1 already gone (HTTP 404)"* ]]
    [[ "${output}" != *"::error::"* ]]
}

@test "DELETE 401 -> exit 1 with response body surfaced" {
    export MOCK_DELETE_HTTP_CODE="401"
    write_delete_body '{"detail":"authentication credentials were not provided"}'
    run bash "${DOCKERHUB_DELETE_TAG_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Docker Hub tag delete returned HTTP 401"* ]]
    [[ "${output}" == *"authentication credentials were not provided"* ]]
}

@test "DELETE 500 -> exit 1 with response body surfaced" {
    export MOCK_DELETE_HTTP_CODE="500"
    write_delete_body '{"detail":"internal server error"}'
    run bash "${DOCKERHUB_DELETE_TAG_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Docker Hub tag delete returned HTTP 500"* ]]
    [[ "${output}" == *"internal server error"* ]]
}

# --- curl network-level failures ---

@test "curl connect-fail during login -> exit 1 with network error" {
    # curl exit 6 = "Couldn't resolve host". Any non-zero exit before an
    # HTTP response is received must be surfaced.
    export MOCK_LOGIN_EXIT="6"
    run bash "${DOCKERHUB_DELETE_TAG_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Docker Hub login request failed before receiving an HTTP response"* ]]
    [[ "${output}" == *"curl exit: 6"* ]]
    # Never reached the DELETE.
    [[ "${output}" != *"DELETE https://hub.docker.com"* ]]
}

@test "curl connect-fail during delete -> exit 1 with network error" {
    # Login succeeds; DELETE hits a network error (e.g. TLS handshake
    # failure post-login, curl exit 35).
    export MOCK_DELETE_EXIT="35"
    run bash "${DOCKERHUB_DELETE_TAG_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Docker Hub DELETE request failed before receiving an HTTP response"* ]]
    [[ "${output}" == *"curl exit: 35"* ]]
}
