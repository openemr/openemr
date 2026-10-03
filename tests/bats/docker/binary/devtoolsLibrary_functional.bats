# BATS: binary devtoolsLibrary.source — functional tests (source and call prepareVariables)

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir binary)"
    LIB="${SCRIPT_DIR}/utilities/devtoolsLibrary.source"
    SCRIPT="${SCRIPT_DIR}/openemr.sh"
    [[ -f "$LIB" ]]
    [[ -f "$SCRIPT" ]]
}

@test "binary devtoolsLibrary: timing helpers use EPOCHREALTIME microseconds" {
    run bash -c "source '$LIB'; grep -Fq '\${EPOCHREALTIME/[.,]/}' '$LIB'"
    [[ $status -eq 0 ]]
    run bash -c "source '$LIB'; current_time_us"
    [[ $status -eq 0 ]] || return 1
    [[ "$output" =~ ^[0-9]+$ ]] || return 1
    run bash -c "source '$LIB'; elapsed_time_us 1000000 1234567"
    [[ $status -eq 0 ]] || return 1
    [[ "$output" == "234567" ]] || return 1
    run bash -c "source '$LIB'; elapsed_time_us 1234567 1000000"
    [[ $status -eq 0 ]] || return 1
    [[ "$output" == "0" ]] || return 1
    run bash -c "source '$LIB'; format_elapsed_seconds 4999"
    [[ $status -eq 0 ]]
    [[ "$output" == "0.00" ]]
    run bash -c "source '$LIB'; format_elapsed_seconds 5000"
    [[ $status -eq 0 ]]
    [[ "$output" == "0.01" ]]
    run bash -c "source '$LIB'; format_elapsed_seconds 234567"
    [[ $status -eq 0 ]]
    [[ "$output" == "0.23" ]]
}

@test "binary openemr.sh: startup timing avoids busybox nanoseconds and python" {
    run grep -q 'date +%s\.%N' "$SCRIPT"
    [[ $status -ne 0 ]] || return 1
    run grep -q 'python3 -c "print(round' "$SCRIPT"
    [[ $status -ne 0 ]] || return 1
    run grep -Fq 'PERM_DURATION_US >= 5000' "$SCRIPT"
    [[ $status -eq 0 ]]
}

@test "binary devtoolsLibrary: prepareVariables with custom env sets CONFIGURATION" {
    run bash -c "export MYSQL_HOST=db.example.com MYSQL_ROOT_PASS=secret123 MYSQL_USER=oeuser MYSQL_PASS=oepass MYSQL_DATABASE=oedb MYSQL_PORT=3307 OE_USER=admin OE_PASS=adminpass; source '$LIB'; prepareVariables; echo \"CONFIG=\$CONFIGURATION\""
    [[ $status -eq 0 ]]
    [[ $output == *"server=db.example.com"* ]]
    [[ $output == *"rootpass=secret123"* ]]
    [[ $output == *"login=oeuser"* ]]
    [[ $output == *"dbname=oedb"* ]]
}

@test "binary devtoolsLibrary: prepareVariables sets CUSTOM_* variables" {
    run bash -c "export MYSQL_HOST=localhost MYSQL_ROOT_PASS=root MYSQL_USER=customuser MYSQL_PASS=custompass MYSQL_DATABASE=customdb; source '$LIB'; prepareVariables; echo \"CUSER=\$CUSTOM_USER CDB=\$CUSTOM_DATABASE\""
    [[ $status -eq 0 ]]
    [[ $output == *"CUSER=customuser"* ]]
    [[ $output == *"CDB=customdb"* ]]
}

@test "binary devtoolsLibrary: prepareVariables defaults (openemr, 3306)" {
    run bash -c "export MYSQL_ROOT_PASS=root; source '$LIB'; prepareVariables; echo \"CUSER=\$CUSTOM_USER CPORT=\$CUSTOM_PORT\""
    [[ $status -eq 0 ]]
    [[ $output == *"CUSER=openemr"* ]]
    [[ $output == *"CPORT=3306"* ]]
}
