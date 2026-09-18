# Helpers for BATS tests of tests/Acceptance/bin/lib/extract-zip.sh.
#
# The library is a sourced bash function, not an env-invoked script, so
# tests need a small wrapper that sources the library and calls the
# function under `run`. See run_extract() below.
#
# mktemp portability: this helpers file uses `mktemp -d` WITHOUT `-t
# <template>`. BusyBox's mktemp (Alpine's bats/bats:1.13.0 base image)
# rejects the `-t <template>` form that GNU coreutils accepts, and this
# suite is expected to run in that container too. The extract-zip.sh
# library itself uses `mktemp -d -t <template>` — that's fine because
# it runs on GHA ubuntu-24.04 runners (GNU coreutils), not in the BATS
# container.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034  # referenced from .bats files
EXTRACT_ZIP_LIB="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/tests/Acceptance/bin/lib/extract-zip.sh"

setup_test_dir() {
    # -d only; NO `-t <template>` — BusyBox mktemp rejects that form.
    TEST_DIR="$(mktemp -d)"
    ZIP_DIR="${TEST_DIR}/zips"
    DEST_DIR="${TEST_DIR}/dest"
    STAGING_DIR="${TEST_DIR}/staging"
    mkdir -p "${ZIP_DIR}" "${DEST_DIR}" "${STAGING_DIR}"
}

teardown_test_dir() {
    cd /
    if [[ -n "${TEST_DIR:-}" && -d "${TEST_DIR}" ]]; then
        rm -rf "${TEST_DIR}"
    fi
    unset TEST_DIR ZIP_DIR DEST_DIR STAGING_DIR
}

# Build a .zip at ${ZIP_DIR}/${1}.zip whose top-level entries are the
# staged directory tree passed as ${2}... (each entry is a path
# relative to ${STAGING_DIR} that must already exist). The wrapper dir
# structure is caller's responsibility — this helper just zips whatever
# is at ${STAGING_DIR}/${entry}.
#
# Example:
#   make_zip openemr-8.2.0 openemr-8.2.0
#   → produces openemr-8.2.0.zip with a single top-level `openemr-8.2.0/`
make_zip() {
    local zip_name="$1"
    shift
    (
        cd "${STAGING_DIR}"
        # -q quiet; -r recurse into any dirs; explicit path list rather
        # than `*` so callers get deterministic top-level entries.
        zip -qr "${ZIP_DIR}/${zip_name}.zip" "$@"
    )
}

# Wrapper: source the library, run the function under `run`. Callers
# pass all four positional args exactly as the production callers do.
run_extract() {
    run bash -c "source '${EXTRACT_ZIP_LIB}'; extract_zip_flattening_single_top_level_dir \"\$@\"" \
        _ "$@"
}
