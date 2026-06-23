# BATS: release fsupgrade-1.sh — functional test (directory creation in temp site)

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir release)"
    FSUP1="${SCRIPT_DIR}/upgrade/fsupgrade-1.sh"
    [[ -f "$FSUP1" ]]
}

@test "fsupgrade-1: creates documents subdirs when run with substituted path" {
    base="${BATS_TEST_TMPDIR}/oe"
    sites="${base}/sites"
    default="${sites}/default"
    mkdir -p "$default"
    # Replace all references to the openemr path so script runs in temp dir
    sed "s|/var/www/localhost/htdocs/openemr|${base}|g" "$FSUP1" > "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh"
    sed 's/chown -R apache:root.*/true \# chown skipped for test/' "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh" > "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh.tmp" && mv "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh.tmp" "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh"
    # Second loop needs sql_upgrade.php to exist (cat ... || true keeps going)
    touch "${base}/sql_upgrade.php"
    chmod +x "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh"
    run bash "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh" 2>&1
    [[ -d "${default}/documents/certificates" ]]
    [[ -d "${default}/documents/smarty/gacl" ]]
    [[ -d "${default}/documents/smarty/main" ]]
    [[ -d "${default}/documents/letter_templates" ]]
    [[ -d "${default}/documents/era" ]]
}

@test "fsupgrade-1: script is idempotent for directory creation" {
    base="${BATS_TEST_TMPDIR}/oe2"
    sites="${base}/sites"
    default="${sites}/default"
    mkdir -p "$default"
    touch "${base}/sql_upgrade.php"
    sed "s|/var/www/localhost/htdocs/openemr|${base}|g" "$FSUP1" > "${BATS_TEST_TMPDIR}/fsupgrade-1-test2.sh"
    sed 's/chown -R apache:root.*/true/' "${BATS_TEST_TMPDIR}/fsupgrade-1-test2.sh" > "${BATS_TEST_TMPDIR}/fsupgrade-1-test2.sh.tmp" && mv "${BATS_TEST_TMPDIR}/fsupgrade-1-test2.sh.tmp" "${BATS_TEST_TMPDIR}/fsupgrade-1-test2.sh"
    chmod +x "${BATS_TEST_TMPDIR}/fsupgrade-1-test2.sh"
    bash "${BATS_TEST_TMPDIR}/fsupgrade-1-test2.sh" 2>/dev/null || true
    bash "${BATS_TEST_TMPDIR}/fsupgrade-1-test2.sh" 2>/dev/null || true
    [[ -d "${default}/documents/certificates" ]]
    [[ -d "${default}/documents/smarty/main" ]]
}
