# BATS: release devtoolsLibrary.source — functional tests (source and call prepareVariables)

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir release)"
    LIB="${SCRIPT_DIR}/utilities/devtoolsLibrary.source"
    [[ -f "$LIB" ]]
}

@test "devtoolsLibrary: prepareVariables with custom env sets CONFIGURATION" {
    run bash -c "export MYSQL_HOST=db.example.com MYSQL_ROOT_PASS=secret123 MYSQL_USER=oeuser MYSQL_PASS=oepass MYSQL_DATABASE=oedb MYSQL_PORT=3307 OE_USER=admin OE_PASS=adminpass; source '$LIB'; prepareVariables; echo \"CONFIG=\$CONFIGURATION\""
    [[ $status -eq 0 ]]
    [[ $output == *"server=db.example.com"* ]]
    [[ $output == *"rootpass=secret123"* ]]
    [[ $output == *"login=oeuser"* ]]
    [[ $output == *"pass=oepass"* ]]
    [[ $output == *"dbname=oedb"* ]]
    [[ $output == *"port=3307"* ]]
    [[ $output == *"iuser=admin"* ]]
    [[ $output == *"iuserpass=adminpass"* ]]
}

@test "devtoolsLibrary: prepareVariables sets CUSTOM_* variables" {
    run bash -c "export MYSQL_HOST=localhost MYSQL_ROOT_PASS=root MYSQL_USER=customuser MYSQL_PASS=custompass MYSQL_DATABASE=customdb MYSQL_PORT=3308; source '$LIB'; prepareVariables; echo \"CUSER=\$CUSTOM_USER CPASS=\$CUSTOM_PASSWORD CDB=\$CUSTOM_DATABASE CPORT=\$CUSTOM_PORT\""
    [[ $status -eq 0 ]]
    [[ $output == *"CUSER=customuser"* ]]
    [[ $output == *"CPASS=custompass"* ]]
    [[ $output == *"CDB=customdb"* ]]
    [[ $output == *"CPORT=3308"* ]]
}

@test "devtoolsLibrary: prepareVariables defaults when env empty" {
    run bash -c "unset MYSQL_HOST MYSQL_ROOT_PASS MYSQL_USER MYSQL_PASS MYSQL_DATABASE MYSQL_PORT OE_USER OE_PASS; export MYSQL_HOST=localhost MYSQL_ROOT_PASS=root; source '$LIB'; prepareVariables; echo \"CONFIG=\$CONFIGURATION\""
    [[ $status -eq 0 ]]
    [[ $output == *"server=localhost"* ]]
    [[ $output == *"loginhost=%"* ]]
    # Defaults: login=openemr, pass=openemr, dbname=openemr
    run bash -c "export MYSQL_ROOT_PASS=root; source '$LIB'; prepareVariables; echo \"CUSER=\$CUSTOM_USER CDB=\$CUSTOM_DATABASE CPORT=\$CUSTOM_PORT\""
    [[ $status -eq 0 ]]
    [[ $output == *"CUSER=openemr"* ]]
    [[ $output == *"CDB=openemr"* ]]
    [[ $output == *"CPORT=3306"* ]]
}

@test "devtoolsLibrary: prepareVariables CUSTOM_ROOT_USER when MYSQL_ROOT_USER set" {
    run bash -c "export MYSQL_ROOT_PASS=root MYSQL_ROOT_USER=superroot; source '$LIB'; prepareVariables; echo \"ROOT=\$CUSTOM_ROOT_USER\""
    [[ $status -eq 0 ]]
    [[ $output == *"ROOT=superroot"* ]]
}

@test "devtoolsLibrary: prepareVariables CUSTOM_ROOT_USER defaults to root" {
    run bash -c "export MYSQL_ROOT_PASS=root; source '$LIB'; prepareVariables; echo \"ROOT=\$CUSTOM_ROOT_USER\""
    [[ $status -eq 0 ]]
    [[ $output == *"ROOT=root"* ]]
}

@test "devtoolsLibrary: setGlobalSettings parses OPENEMR_SETTING_ and echoes Set X to Y" {
    # Stub mariadb so we don't require real DB; script echoes "Set name to value" before calling mariadb
    stub_dir="${BATS_TEST_TMPDIR}/stub_mariadb"
    mkdir -p "$stub_dir"
    echo '#!/bin/sh
exit 0' > "${stub_dir}/mariadb"
    chmod +x "${stub_dir}/mariadb"
    run env PATH="${stub_dir}:$PATH" bash -c "export MYSQL_ROOT_PASS=root MYSQL_HOST=localhost CUSTOM_USER=openemr CUSTOM_PASSWORD=openemr CUSTOM_DATABASE=openemr CUSTOM_PORT=3306 OPENEMR_SETTING_rest_api=1 OPENEMR_SETTING_my_setting=value; source '$LIB'; prepareVariables; setGlobalSettings 2>&1"
    [[ $status -eq 0 ]]
    [[ $output == *"Set rest_api to 1"* ]]
    [[ $output == *"Set my_setting to value"* ]]
}

@test "devtoolsLibrary: setGlobalSettings does nothing when no OPENEMR_SETTING_ vars" {
    run bash -c "export MYSQL_ROOT_PASS=root; source '$LIB'; prepareVariables; setGlobalSettings 2>&1"
    [[ $status -eq 0 ]]
    [[ $output != *"Set "*" to "* ]] || [[ -z $output ]]
}
