# BATS tests for .github/scripts/parse-artifact-version.sh.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# ==== Happy paths -- OCI label is the primary source ====

@test "OCI label clean X.Y.Z -> resolved=8.4.1" {
    export OCI_VERSION_LABEL="8.4.1"
    export FALLBACK_VERSION=""
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"resolved=8.4.1"* ]]
    # Reader log confirms OCI-first path.
    [[ "${output}" == *"OCI label"* ]]
    [[ "${output}" == *"8.4.1"* ]]
}

@test "OCI label with -dev suffix -> strip to X.Y.Z prefix (master build case)" {
    # master's docker images ship OCI label like '8.5.0-dev'; version-
    # check tests compare X.Y.Z prefix only, so strip the suffix.
    export OCI_VERSION_LABEL="8.5.0-dev"
    export FALLBACK_VERSION=""
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"resolved=8.5.0"* ]]
}

@test "OCI label with build metadata -> strip to X.Y.Z prefix" {
    # Defensive: even though release pipeline doesn't currently emit
    # semver build metadata, the prefix-only match handles any suffix.
    export OCI_VERSION_LABEL="8.4.1+build.123"
    export FALLBACK_VERSION=""
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"resolved=8.4.1"* ]]
}

@test "OCI label has 4 dotted components -> take first 3 (X.Y.Z prefix only)" {
    # Ancient 8.0.0.3 tag is 4 components -- version-check compares
    # X.Y.Z only, so consume just the 3-part prefix.
    export OCI_VERSION_LABEL="8.0.0.3"
    export FALLBACK_VERSION=""
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"resolved=8.0.0"* ]]
}

# ==== Fallback path -- version.php read ====

@test "OCI empty + fallback populated -> resolved=fallback (pr-built case)" {
    # build-image job doesn't pass --build-arg IMAGE_VERSION, so the
    # OCI label is empty. Fallback comes from read-version-php.php.
    export OCI_VERSION_LABEL=""
    export FALLBACK_VERSION="8.5.0"
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"resolved=8.5.0"* ]]
    [[ "${output}" == *"version.php fallback"* ]]
}

@test "OCI malformed (no X.Y.Z prefix) + fallback populated -> resolved=fallback" {
    # Defensive: garbage OCI shouldn't win over a clean fallback.
    export OCI_VERSION_LABEL="not-a-version"
    export FALLBACK_VERSION="8.4.1"
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"resolved=8.4.1"* ]]
}

@test "OCI empty + fallback empty -> exit 1 (both sources failed)" {
    export OCI_VERSION_LABEL=""
    export FALLBACK_VERSION=""
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"neither OCI label nor version.php fallback"* ]]
}

@test "OCI empty + FALLBACK env unset (not just empty) -> exit 1" {
    export OCI_VERSION_LABEL=""
    unset FALLBACK_VERSION
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"neither OCI label nor version.php fallback"* ]]
}

@test "OCI empty + FALLBACK garbage -> exit 1 (fallback not shape-validated on entry, so garbage fails at output validation)" {
    # Script doesn't pre-validate FALLBACK -- it accepts anything and
    # runs the final X.Y.Z shape check as the gate. Garbage fallback
    # falls through OCI-empty and fails the final shape validation.
    export OCI_VERSION_LABEL=""
    export FALLBACK_VERSION="not-a-version"
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"does not match X.Y.Z shape"* ]]
}

# ==== Env absent (both) ====

@test "both OCI and FALLBACK env completely unset -> exit 1" {
    unset OCI_VERSION_LABEL FALLBACK_VERSION
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"neither OCI label nor version.php fallback"* ]]
}

# ==== Precedence: OCI wins even when FALLBACK is also populated ====

@test "both OCI and fallback populated -> OCI wins (cross-signal preserved)" {
    # This is the shipped/rel-branch build case. When both are
    # available, OCI is the authoritative expected_version -- version.
    # php becomes one of the 3 signals to cross-check AGAINST (via the
    # About-page render test), not the source of expected_version.
    export OCI_VERSION_LABEL="8.4.1"
    export FALLBACK_VERSION="9.9.9"
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"resolved=8.4.1"* ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" != *"resolved=9.9.9"* ]]
}

# ==== Output-shape guards ====

@test "GITHUB_OUTPUT unset -> falls back to stdout emission" {
    unset GITHUB_OUTPUT
    export OCI_VERSION_LABEL="8.4.1"
    export FALLBACK_VERSION=""
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"resolved=8.4.1"* ]]
}

# ==== Realistic shapes seen in production ====

@test "realistic: latest tag ships '8.4.1' OCI label -> resolved=8.4.1" {
    export OCI_VERSION_LABEL="8.4.1"
    export FALLBACK_VERSION=""
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"resolved=8.4.1"* ]]
}

@test "realistic: dev tag ships '8.5.0-dev' OCI label -> resolved=8.5.0" {
    export OCI_VERSION_LABEL="8.5.0-dev"
    export FALLBACK_VERSION=""
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"resolved=8.5.0"* ]]
}

@test "realistic: pr-built has empty OCI label, version.php reports master's dev version -> resolved=8.5.0" {
    export OCI_VERSION_LABEL=""
    export FALLBACK_VERSION="8.5.0"
    run bash "${PARSE_ARTIFACT_VERSION_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "$(cat "${GITHUB_OUTPUT}")" == *"resolved=8.5.0"* ]]
}
