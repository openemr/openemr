# BATS: binary fsupgrade-1.sh — functional test (directory creation)

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir binary)"
    FSUP1="${SCRIPT_DIR}/upgrade/fsupgrade-1.sh"
    [[ -f "$FSUP1" ]]
}

@test "binary fsupgrade-1: creates documents subdirs when run with substituted path" {
    base="${BATS_TEST_TMPDIR}/oe"
    sites="${base}/sites"
    default="${sites}/default"
    mkdir -p "$default"
    sed "s|/var/www/localhost/htdocs/openemr|${base}|g" "$FSUP1" > "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh"
    sed 's/chown -R apache:root.*/true/' "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh" > "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh.tmp" && mv "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh.tmp" "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh"
    touch "${base}/sql_upgrade.php"
    chmod +x "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh"
    run bash "${BATS_TEST_TMPDIR}/fsupgrade-1-test.sh" 2>&1
    [[ -d "${default}/documents/certificates" ]]
    [[ -d "${default}/documents/smarty/gacl" ]]
    [[ -d "${default}/documents/smarty/main" ]]
    [[ -d "${default}/documents/letter_templates" ]]
}
