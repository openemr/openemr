# BATS tests for .github/scripts/verify-dockerhub-tag-exists.sh
#
# Runs against a mock `curl` (see helpers.bash + curl-mock.sh). Covers:
#   - HTTP 200 happy path (exit 0)
#   - HTTP 404 (exit 1 with recovery-paths error)
#   - unexpected HTTP status (exit 1 with response-body dump)
#   - curl-level failure (exit 1 with connection-error message)
#   - required env-var guard (TAG unset)
#   - default REPO applied (openemr/openemr) + REPO override respected
#   - URL construction: TAG appears in the printed GET line

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

@test "HTTP 200 -> exit 0 with success message" {
    MOCK_CURL_HTTP_CODE="200" \
        run bash "${VERIFY_DOCKERHUB_TAG_EXISTS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"openemr/openemr:release-candidate-12345-1 exists on Docker Hub"* ]]
    [[ "${output}" != *"::error::"* ]]
}

@test "HTTP 404 -> exit 1 with recovery-paths error" {
    MOCK_CURL_HTTP_CODE="404" \
    MOCK_CURL_BODY='{"message":"tag not found"}' \
        run bash "${VERIFY_DOCKERHUB_TAG_EXISTS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Tag openemr/openemr:release-candidate-12345-1 not found on Docker Hub (HTTP 404)"* ]]
    [[ "${output}" == *"::error::"*"cleaned up"* ]]
    [[ "${output}" == *"::error::Recovery paths"* ]]
}

@test "HTTP 500 -> exit 1 with unexpected-status error + body dump" {
    MOCK_CURL_HTTP_CODE="500" \
    MOCK_CURL_BODY='{"error":"internal server error"}' \
        run bash "${VERIFY_DOCKERHUB_TAG_EXISTS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Unexpected HTTP 500 from Docker Hub v2 API"* ]]
    [[ "${output}" == *"internal server error"* ]]
}

@test "HTTP 429 (rate limit) -> exit 1 with unexpected-status error" {
    MOCK_CURL_HTTP_CODE="429" \
    MOCK_CURL_BODY='{"error":"too many requests"}' \
        run bash "${VERIFY_DOCKERHUB_TAG_EXISTS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Unexpected HTTP 429"* ]]
}

@test "curl-level failure (exit 6 -- couldn't resolve host) -> exit 1 with connection error" {
    MOCK_CURL_EXIT="6" \
        run bash "${VERIFY_DOCKERHUB_TAG_EXISTS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Docker Hub request failed before receiving an HTTP response"* ]]
    [[ "${output}" == *"curl exit: 6"* ]]
}

@test "curl-level failure (exit 28 -- operation timeout) -> exit 1" {
    MOCK_CURL_EXIT="28" \
        run bash "${VERIFY_DOCKERHUB_TAG_EXISTS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"curl exit: 28"* ]]
}

@test "missing TAG -> non-zero exit with clear error" {
    unset TAG
    run bash "${VERIFY_DOCKERHUB_TAG_EXISTS_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"TAG"* ]]
}

@test "REPO override respected in success message" {
    REPO="someorg/somerepo" \
    TAG="v1.2.3" \
    MOCK_CURL_HTTP_CODE="200" \
        run bash "${VERIFY_DOCKERHUB_TAG_EXISTS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"someorg/somerepo:v1.2.3"* ]]
    # URL in the GET line should include the overridden REPO.
    [[ "${output}" == *"hub.docker.com/v2/repositories/someorg/somerepo/tags/v1.2.3/"* ]]
}

@test "default REPO is openemr/openemr" {
    MOCK_CURL_HTTP_CODE="200" \
        run bash "${VERIFY_DOCKERHUB_TAG_EXISTS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"hub.docker.com/v2/repositories/openemr/openemr/tags/"* ]]
}
