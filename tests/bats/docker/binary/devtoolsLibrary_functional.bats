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
    run bash -c "set -e; source '$LIB'; grep -Fq '\${EPOCHREALTIME/[.,]/}' '$LIB'; [[ \"\$(current_time_us)\" =~ ^[0-9]+$ ]]; [[ \"\$(elapsed_time_us 1000000 1234567)\" == \"234567\" ]]; [[ \"\$(elapsed_time_us 1234567 1000000)\" == \"0\" ]]; [[ \"\$(format_elapsed_seconds 4999)\" == \"0.00\" ]]; [[ \"\$(format_elapsed_seconds 5000)\" == \"0.01\" ]]; [[ \"\$(format_elapsed_seconds 234567)\" == \"0.23\" ]]"
    [[ $status -eq 0 ]]
}

@test "binary openemr.sh: startup timing avoids busybox nanoseconds and python" {
    ! grep -q 'date +%s\.%N' "$SCRIPT"
    ! grep -q 'python3 -c "print(round' "$SCRIPT"
    grep -Fq 'PERM_DURATION_US >= 5000' "$SCRIPT"
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
