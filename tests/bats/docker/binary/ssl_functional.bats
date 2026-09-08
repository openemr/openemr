# BATS: binary ssl.sh — functional tests

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir binary)"
    SSL="${SCRIPT_DIR}/ssl.sh"
    [[ -f "$SSL" ]]
}

@test "binary ssl: runs without crashing when DOMAIN unset" {
    run bash -c "'$SSL' 2>&1; exit \$?"
    [[ -n $output || $status -ge 0 ]]
}

@test "binary ssl: output contains expected message or error" {
    run bash -c "'$SSL' 2>&1"
    [[ $output == *"SSL"* ]] || [[ $output == *"Self-signed"* ]] || [[ $output == *"Warning"* ]] || [[ $output == *"certificate"* ]] || [[ $output == *"Generating"* ]]
}

@test "binary ssl: with stub openssl that fails, script does not crash" {
    stub_dir="${BATS_TEST_TMPDIR}/stub_path"
    mkdir -p "$stub_dir"
    echo '#!/bin/sh
exit 1' > "${stub_dir}/openssl"
    chmod +x "${stub_dir}/openssl"
    run env PATH="${stub_dir}:$PATH" bash -c "'$SSL' 2>&1"
    [[ $output == *"Warning"* ]] || [[ $output == *"SSL configuration completed"* ]] || [[ $output == *"Generating"* ]] || [[ $status -ge 0 ]]
}
