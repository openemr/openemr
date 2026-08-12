# BATS tests for tests/Acceptance/bin/lib/extract-zip.sh.
#
# Covers extract_zip_flattening_single_top_level_dir — the shared helper
# sourced by boot-package.sh + upgrade-package.sh. Behavior contract:
#   - single top-level directory in the zip is flattened into dest
#   - contents (including dotfiles) all land in dest
#   - multi top-level entries -> hard error (not silent merge)
#   - zero top-level entries (empty zip) -> hard error
#   - top-level file (not dir) -> hard error
#   - missing zip file -> unzip error propagates (set -e in caller)
#   - unzip not on PATH -> ::error:: with caller-supplied context noun
#   - error_context noun appears verbatim in the unzip-missing message
#     (so boot-package's "--local-zip" and upgrade-package's
#     "--to-local-zip" wording stays distinct)

load 'helpers'

# Install the `zip` binary if missing. `unzip` ships in bats/bats:1.13.0's
# Alpine base image but `zip` does not, and this suite builds fixture
# archives with `zip -q` in helpers.bash::make_zip. GHA ubuntu-24.04
# runners already have both. Runs once per test file.
setup_file() {
    if ! command -v zip >/dev/null 2>&1; then
        if command -v apk >/dev/null 2>&1; then
            apk add --no-cache zip >/dev/null 2>&1 || true
        elif command -v apt-get >/dev/null 2>&1; then
            apt-get update >/dev/null 2>&1 && apt-get install -y zip >/dev/null 2>&1 || true
        fi
    fi
    if ! command -v zip >/dev/null 2>&1; then
        echo "# skip-reason: 'zip' binary not installable in this environment" >&3
        skip "zip binary required to build fixture archives"
    fi
}

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

@test "single top-level dir: contents flattened into dest" {
    mkdir -p "${STAGING_DIR}/openemr-8.2.0/interface"
    echo "index" >"${STAGING_DIR}/openemr-8.2.0/index.php"
    echo "iface" >"${STAGING_DIR}/openemr-8.2.0/interface/main.php"
    make_zip openemr-8.2.0 openemr-8.2.0

    run_extract "${ZIP_DIR}/openemr-8.2.0.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -eq 0 ]]
    [[ -f "${DEST_DIR}/index.php" ]]
    [[ -f "${DEST_DIR}/interface/main.php" ]]
    [[ "$(cat "${DEST_DIR}/index.php")" == "index" ]]
    # Wrapper dir itself must NOT appear inside dest — that would mean
    # the flattening didn't happen and callers would end up with a
    # doubly-nested webroot.
    [[ ! -d "${DEST_DIR}/openemr-8.2.0" ]]
}

@test "single top-level dir: dotfiles also flattened (shopt -s dotglob)" {
    mkdir -p "${STAGING_DIR}/openemr-8.2.0"
    echo "htaccess" >"${STAGING_DIR}/openemr-8.2.0/.htaccess"
    echo "index" >"${STAGING_DIR}/openemr-8.2.0/index.php"
    make_zip openemr-8.2.0 openemr-8.2.0

    run_extract "${ZIP_DIR}/openemr-8.2.0.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -eq 0 ]]
    [[ -f "${DEST_DIR}/.htaccess" ]]
    [[ -f "${DEST_DIR}/index.php" ]]
}

@test "single top-level dir: nested directory structure preserved" {
    mkdir -p "${STAGING_DIR}/openemr-8.2.0/sites/default/documents"
    echo "config" >"${STAGING_DIR}/openemr-8.2.0/sites/default/sqlconf.php"
    make_zip openemr-8.2.0 openemr-8.2.0

    run_extract "${ZIP_DIR}/openemr-8.2.0.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -eq 0 ]]
    [[ -d "${DEST_DIR}/sites/default/documents" ]]
    [[ -f "${DEST_DIR}/sites/default/sqlconf.php" ]]
}

@test "multi top-level entries: rejected with clear error" {
    mkdir -p "${STAGING_DIR}/openemr-8.2.0"
    mkdir -p "${STAGING_DIR}/rogue-dir"
    echo "x" >"${STAGING_DIR}/openemr-8.2.0/index.php"
    echo "y" >"${STAGING_DIR}/rogue-dir/oops.php"
    make_zip mixed openemr-8.2.0 rogue-dir

    run_extract "${ZIP_DIR}/mixed.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"expected exactly one top-level directory"* ]]
    [[ "${output}" == *"found 2"* ]]
    # dest must stay empty on failure — no partial merge
    [[ -z "$(ls -A "${DEST_DIR}")" ]]
}

@test "single top-level dir is empty: succeeds silently, dest stays empty" {
    # Regression guard for the nullglob-off-during-mv bug: with nullglob
    # disabled between the root check and the mv, an empty top-level dir
    # would expand `${zip_roots[0]}/*` to a literal `*` and mv would fail
    # with "cannot stat '.../\\*': No such file or directory". Keeping
    # nullglob on through the mv turns that into a no-op (dest stays
    # empty — callers detect empty content via their own downstream
    # presence checks).
    mkdir -p "${STAGING_DIR}/openemr-8.2.0"
    make_zip empty-openemr openemr-8.2.0

    run_extract "${ZIP_DIR}/empty-openemr.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -eq 0 ]]
    [[ -z "$(ls -A "${DEST_DIR}")" ]]
}

@test "multi top-level entries with dot-prefixed sibling: rejected (nullglob+dotglob)" {
    # Regression guard for the nullglob-alone bug: without `dotglob`,
    # the single-root check ignored dot-prefixed top-level entries so
    # `openemr-.../` + `.metadata` would pass with found=1 and
    # `.metadata` would be silently dropped by the wrapper flatten.
    mkdir -p "${STAGING_DIR}/openemr-8.2.0"
    echo "x" >"${STAGING_DIR}/openemr-8.2.0/index.php"
    echo "meta" >"${STAGING_DIR}/.metadata"
    make_zip with-dotfile openemr-8.2.0 .metadata

    run_extract "${ZIP_DIR}/with-dotfile.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"expected exactly one top-level directory"* ]]
    [[ "${output}" == *"found 2"* ]]
    [[ -z "$(ls -A "${DEST_DIR}")" ]]
}

@test "top-level file (not directory) rejected" {
    # A zip containing a single top-level file (rather than a
    # directory) should fail the `-d "${zip_roots[0]}"` guard.
    echo "loose" >"${STAGING_DIR}/loose-file.txt"
    make_zip loose loose-file.txt

    run_extract "${ZIP_DIR}/loose.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"expected exactly one top-level directory"* ]]
    [[ "${output}" == *"found 1"* ]]
}

@test "empty zip rejected (zero top-level entries)" {
    # `zip` refuses to create a truly empty archive, so build one with
    # printf + zip stdin trickery: create with a dummy entry then
    # delete it, leaving a 0-entry archive.
    echo "x" >"${STAGING_DIR}/temp"
    (cd "${STAGING_DIR}" && zip -q "${ZIP_DIR}/empty.zip" temp && zip -qd "${ZIP_DIR}/empty.zip" temp)

    run_extract "${ZIP_DIR}/empty.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"expected exactly one top-level directory"* ]]
    [[ "${output}" == *"found 0"* ]]
}

@test "missing zip file: unzip surfaces failure, extraction aborts" {
    run_extract "${ZIP_DIR}/does-not-exist.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -ne 0 ]]
    # dest stays empty because the mv never runs
    [[ -z "$(ls -A "${DEST_DIR}")" ]]
}

@test "error_context noun interpolated into unzip-missing error" {
    # PATH stripped so `command -v unzip` fails; assert the
    # caller-supplied noun appears verbatim in the diagnostic.
    mkdir -p "${STAGING_DIR}/openemr-8.2.0"
    echo "x" >"${STAGING_DIR}/openemr-8.2.0/index.php"
    make_zip openemr-8.2.0 openemr-8.2.0

    # Use absolute path to bash — PATH="" would otherwise prevent the
    # shell binary itself from being found by `run`, masking the guard
    # we're actually trying to exercise (unzip missing).
    local bash_bin
    bash_bin="$(command -v bash)"
    PATH="" run "${bash_bin}" -c \
        "source '${EXTRACT_ZIP_LIB}'; extract_zip_flattening_single_top_level_dir \"\$@\"" \
        _ "${ZIP_DIR}/openemr-8.2.0.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" \
        "--local-zip / zip extraction"

    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"unzip is required for --local-zip / zip extraction"* ]]
    [[ "${output}" == *"was not found on PATH"* ]]
}

@test "error_context noun distinguishes boot-package vs upgrade-package callers" {
    # Same guard, different noun — proves the parameterization actually
    # flows through (regression guard: swap the source-of-truth from a
    # hard-coded string to a $4 parameter without breaking either
    # caller's error message).
    mkdir -p "${STAGING_DIR}/openemr-8.2.0"
    echo "x" >"${STAGING_DIR}/openemr-8.2.0/index.php"
    make_zip openemr-8.2.0 openemr-8.2.0

    # Use absolute path to bash — PATH="" would otherwise prevent the
    # shell binary itself from being found by `run`, masking the guard
    # we're actually trying to exercise (unzip missing).
    local bash_bin
    bash_bin="$(command -v bash)"
    PATH="" run "${bash_bin}" -c \
        "source '${EXTRACT_ZIP_LIB}'; extract_zip_flattening_single_top_level_dir \"\$@\"" \
        _ "${ZIP_DIR}/openemr-8.2.0.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" \
        "--to-local-zip / zip extraction"

    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"unzip is required for --to-local-zip / zip extraction"* ]]
}

@test "error message includes zip path (for grep-in-CI-logs debuggability)" {
    mkdir -p "${STAGING_DIR}/dirA" "${STAGING_DIR}/dirB"
    echo "x" >"${STAGING_DIR}/dirA/f"
    echo "y" >"${STAGING_DIR}/dirB/f"
    make_zip badzip dirA dirB

    run_extract "${ZIP_DIR}/badzip.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"${ZIP_DIR}/badzip.zip"* ]]
}

@test "dest dir need not be empty pre-call, but wrapper contents move into it" {
    # Callers (boot-package.sh, upgrade-package.sh) mkdir -p the dest
    # before invoking. Nothing in this helper's contract says the dest
    # must be empty — a leftover file in dest should coexist alongside
    # the extracted tree.
    mkdir -p "${STAGING_DIR}/openemr-8.2.0"
    echo "extracted" >"${STAGING_DIR}/openemr-8.2.0/index.php"
    make_zip openemr-8.2.0 openemr-8.2.0
    echo "preexisting" >"${DEST_DIR}/already-here.txt"

    run_extract "${ZIP_DIR}/openemr-8.2.0.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -eq 0 ]]
    [[ -f "${DEST_DIR}/index.php" ]]
    [[ -f "${DEST_DIR}/already-here.txt" ]]
}

@test "scratch dir cleaned up on success (EXIT trap fires)" {
    mkdir -p "${STAGING_DIR}/openemr-8.2.0"
    echo "x" >"${STAGING_DIR}/openemr-8.2.0/index.php"
    make_zip openemr-8.2.0 openemr-8.2.0

    # Snapshot /tmp entries matching our scratch-template prefix before
    # + after; the trap should leave no residue.
    local before_count
    before_count="$(find /tmp -maxdepth 1 -name 'extract-zip-cleanup-success.*' 2>/dev/null | wc -l)"

    run_extract "${ZIP_DIR}/openemr-8.2.0.zip" "${DEST_DIR}" "extract-zip-cleanup-success.XXXXXX" "test context"

    [[ ${status} -eq 0 ]]
    local after_count
    after_count="$(find /tmp -maxdepth 1 -name 'extract-zip-cleanup-success.*' 2>/dev/null | wc -l)"
    [[ "${after_count}" -eq "${before_count}" ]]
}

@test "scratch dir cleaned up on failure (EXIT trap fires on multi-top-level rejection)" {
    mkdir -p "${STAGING_DIR}/dirA" "${STAGING_DIR}/dirB"
    echo "x" >"${STAGING_DIR}/dirA/f"
    echo "y" >"${STAGING_DIR}/dirB/f"
    make_zip mixed dirA dirB

    local before_count
    before_count="$(find /tmp -maxdepth 1 -name 'extract-zip-cleanup-failure.*' 2>/dev/null | wc -l)"

    run_extract "${ZIP_DIR}/mixed.zip" "${DEST_DIR}" "extract-zip-cleanup-failure.XXXXXX" "test context"

    [[ ${status} -ne 0 ]]
    local after_count
    after_count="$(find /tmp -maxdepth 1 -name 'extract-zip-cleanup-failure.*' 2>/dev/null | wc -l)"
    [[ "${after_count}" -eq "${before_count}" ]]
}

@test "wrapper dir name doesn't have to match zip filename (any single top-level dir works)" {
    # The single-top-level-dir enforcement doesn't care what the dir is
    # NAMED — it just requires exactly one. Confirms we're not
    # accidentally checking for a specific `openemr-<version>/` string.
    mkdir -p "${STAGING_DIR}/arbitrary-name"
    echo "x" >"${STAGING_DIR}/arbitrary-name/marker"
    make_zip weird arbitrary-name

    run_extract "${ZIP_DIR}/weird.zip" "${DEST_DIR}" "extract-zip-test.XXXXXX" "test context"

    [[ ${status} -eq 0 ]]
    [[ -f "${DEST_DIR}/marker" ]]
    [[ ! -d "${DEST_DIR}/arbitrary-name" ]]
}

@test "caller runs under set -euo pipefail with default shopts: extract still succeeds" {
    # Regression guard for the shopt-p-under-set-e bug: production
    # callers (boot-package.sh, upgrade-package.sh) source this helper
    # under `set -euo pipefail` with nullglob + dotglob at their bash-
    # default OFF state. The helper's `shopt_saved="$(shopt -p nullglob
    # dotglob)"` capture step used to fail silently there — `shopt -p X
    # Y` exits 1 when any listed option is disabled, and under set -e
    # the caller's script died with no stderr (the exit status was the
    # only signal; stdout of shopt was captured into the variable).
    #
    # Reproduces the exact caller shape rather than going through
    # run_extract() (which invokes bash without set -e) so the guard
    # actually catches the regression.
    mkdir -p "${STAGING_DIR}/openemr-8.2.0"
    echo "x" >"${STAGING_DIR}/openemr-8.2.0/marker"
    make_zip strict openemr-8.2.0

    run bash -c "
        set -euo pipefail
        shopt -u nullglob dotglob
        source '${EXTRACT_ZIP_LIB}'
        extract_zip_flattening_single_top_level_dir \
            '${ZIP_DIR}/strict.zip' \
            '${DEST_DIR}' \
            'extract-zip-strict.XXXXXX' \
            'strict-mode test context'
    "

    [[ ${status} -eq 0 ]]
    [[ -f "${DEST_DIR}/marker" ]]
}
