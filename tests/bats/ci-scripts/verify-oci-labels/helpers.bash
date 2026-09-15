# Helpers for BATS tests of .github/scripts/verify-oci-labels.sh.
#
# Loaded by verify.bats via `load 'helpers'`. Installs a `docker` mock on
# PATH (see docker-mock.sh for the contract) so the script under test
# never touches the real Docker CLI or Docker Hub.
#
# Pattern follows tests/bats/ci-scripts/validate-byte-identical/helpers.bash --
# standalone bash, no bats-support / bats-assert; plain [[ ]] asserts.

# Absolute path to the script under test, captured AT LOAD TIME (before
# any @test does `cd` into its temp working dir).
__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
VERIFY_OCI_LABELS_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/verify-oci-labels.sh"

# Fresh working dir + docker mock. Sets the following vars in the calling
# shell (BATS's setup() runs in the same shell as the @test):
#
#   CWD                        fresh temp dir; the test cd's into it
#   MOCK_DOCKER_LABELS_FILE    exported -- fixture the docker mock reads.
#                              Tests populate this via write_labels() to
#                              simulate different image label shapes.
#   PATH                       prepended with the docker mock dir.
setup_test_dir() {
    # `mktemp -d` (no `-t <template>`) — BusyBox's mktemp in the
    # bats/bats:1.13.0 Alpine image rejects the `-t` form that GNU
    # mktemp accepts.
    CWD=$(mktemp -d)

    export MOCK_DOCKER_LABELS_FILE="${CWD}/labels.txt"

    # Install the docker mock at the front of PATH.
    local mock_dir="${CWD}/.docker-mock"
    mkdir -p "${mock_dir}"
    cp "${__HELPERS_DIR}/docker-mock.sh" "${mock_dir}/docker"
    chmod +x "${mock_dir}/docker"
    export PATH="${mock_dir}:${PATH}"

    cd "${CWD}" || exit 1

    # Force plain output regardless of caller environment. The script's
    # `echo "::error::..."` lines are recognized as annotations by GitHub
    # Actions runners (which set GITHUB_ACTIONS=true globally); the tests
    # assert on the plain-text substring form, which is what the runner
    # collapses those lines to anyway when GITHUB_ACTIONS is unset. Not
    # strictly needed here (the script emits ::error:: unconditionally),
    # but mirrors the convention from the other ci-scripts BATS suites.
    unset GITHUB_ACTIONS

    # Sensible defaults for the 4 required env vars. Individual tests
    # override any subset via `export VAR=...` before running the script.
    export REF_TAG="openemr/openemr:test-tag"
    export EXPECTED_REVISION="master"
    export EXPECTED_VERSION="8.2.0"
    export EXPECTED_BUILD_DATE="2026-01-15"
}

teardown_test_dir() {
    cd /
    # Restore PATH first so a subsequent shell command uses real binaries.
    export PATH="${PATH#"${CWD}/.docker-mock":}"
    rm -rf "${CWD}"
    unset MOCK_DOCKER_LABELS_FILE
    unset REF_TAG EXPECTED_REVISION EXPECTED_VERSION EXPECTED_BUILD_DATE
}

# Write the labels-fixture file the docker mock reads. Each arg is a
# "<key>=<value>" pair (or "<key>=ABSENT" to simulate a missing label).
#
# Usage:
#   write_labels \
#       revision=master \
#       version=8.2.0 \
#       created=2026-01-15 \
#       title=OpenEMR \
#       source=https://github.com/openemr/openemr \
#       url=https://www.open-emr.org \
#       licenses=GPL-3.0-or-later
write_labels() {
    : > "${MOCK_DOCKER_LABELS_FILE}"
    for pair in "$@"; do
        echo "${pair}" >> "${MOCK_DOCKER_LABELS_FILE}"
    done
}

# Convenience: write a fully-valid label set matching the setup_test_dir
# defaults (EXPECTED_REVISION=master, EXPECTED_VERSION=8.2.0,
# EXPECTED_BUILD_DATE=2026-01-15). Tests that want to exercise a
# specific failure mode start from this and either override individual
# labels before calling the script, or overwrite via a fresh write_labels.
write_all_good_labels() {
    write_labels \
        revision=master \
        version=8.2.0 \
        created=2026-01-15 \
        title=OpenEMR \
        source=https://github.com/openemr/openemr \
        url=https://www.open-emr.org \
        licenses=GPL-3.0-or-later
}

# Delete the labels file so `docker pull` in the mock returns non-zero
# (simulates a manifest-not-found failure). Also causes subsequent
# `docker inspect` to fail loudly if the pull ever slips past the guard.
simulate_pull_failure() {
    rm -f "${MOCK_DOCKER_LABELS_FILE}"
}
