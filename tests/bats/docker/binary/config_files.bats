# BATS: binary config files — Dockerfile, php-fpm, Apache

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir binary)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "binary Dockerfile: references php-fpm" {
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'php-fpm'
}

@test "binary Dockerfile: COPY php-fpm.conf" {
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'php-fpm'
}

@test "binary Dockerfile: fetches tests via git clone not GitHub archive" {
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'git clone'
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'export-ignore'
    ! grep -q 'archive/refs/tags' "${SCRIPT_DIR}/Dockerfile"
}

# GitHub source tarballs honor export-ignore. tests/ is marked that way, so
# archive/refs/tags/.../openemr-X/tests never exists on current tags.
@test "binary Dockerfile: does not fetch export-ignored tests from GitHub archives" {
    local root gitattributes
    root="$(get_repo_root)"
    gitattributes="${root}/.gitattributes"
    assert_file_exists "$gitattributes"
    grep -qE '^[[:space:]]*tests/[[:space:]]+export-ignore' "$gitattributes" \
        || { echo "expected tests/ export-ignore in .gitattributes"; return 1; }
    if grep -q 'archive/refs/tags' "${SCRIPT_DIR}/Dockerfile"; then
        echo "tests/ is export-ignore; GitHub tag tarballs omit it."
        echo "Fetch tests via git clone (see docker/release/Dockerfile)."
        return 1
    fi
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'git clone'
}

@test "binary Dockerfile: forge URLs derive php selector from PHP_VERSION_ABBR" {
    local dockerfile="${SCRIPT_DIR}/Dockerfile"
    local php_version
    local openemr_version
    local binary_release_date
    local alpine_version
    local php_version_abbr
    local probe
    php_version=$(sed -n 's/^ARG PHP_VERSION=//p' "$dockerfile" | head -1)
    openemr_version=$(sed -n 's/^ARG OPENEMR_VERSION=//p' "$dockerfile" | head -1)
    binary_release_date=$(sed -n 's/^ARG BINARY_RELEASE_DATE=//p' "$dockerfile" | head -1)
    alpine_version=$(sed -n 's/^ARG ALPINE_VERSION=//p' "$dockerfile" | head -1)
    # Shape, not value. These four ARGs are bot-maintained by
    # .github/workflows/updatecli-docker-pins.yml, so pinning the literals
    # here would turn every routine pin bump red until someone hand-edited
    # this file. What the derivation actually needs is that each ARG is
    # present and well-formed.
    [[ "$php_version" =~ ^[0-9]+\.[0-9]+$ ]]
    [[ "$openemr_version" =~ ^[0-9]+(_[0-9]+)+$ ]]
    [[ "$binary_release_date" =~ ^[0-9]{8}$ ]]
    [[ "$alpine_version" =~ ^3\.[0-9]+$ ]]
    php_version_abbr=$(sed -n 's/^ARG PHP_VERSION_ABBR=//p' "$dockerfile" | head -1)
    [[ "$php_version_abbr" == '${PHP_VERSION//./}' ]]
    assert_file_contains "$dockerfile" 'php\${PHP_VERSION_ABBR}-openemr-v\${OPENEMR_VERSION}-.*-\${BINARY_RELEASE_DATE}/php-fpm-v\${OPENEMR_VERSION}'
    assert_file_contains "$dockerfile" 'php\${PHP_VERSION_ABBR}-openemr-v\${OPENEMR_VERSION}-.*-\${BINARY_RELEASE_DATE}/php-cli-v\${OPENEMR_VERSION}'
    assert_file_contains "$dockerfile" 'php\${PHP_VERSION_ABBR}-openemr-v\${OPENEMR_VERSION}-.*-\${BINARY_RELEASE_DATE}/openemr.phar'

    if ! command -v docker >/dev/null 2>&1; then
        echo "docker not available; skipping bounded PHP_VERSION_ABBR build probe" >&3
        return 0
    fi
    probe="${BATS_TEST_TMPDIR}/php-abbr-probe"
    mkdir -p "$probe"
    cat > "${probe}/Dockerfile" <<EOF
# syntax=docker/dockerfile:1
FROM alpine:${alpine_version}
ARG PHP_VERSION=8.5
ARG PHP_VERSION_ABBR=\${PHP_VERSION//./}
ARG EXPECTED_PHP_VERSION_ABBR=85
RUN test "\${PHP_VERSION_ABBR}" = "\${EXPECTED_PHP_VERSION_ABBR}"
EOF
    docker build --quiet \
        --build-arg PHP_VERSION=8.5 \
        --build-arg EXPECTED_PHP_VERSION_ABBR=85 \
        "$probe"
    docker build --quiet \
        --build-arg PHP_VERSION=8.4 \
        --build-arg EXPECTED_PHP_VERSION_ABBR=84 \
        "$probe"
}

@test "binary php-fpm.conf: exists" {
    assert_file_exists "${SCRIPT_DIR}/php-fpm.conf"
}

@test "binary php-fpm.d/www.conf: exists" {
    assert_file_exists "${SCRIPT_DIR}/php-fpm.d/www.conf"
}

@test "binary openemr.conf: exists" {
    assert_file_exists "${SCRIPT_DIR}/openemr.conf"
}

@test "binary php.ini: exists" {
    assert_file_exists "${SCRIPT_DIR}/php.ini"
}

@test "binary auto_configure.php: exists" {
    assert_file_exists "${SCRIPT_DIR}/auto_configure.php"
}

@test "binary docker-compose.test.yml: exists for local testing" {
    assert_file_exists "${SCRIPT_DIR}/docker-compose.test.yml"
}
