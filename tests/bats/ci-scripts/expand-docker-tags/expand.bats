# BATS tests for .github/scripts/expand-docker-tags.sh
#
# Pure text-transform script (comma-separated tag labels in, newline-
# separated `openemr/openemr:<tag>` refs out). No mocks needed. Covers:
#   - version-number tag -> gets a dated sibling
#   - floating tag (dev/next/latest/manual-test) -> no dated sibling
#   - multiple comma-separated tags -> mixed expansion
#   - whitespace tolerance around commas and inside entries
#   - comma-only / empty-entry input -> empty output (not an error)
#   - required env-var guards

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

@test "version-number tag gets dated sibling" {
    INPUT_TAGS="8.2.0" BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "openemr/openemr:8.2.0"$'\n'"openemr/openemr:8.2.0-2026-07-29" ]]
}

@test "version-number tag with 2-segment version (e.g. 8.2) still gets dated sibling" {
    INPUT_TAGS="8.2" BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "openemr/openemr:8.2"$'\n'"openemr/openemr:8.2-2026-07-29" ]]
}

@test "version-number tag with 4-segment version (e.g. 8.2.0.1) still gets dated sibling" {
    INPUT_TAGS="8.2.0.1" BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "openemr/openemr:8.2.0.1"$'\n'"openemr/openemr:8.2.0.1-2026-07-29" ]]
}

@test "floating tag 'latest' gets no dated sibling" {
    INPUT_TAGS="latest" BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "openemr/openemr:latest" ]]
}

@test "floating tag 'dev' gets no dated sibling" {
    INPUT_TAGS="dev" BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "openemr/openemr:dev" ]]
}

@test "floating tag 'next' gets no dated sibling" {
    INPUT_TAGS="next" BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "openemr/openemr:next" ]]
}

@test "floating tag 'manual-test' gets no dated sibling" {
    INPUT_TAGS="manual-test" BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "openemr/openemr:manual-test" ]]
}

@test "single-segment digit tag (e.g. '8') is NOT treated as version number (no dated sibling)" {
    # Regex requires at least one dot -- "8" alone is a floating-shaped tag.
    INPUT_TAGS="8" BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "openemr/openemr:8" ]]
}

@test "multiple comma-separated tags: version + floating mixed" {
    INPUT_TAGS="8.2.0,latest" BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    expected="openemr/openemr:8.2.0"$'\n'"openemr/openemr:8.2.0-2026-07-29"$'\n'"openemr/openemr:latest"
    [[ "${output}" == "${expected}" ]]
}

@test "multiple comma-separated tags: two floatings" {
    INPUT_TAGS="dev,next" BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    expected="openemr/openemr:dev"$'\n'"openemr/openemr:next"
    [[ "${output}" == "${expected}" ]]
}

@test "whitespace tolerance: spaces around commas and inside entries stripped" {
    INPUT_TAGS="  8.2.0 , latest " BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    expected="openemr/openemr:8.2.0"$'\n'"openemr/openemr:8.2.0-2026-07-29"$'\n'"openemr/openemr:latest"
    [[ "${output}" == "${expected}" ]]
}

@test "comma-only input produces empty output (not an error)" {
    INPUT_TAGS="," BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ -z "${output}" ]]
}

@test "doubled commas and trailing comma tolerated (skipped as empty entries)" {
    INPUT_TAGS="8.2.0,,latest," BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    expected="openemr/openemr:8.2.0"$'\n'"openemr/openemr:8.2.0-2026-07-29"$'\n'"openemr/openemr:latest"
    [[ "${output}" == "${expected}" ]]
}

@test "empty INPUT_TAGS input produces empty output" {
    INPUT_TAGS="" BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    # Note: INPUT_TAGS being unset would trigger the required-env guard;
    # INPUT_TAGS set to empty string is a distinct case ("no tags to expand").
    # The `:?` parameter expansion treats unset AND null the same by default,
    # so empty string DOES trigger the guard. That's intentional -- an empty
    # input list is a caller bug, not a valid "expand nothing" request.
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"INPUT_TAGS"* ]]
}

@test "unset INPUT_TAGS -> non-zero exit with clear error" {
    unset INPUT_TAGS
    BUILD_DATE="2026-07-29" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"INPUT_TAGS"* ]]
}

@test "unset BUILD_DATE -> non-zero exit with clear error" {
    unset BUILD_DATE
    INPUT_TAGS="8.2.0" run bash "${EXPAND_DOCKER_TAGS_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"BUILD_DATE"* ]]
}
