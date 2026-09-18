# BATS: binary ssl.sh — SSL/TLS (same behavior as 8.1.0)

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir binary)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "binary ssl.sh: self-signed and webserver certs" {
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'selfsigned'
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'webserver.cert.pem'
}

@test "binary ssl.sh: docker-selfsigned-configured" {
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'docker-selfsigned-configured'
}

@test "binary ssl.sh: DOMAIN and certbot for Let's Encrypt" {
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'DOMAIN'
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'certbot'
}
