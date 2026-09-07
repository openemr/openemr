# BATS: release openemr.sh — functional tests (early execution and role logic)

load '../test_helper/bats-support/load'
load '../test_helper/bats-assert/load'
load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir release)"
    OPENEMR="${SCRIPT_DIR}/openemr.sh"
    LIB="${SCRIPT_DIR}/utilities/devtoolsLibrary.source"
    [[ -f "$OPENEMR" ]]
    [[ -f "$LIB" ]]
}

# Executing openemr.sh outside a container needs two things the image provides
# and a test runner does not: run-parts (Debian/busybox, absent on macOS) and
# GNU timeout. Skip rather than fail where they are missing -- CI runs on
# ubuntu-24.04, which has both.
require_entrypoint_tools() {
    command -v run-parts >/dev/null 2>&1 || skip 'run-parts not installed'
    command -v timeout >/dev/null 2>&1 || skip 'timeout not installed'
}

@test "openemr: a failing tooearly hook aborts launch" {
    require_entrypoint_tools

    local hooks="${BATS_TEST_TMPDIR}/hooks"
    mkdir -p "${hooks}/tooearly"
    # No dots in hook filenames: Debian's run-parts, which is what runs here,
    # silently skips anything with an extension. See the hook docblock in
    # devtoolsLibrary.source.
    printf '#!/bin/sh\necho reached-01\n' > "${hooks}/tooearly/01ok"
    printf '#!/bin/sh\nexit 1\n' > "${hooks}/tooearly/02fail"
    chmod +x "${hooks}/tooearly/01ok" "${hooks}/tooearly/02fail"

    run env "DEVTOOLS_LIB=${LIB}" "HOOKS_ROOT=${hooks}" K8S=admin \
        timeout 10 "$OPENEMR"

    # 01ok's output proves execution actually reached the call site, so the
    # non-zero status below cannot be the script dying on its way there.
    assert_output --partial 'reached-01'
    assert_failure

    # The load-bearing assertion: "[TIMING] Step 0-Start" is the first thing
    # openemr.sh emits after `run_vendor_hook tooearly`. Its absence is what
    # distinguishes "the hook stopped the launch" -- the documented contract --
    # from "the hook failed and the script carried on anyway."
    refute_output --partial '[TIMING] Step 0-Start'
}

@test "openemr: tooearly hook runs before any container work" {
    require_entrypoint_tools

    local hooks="${BATS_TEST_TMPDIR}/hooks"
    mkdir -p "${hooks}/tooearly"
    printf '#!/bin/sh\necho tooearly-ran\nexit 1\n' > "${hooks}/tooearly/01fail"
    chmod +x "${hooks}/tooearly/01fail"

    run env "DEVTOOLS_LIB=${LIB}" "HOOKS_ROOT=${hooks}" K8S=admin \
        MYSQL_HOST=localhost MYSQL_ROOT_PASS=root \
        timeout 10 "$OPENEMR"

    # Positive control, as above: without it every assertion below would also
    # hold for a script that died before it ever reached the call site.
    assert_output --partial 'tooearly-ran'
    assert_failure
    # tooearly is documented as running "before any container work has
    # started". Nothing downstream of the call site may have run: no swarm
    # coordination, no certificate generation, no database contact.
    refute_output --partial '[TIMING] Step 0-Start'
    refute_output --partial 'Generating self-signed SSL certificate'
    refute_output --partial 'Waiting for MySQL'
}

@test "openemr: CONFIGURATION is set after prepareVariables when sourced with env" {
    # Source only the library and openemr.sh vars then call prepareVariables via library
    run bash -c "export MYSQL_HOST=db MYSQL_ROOT_PASS=secret; source '${SCRIPT_DIR}/utilities/devtoolsLibrary.source'; prepareVariables; echo \"CONFIG=\$CONFIGURATION\""
    assert_success
    assert_output --partial "server=db"
    assert_output --partial "rootpass=secret"
}
