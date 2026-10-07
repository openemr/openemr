# Helpers for BATS tests of .github/scripts/lib/derive-from-version.sh.
#
# The lib is SOURCED by callers, not executed. Each test sources it
# in a subshell (via `run bash -c`) so the function definitions
# don't leak into the BATS process state.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
DERIVE_FROM_VERSION_LIB="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/lib/derive-from-version.sh"

setup_test_dir() {
    CWD=$(mktemp -d)
    cd "${CWD}" || exit 1
}

teardown_test_dir() {
    cd /
    rm -rf "${CWD}"
}

# Run `derive_from_version_sql_candidates <checkout_dir> [exclude]`
# in a subshell with the lib sourced. Captures stdout into $output
# and exit code into $status (standard BATS `run` semantics).
run_candidates() {
    local checkout_dir="$1"
    local exclude="${2:-}"
    run bash -c "source '${DERIVE_FROM_VERSION_LIB}' && derive_from_version_sql_candidates '${checkout_dir}' '${exclude}'"
}

# Build a release-tree fixture at ${CWD}/<name>/sql/ containing
# the given list of X_Y_Z-to-A_B_C filenames. Returns the fixture
# path on stdout.
#
# Args:
#   $1   Fixture name (becomes directory name).
#   $2+  Each additional arg is a filename shape (e.g. "8_1_0-to-8_2_0")
#        — the "_upgrade.sql" suffix is auto-appended.
write_sql_fixture() {
    local name="$1"
    shift
    local dir="${CWD}/${name}"
    mkdir -p "${dir}/sql"
    for stem in "$@"; do
        touch "${dir}/sql/${stem}_upgrade.sql"
    done
    echo "${dir}"
}

# Build a fixture with the given sql files AND an "unrelated" file
# that should NOT be picked up by the enumeration (to pin that the
# glob is scoped correctly).
write_sql_fixture_with_distractors() {
    local name="$1"
    local dir="${CWD}/${name}"
    mkdir -p "${dir}/sql"
    touch "${dir}/sql/8_1_1-to-8_2_0_upgrade.sql"
    # Distractors: files that look sql-ish but aren't upgrade files
    touch "${dir}/sql/database.sql"
    touch "${dir}/sql/some-random.sql"
    touch "${dir}/sql/currentLanguage_utf8.sql"
    echo "${dir}"
}
