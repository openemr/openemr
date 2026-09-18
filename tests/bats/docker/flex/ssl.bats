# BATS: flex ssl.sh

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir flex)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "flex ssl.sh: self-signed and webserver certs" {
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'selfsigned'
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'webserver.cert.pem'
}

@test "flex ssl.sh: DOMAIN and certbot" {
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'DOMAIN'
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'certbot'
}
