# BATS tests for .github/scripts/verify-oci-labels.sh
#
# Runs the script against a mock `docker` CLI (see helpers.bash +
# docker-mock.sh). Covers:
#   - all-good happy path (exit 0, success summary)
#   - each of the three value-check failure modes (revision / version /
#     created mismatch) -- verifies the Dockerfile-context hint is
#     emitted alongside the primary ::error:: line
#   - each of the four presence-check failure modes (title / source /
#     url / licenses missing) -- verifies the intact-LABEL-block hint
#   - the docker "<no value>" normalization (inspect returning literal
#     "<no value>" is treated as empty and triggers the presence check)
#   - multiple failures accumulate into a single exit 1 (no early-exit
#     hiding follow-on failures)
#   - required-env-var guards (script exits non-zero when any of the 4
#     inputs is unset)
#   - pull failure surfaces via set -e (exit non-zero, no fake-success)
#
# What's NOT covered
#   - the exact docker CLI wire format ("docker pull <ref>" arg count,
#     "docker inspect --format" arg ordering) -- the mock accepts the
#     shape the current script uses. If the script's docker invocations
#     ever diverge from that shape, the mock's parsing will need
#     updating in lockstep.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# --- happy path ---

@test "all labels correct -> exit 0 with success summary" {
    write_all_good_labels
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"✓ revision=master version=8.2.0 created=2026-01-15"* ]]
    [[ "${output}" == *"✓ title=OpenEMR"* ]]
    [[ "${output}" == *"✓ source=https://github.com/openemr/openemr"* ]]
    [[ "${output}" == *"✓ url=https://www.open-emr.org"* ]]
    [[ "${output}" == *"✓ licenses=GPL-3.0-or-later"* ]]
    # No error output on the happy path.
    [[ "${output}" != *"::error::"* ]]
}

# --- value-check failures (with Dockerfile-context hints) ---

@test "revision mismatch -> exit 1 with OPENEMR_VERSION hint" {
    write_labels \
        revision=wrong-branch \
        version=8.2.0 \
        created=2026-01-15 \
        title=OpenEMR source=x url=y licenses=z
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::revision label 'wrong-branch' doesn't match expected 'master'"* ]]
    [[ "${output}" == *"dropped the OPENEMR_VERSION ARG or hardcoded the LABEL value"* ]]
}

@test "version mismatch -> exit 1 with IMAGE_VERSION hint" {
    write_labels \
        revision=master \
        version=7.0.0 \
        created=2026-01-15 \
        title=OpenEMR source=x url=y licenses=z
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::version label '7.0.0' doesn't match expected '8.2.0' (parsed from openemr@master/version.php)"* ]]
    [[ "${output}" == *"dropped the IMAGE_VERSION ARG or hardcoded the LABEL value"* ]]
}

@test "created mismatch -> exit 1 with BUILD_DATE hint" {
    write_labels \
        revision=master \
        version=8.2.0 \
        created=1999-12-31 \
        title=OpenEMR source=x url=y licenses=z
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::created label '1999-12-31' doesn't match expected '2026-01-15'"* ]]
    [[ "${output}" == *"dropped the BUILD_DATE ARG or hardcoded the LABEL value"* ]]
}

# --- presence-check failures (with intact-LABEL-block hint) ---

@test "missing title label -> exit 1 with LABEL-block hint" {
    write_labels \
        revision=master version=8.2.0 created=2026-01-15 \
        title=ABSENT source=x url=y licenses=z
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Image is missing org.opencontainers.image.title label entirely"* ]]
    [[ "${output}" == *"LABEL block at the final stage is intact"* ]]
}

@test "missing source label -> exit 1" {
    write_labels \
        revision=master version=8.2.0 created=2026-01-15 \
        title=OpenEMR source=ABSENT url=y licenses=z
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Image is missing org.opencontainers.image.source label entirely"* ]]
}

@test "missing url label -> exit 1" {
    write_labels \
        revision=master version=8.2.0 created=2026-01-15 \
        title=OpenEMR source=x url=ABSENT licenses=z
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Image is missing org.opencontainers.image.url label entirely"* ]]
}

@test "missing licenses label -> exit 1" {
    write_labels \
        revision=master version=8.2.0 created=2026-01-15 \
        title=OpenEMR source=x url=y licenses=ABSENT
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Image is missing org.opencontainers.image.licenses label entirely"* ]]
}

# --- normalization of docker's "<no value>" sentinel ---

@test "docker '<no value>' sentinel treated as empty (revision comparison)" {
    # revision is absent from the fixture => docker mock echoes "<no value>".
    # The script's inspect_label helper must normalize that to "" so the
    # comparison surfaces as a mismatch rather than accidentally matching
    # some other "<no value>" string.
    write_labels \
        version=8.2.0 created=2026-01-15 \
        title=OpenEMR source=x url=y licenses=z
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    # The mismatch line shows an empty string, not "<no value>".
    [[ "${output}" == *"::error::revision label '' doesn't match expected 'master'"* ]]
    [[ "${output}" != *"<no value>"* ]]
}

@test "docker '<no value>' sentinel treated as empty (static-label presence)" {
    # title is absent from the fixture => docker mock echoes "<no value>".
    # -z must fire, triggering the "missing label entirely" error rather
    # than accepting the sentinel string as a valid non-empty value.
    write_labels \
        revision=master version=8.2.0 created=2026-01-15 \
        source=x url=y licenses=z
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::Image is missing org.opencontainers.image.title label entirely"* ]]
    [[ "${output}" != *"✓ title=<no value>"* ]]
}

# --- multiple failures accumulate ---

@test "multiple failures accumulate into a single exit 1" {
    # Wrong revision + missing title + wrong created: all 3 should show
    # up in output rather than the script bailing after the first
    # mismatch.
    write_labels \
        revision=nope \
        version=8.2.0 \
        created=1999-12-31 \
        title=ABSENT \
        source=x url=y licenses=z
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"revision label 'nope'"* ]]
    [[ "${output}" == *"created label '1999-12-31'"* ]]
    [[ "${output}" == *"Image is missing org.opencontainers.image.title label entirely"* ]]
}

# --- required env-var guards ---

@test "missing REF_TAG -> non-zero exit with clear error" {
    write_all_good_labels
    unset REF_TAG
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"REF_TAG"* ]]
}

@test "missing EXPECTED_REVISION -> non-zero exit" {
    write_all_good_labels
    unset EXPECTED_REVISION
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"EXPECTED_REVISION"* ]]
}

@test "missing EXPECTED_VERSION -> non-zero exit" {
    write_all_good_labels
    unset EXPECTED_VERSION
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"EXPECTED_VERSION"* ]]
}

@test "missing EXPECTED_BUILD_DATE -> non-zero exit" {
    write_all_good_labels
    unset EXPECTED_BUILD_DATE
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"EXPECTED_BUILD_DATE"* ]]
}

# --- pull failure ---

@test "docker pull failure -> non-zero exit (no fake success)" {
    # No labels fixture => mock's `docker pull` exits 1.
    # set -e in the script must propagate that up.
    simulate_pull_failure
    run bash "${VERIFY_OCI_LABELS_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" != *"✓ revision="* ]]  # summary line must NOT fire
}
