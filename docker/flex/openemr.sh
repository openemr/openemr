#!/usr/bin/env bash
# ============================================================================
# OpenEMR Flex Container Startup Script
# ============================================================================
# This script runs when the flex container starts and handles all setup tasks
# needed to get OpenEMR running. Unlike versioned containers, the flex container
# fetches OpenEMR source code at runtime from a configurable git repository,
# making it suitable for testing different branches, tags, or forks.
#
# Key Features:
#   - Runtime OpenEMR source fetching (configurable via FLEX_REPOSITORY env vars)
#   - Runtime dependency building (composer/npm can run at container startup)
#   - Support for multiple development modes (EASY_DEV_MODE, EASY_DEV_MODE_NEW, etc.)
#   - Automated database setup and configuration
#   - Multi-container coordination (swarm mode)
#   - SSL/TLS certificate management
#   - Redis session configuration
#   - File permissions management
#
# Environment Variables:
#   Required for flex mode:
#     - FLEX_REPOSITORY: Git repository URL (default: https://github.com/openemr/openemr.git)
#     - FLEX_REPOSITORY_BRANCH or FLEX_REPOSITORY_TAG: Branch or tag to use
#   Required for auto installation:
#     - MYSQL_HOST: Database server hostname
#     - MYSQL_ROOT_PASS: Database root password (use 'BLANK' for empty password)
#   Optional:
#     - MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE: Database credentials
#     - OE_USER, OE_PASS: OpenEMR admin credentials
#     - EASY_DEV_MODE: 'yes' to skip permission changes (for volume mounts)
#     - EASY_DEV_MODE_NEW: 'yes' to use local repo instead of downloading
#     - INSANE_DEV_MODE: 'yes' for devtools support
#     - FORCE_NO_BUILD_MODE: 'yes' to skip composer/npm builds
#     - DEVELOPER_TOOLS: 'yes' to install development tools
#     - GITHUB_COMPOSER_TOKEN: GitHub token for composer
#     - REDIS_SERVER: Redis server address
#     - PCOV_ON: Enable PCOV for code coverage (mutually exclusive with XDebug)
#     - XDEBUG_ON: Enable XDebug for debugging
#
# Usage:
#   Called automatically by Docker CMD, but can be run manually for testing
# ============================================================================

set -euo pipefail

# ============================================================================
# SHELL LIBRARY SOURCING
# ============================================================================
# Load helper functions from devtoolsLibrary.source
# This provides utility functions for database operations, configuration, etc.
# shellcheck source=SCRIPTDIR/utilities/devtoolsLibrary.source
. /root/devtoolsLibrary.source

# ============================================================================
# PATH CONFIGURATION
# ============================================================================
# Define paths used throughout the script for OpenEMR installation and configuration
OE_ROOT="/var/www/localhost/htdocs/openemr"
# shellcheck disable=SC2034  # AUTO_CONFIG is defined for consistency with 7.0.5, may be used in future
AUTO_CONFIG="/var/www/localhost/htdocs/openemr/auto_configure.php"
SQLCONF_FILE="${OE_ROOT}/sites/default/sqlconf.php"

# ============================================================================
# DATABASE CONFIGURATION
# ============================================================================
# OpenEMR requires a MySQL/MariaDB database. These variables control the
# database connection and credentials. Defaults are provided for development.
MYSQL_HOST="${MYSQL_HOST:-mysql}"
MYSQL_PORT="${MYSQL_PORT:-3306}"
MYSQL_ROOT_USER="${MYSQL_ROOT_USER:-root}"
MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-root}"
MYSQL_USER="${MYSQL_USER:-openemr}"
MYSQL_PASS="${MYSQL_PASS:-openemr}"
MYSQL_DATABASE="${MYSQL_DATABASE:-openemr}"
MYSQL_COLLATION="${MYSQL_COLLATION:-utf8mb4_general_ci}"

# ============================================================================
# OPENEMR ADMIN USER CONFIGURATION
# ============================================================================
# Initial administrator account created during first-time setup.
# IMPORTANT: Change these defaults in production!
OE_USER="${OE_USER:-admin}"
OE_USER_NAME="${OE_USER_NAME:-Administrator}"
OE_PASS="${OE_PASS:-pass}"

# ============================================================================
# OPERATION MODE SETTINGS
# ============================================================================
# Control container behavior for different deployment scenarios
MANUAL_SETUP="${MANUAL_SETUP:-no}"
K8S="${K8S:-}"
SWARM_MODE="${SWARM_MODE:-no}"

# defaults
: "${DEMO_MODE:=no}" \
  "${DEVELOPER_TOOLS:=no}" \
  "${EASY_DEV_MODE:=no}" \
  "${EASY_DEV_MODE_NEW:=no}" \
  "${EMPTY:=no}" \
  "${FLEX_REPOSITORY_TAG:=}" \
  "${FORCE_NO_BUILD_MODE:=no}" \
  "${GITHUB_COMPOSER_TOKEN:=}" \
  "${GITHUB_COMPOSER_TOKEN_ENCODED:=}" \
  "${GITHUB_COMPOSER_TOKEN_ENCODED_ALTERNATE:=}" \
  "${INSANE_DEV_MODE:=no}" \
  "${K8S:=}" \
  "${MANUAL_SETUP:=no}" \
  "${PCOV_ON:=no}" \
  "${REDIS_PASSWORD:=}" \
  "${REDIS_SERVER:=}" \
  "${REDIS_USERNAME:=}" \
  "${SWARM_MODE:=no}" \
  "${XDEBUG_IDE_KEY:=}" \
  "${XDEBUG_ON:=no}"

# Normalize PCOV_ON to "true" or "false" for simpler checks
case "${PCOV_ON,,}" in
    1|yes|true) PCOV_ON=true ;;
    *) PCOV_ON=false ;;
esac

# Disable git's CVE-2022-24765 ownership check for the bind-mounted
# OpenEMR working tree. When the host UID does not match the container's
# apache UID (common on macOS Docker Desktop, WSL2, GitHub Codespaces),
# git refuses to operate without this. Without it, in-container git
# tooling fails: npm postinstall hooks (husky, lint-staged), prek,
# composer scripts that read commit metadata, and openemr-cmd commands
# that shell out to git.
#
# Runs unconditionally on every container start, before any code path
# that might invoke git. Scoped to DEVELOPER_TOOLS=yes only: production
# images COPY the source rather than bind-mount it, so .git is owned by
# the container's build user and the ownership check passes naturally.
#
# --replace-all (vs --add) so /etc/gitconfig stays stable across restarts;
# specific path (vs '*') keeps the ownership check intact for any other
# repos that might exist in the container.
if [[ "${DEVELOPER_TOOLS}" = "yes" ]]; then
    git config --system --replace-all safe.directory /var/www/localhost/htdocs/openemr
fi

# Install development-only Alpine packages that openemr-cmd subcommands and
# the e2e test runner depend on:
#   chromium / chromium-chromedriver  for Symfony Panther (e2e tests)
#   py3-codespell                     for openemr-cmd cps / cq (codespell)
#   pre-commit                        for openemr-cmd prek -- the in-container
#                                     tool is Python's 'pre-commit'; openemr-cmd
#                                     exposes it under the label 'prek' so the
#                                     host-side and docker-side CLIs share a name
#   actionlint                        for the actionlint-system pre-commit hook;
#                                     openemr-cmd prek substitutes actionlint-docker
#                                     -> actionlint-system at invocation time
#                                     because DinD is not available from inside
#                                     this container
#   php${PHP_VERSION_ABBR}-pdo_sqlite for in-process SQLite via Doctrine DBAL.
#                                     HolidayServiceTest in the isolated test
#                                     suite builds an in-memory ':memory:'
#                                     SQLite connection in setUp; without
#                                     pdo_sqlite the whole class fatals with
#                                     'could not find driver' at driver init.
#
# Runs at top of script (not inside NEED_COMPOSER_BUILD) because apk packages
# live in the container's writable rootfs, not in any named volume. A
# 'docker compose down --keep-volumes && up' recreates the rootfs but
# preserves vendor (NEED_COMPOSER_BUILD=false), which would otherwise leave
# these binaries missing in the new container.
#
# Idempotent: 'apk info -e' guards short-circuit when everything is already
# installed. Tolerant of network failures.
if [[ "${DEVELOPER_TOOLS}" = "yes" ]] && {
       ! apk info -e chromium                                   >/dev/null 2>&1 || \
       ! apk info -e chromium-chromedriver                      >/dev/null 2>&1 || \
       ! apk info -e py3-codespell                              >/dev/null 2>&1 || \
       ! apk info -e pre-commit                                 >/dev/null 2>&1 || \
       ! apk info -e actionlint                                 >/dev/null 2>&1 || \
       ! apk info -e "php${PHP_VERSION_ABBR?}-pdo_sqlite"        >/dev/null 2>&1; }; then
    apk add --no-cache chromium chromium-chromedriver py3-codespell pre-commit actionlint \
        "php${PHP_VERSION_ABBR?}-pdo_sqlite" \
        || echo "dev apk packages: install failed; some openemr-cmd subcommands may be unavailable" >&2
fi

auto_setup() {
    prepareVariables

    # Update heartbeat before starting setup (if in swarm mode)
    [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat

    # Only set permissions if not in EASY_DEV_MODE (optimized: use -exec {} + instead of \; for better performance)
    if [[ "${EASY_DEV_MODE}" != "yes" ]]; then
        # Use {} + instead of {} \; to batch file operations and reduce process overhead
        find /var/www/localhost/htdocs/openemr -type f -not -perm 600 -exec chmod 600 {} + 2>/dev/null || true
    fi

    # Create temporary file cache directory for auto_configure.php to use
    # This enables opcache file caching for faster PHP execution during installation
    TMP_FILE_CACHE_LOCATION="/tmp/php-file-cache"
    mkdir -p "${TMP_FILE_CACHE_LOCATION}"
    # Apache is the only writer (we drop to apache below via su -p);
    # chown and restrict the dir to that user. The dir is rm -rf'd at
    # the end of this entrypoint either way.
    chown apache:apache "${TMP_FILE_CACHE_LOCATION}"
    chmod 0700 "${TMP_FILE_CACHE_LOCATION}"

    # Create auto_configure.ini to leverage opcache for faster installation
    {
        echo "opcache.enable=1"
        echo "opcache.enable_cli=1"
        echo "opcache.file_cache=${TMP_FILE_CACHE_LOCATION}"
        echo "opcache.file_cache_only=1"
        echo "opcache.file_cache_consistency_checks=1"
        echo "opcache.enable_file_override=1"
        echo "opcache.max_accelerated_files=1000000"
    } > auto_configure.ini

    # Update heartbeat right before long-running PHP command
    [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat

    # Run auto_configure with optimized PHP settings.
    # Drop privileges to apache: auto_configure.php goes through the
    # Installer class, which openemr#12267's RootCliGuard refuses to
    # run as root. `su-exec` exec's the program directly with no
    # intervening shell, preserving env and passing each arg
    # verbatim — see run_php_as_apache in devtoolsLibrary.source for
    # the rationale on using su-exec instead of busybox su.
    # The Dockerfile sets auto_configure.php to mode 000 as a safety
    # measure (root can read regardless); briefly chmod to 0644 so
    # apache can read it. The file is rm'd at the end of this
    # entrypoint either way.
    chmod 0644 /var/www/localhost/htdocs/auto_configure.php
    su-exec apache \
        php /var/www/localhost/htdocs/auto_configure.php -c auto_configure.ini -f "${CONFIGURATION}" || return 1

    # Update heartbeat after PHP execution completes
    [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat

    # Remove temporary file cache directory and auto_configure.ini
    rm -rf "${TMP_FILE_CACHE_LOCATION}"
    rm -f auto_configure.ini

    echo "OpenEMR configured."
    CONFIG=$(php -r "require_once('/var/www/localhost/htdocs/openemr/sites/default/sqlconf.php'); echo \$config;")
    if [[ "${CONFIG}" = "0" ]]; then
        echo "Error in auto-config. Configuration failed."
        exit 2
    fi

    # Update heartbeat after configuration verification
    [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat

    if [[ "${DEMO_MODE}" = "standard" ]]; then
        demoData
        [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
    fi

    if [[ "${SQL_DATA_DRIVE}" != "" ]]; then
        sqlDataDrive
        [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
    fi

    setGlobalSettings
    # Final heartbeat update after setup completion
    [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
}

# ============================================================================
# DATABASE WAITING FUNCTIONS
# ============================================================================

# Waits for MySQL/MariaDB to be ready to accept connections.
# This is critical because the database container may start simultaneously
# with this container, and databases need time to initialize.
#
# Uses mysqladmin ping for efficient health checking and implements
# exponential backoff to reduce unnecessary connection attempts.
wait_for_mysql() {
    local -i retries=60
    local -i initial_delay=1
    local -i max_delay=5
    local -i current_delay=${initial_delay}
    echo "Waiting for MySQL at ${MYSQL_HOST}:${MYSQL_PORT}..."

    # Try immediate connection first (MySQL might already be ready)
    # Use mysqladmin ping for more efficient health check
    if mysqladmin ping \
        --host="${MYSQL_HOST}" \
        --port="${MYSQL_PORT}" \
        --user="${MYSQL_ROOT_USER}" \
        --password="${MYSQL_ROOT_PASS}" \
        --silent >/dev/null 2>&1; then
        echo "MySQL is ready!"
        return 0
    fi

    while (( retries-- > 0 )); do
        # Test database connectivity using mysqladmin ping
        if mysqladmin ping \
            --host="${MYSQL_HOST}" \
            --port="${MYSQL_PORT}" \
            --user="${MYSQL_ROOT_USER}" \
            --password="${MYSQL_ROOT_PASS}" \
            --silent >/dev/null 2>&1; then
            echo "MySQL is ready!"
            return 0
        fi

        # Exponential backoff: start with shorter delays, increase gradually
        # Only print message every 5 retries to reduce log noise
        if (( retries % 5 == 0 || current_delay <= 2 )); then
            echo "MySQL not ready yet, retrying in ${current_delay} seconds... (${retries} attempts remaining)"
        fi
        sleep "${current_delay}"

        # Increase delay gradually (exponential backoff), but cap at max_delay
        if (( ++current_delay > max_delay )); then
            current_delay=${max_delay}
        fi
    done

    echo "ERROR: Timed out waiting for MySQL at ${MYSQL_HOST}:${MYSQL_PORT}" >&2
    return 1
}

# Waits for Redis to be available (if Redis is configured).
# Redis is optional but recommended for session storage and horizontal scaling.
wait_for_redis() {
    # Skip if Redis isn't configured
    [[ -z "${REDIS_SERVER:-}" ]] && return 0

    # Parse Redis server address (format: "host:port" or just "host")
    local redis_host
    local redis_port
    IFS=: read -r redis_host redis_port _ <<< "${REDIS_SERVER}"
    redis_port="${redis_port:-6379}"  # Default Redis port

    # Try to connect to Redis using netcat (allow time for DNS and service startup)
    local -i retries=30
    while (( retries-- > 0 )); do
        if command -v nc >/dev/null 2>&1 && nc -z "${redis_host}" "${redis_port}" >/dev/null 2>&1; then
            echo "Redis is ready!"
            return 0
        fi
        sleep 1
    done

    echo "ERROR: Redis at ${REDIS_SERVER} not reachable after 30 retries" >&2
    return 1
}

# Checks if OpenEMR has already been configured.
# Returns "1" if configured, "0" if not configured yet.
is_configured() {
    php -r "if (is_file('${SQLCONF_FILE}')) { require '${SQLCONF_FILE}'; echo isset(\$config) && \$config ? 1 : 0; } else { echo 0; }" 2>/dev/null | tail -1 || echo 0
}

# ============================================================================
# CONTAINER ROLE DEFINITIONS
# ============================================================================
# AUTHORITY: Right to change OpenEMR's configured state
#   - true for singletons, swarm leaders, and Kubernetes startup jobs
#   - false for swarm members and Kubernetes workers
#
# OPERATOR: Right to launch Apache and serve OpenEMR
#   - true for singletons, swarm members (leader or otherwise), and Kubernetes workers
#   - false for Kubernetes startup jobs and manual image runs

AUTHORITY=yes
OPERATOR=yes
SWARM_WAIT_DEFERRED=no

# Kubernetes-specific role assignment
if [[ "${K8S}" = "admin" ]]; then
    OPERATOR=no
elif [[ "${K8S}" = "worker" ]]; then
    AUTHORITY=no
fi

# ============================================================================
# SWARM MODE COORDINATION FUNCTIONS
# ============================================================================
# When running multiple containers (swarm mode), coordinate to ensure only
# one container performs the initial setup to avoid conflicts.

# Returns whether we should wait for the leader to finish setup.
# Returns 0 (wait) if setup is not complete, 1 (don't wait) if complete.
# Setup is complete if the file exists.
swarm_wait() {
    [[ ! -f "${OE_ROOT}/sites/docker-completed" ]]
}

# Checks if the current leader container has stopped responding (become stale).
# This detects crashed or frozen leader containers by checking when the leader
# file was last updated. If older than the timeout (default 5 minutes), the
# leader is considered stale.
is_leader_stale() {
    local leader_file="${OE_ROOT}/sites/docker-leader"

    # No leader file means no active leader (not stale, just absent)
    [[ ! -f "${leader_file}" ]] && return 0

    # Check file age
    local -i leader_timeout="${LEADER_TIMEOUT:-300}"  # Default: 5 minutes
    local -i now
    now=$(date +%s)
    local -i leader_mtime
    leader_mtime=$(stat -c %Y "${leader_file}" 2>/dev/null || stat -f %m "${leader_file}" 2>/dev/null || echo 0)
    local -i age=$((now - leader_mtime))

    # Leader is stale if file is older than timeout
    (( age > leader_timeout ))
}

# Tries to become the leader container using atomic file creation.
# Only one container can be leader at a time. Uses file locking via noclobber.
try_become_leader() {
    local leader_file="${OE_ROOT}/sites/docker-leader"

    # If setup is already complete, nobody needs to be leader
    if [[ -f "${OE_ROOT}/sites/docker-completed" ]]; then
        AUTHORITY=no
        echo "Setup already complete, this instance is a follower (AUTHORITY=no)"
        return 0
    fi

    # Check if current leader is stale
    # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
    if is_leader_stale; then
        echo "Current leader appears stale, attempting to take over..."
        rm -f "${leader_file}" "${OE_ROOT}/sites/docker-initiated"
        sleep 1  # Small delay to ensure file deletion is visible
    fi

    # Try to create leader file atomically
    set -o noclobber
    if { date +%s > "${leader_file}"; } 2>/dev/null; then
        # Success! We're the leader
        AUTHORITY=yes
        echo "This instance is the docker-leader (AUTHORITY=yes)"
        set +o noclobber
        return 0
    else
        # Someone else is the leader
        AUTHORITY=no
        echo "This instance is a follower (AUTHORITY=no)"
        set +o noclobber
        return 0
    fi
}

# Updates the leader file with current timestamp (heartbeat).
# This lets followers know the leader is still alive and working.
update_leader_heartbeat() {
    local leader_file="${OE_ROOT}/sites/docker-leader"
    if [[ "${AUTHORITY}" = "yes" ]]; then
        date +%s > "${leader_file}" 2>/dev/null || true
    fi
}

# Waits for the swarm leader to finish setup before reading shared configuration.
wait_for_swarm_completion() {
    if [[ "${AUTHORITY}" = "no" && ! -f "${OE_ROOT}/sites/docker-completed" ]]; then
        echo "Waiting for docker-leader to finish configuration (with timeout-based recovery)..."
        local -i max_wait_time="${LEADER_WAIT_TIMEOUT:-600}"  # Default: 10 minutes
        local -i waited=0

        # Wait for setup completion with stale leader detection
        # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
        while swarm_wait && (( waited < max_wait_time )); do
            # Check if leader has died
            # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
            if is_leader_stale; then
                echo "Leader appears to have crashed, attempting to take over..."
                try_become_leader
                if [[ "${AUTHORITY}" = "yes" ]]; then
                    echo "Successfully became leader after previous leader failure"
                    break
                fi
            fi
            sleep 10
            (( waited += 10 ))
        done

        # Try one more time to become leader (in case leader died just as we timed out)
        # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
        if [[ ! -f "${OE_ROOT}/sites/docker-completed" ]]; then
            try_become_leader
            if [[ "${AUTHORITY}" = "yes" ]]; then
                echo "Promoted to leader after waiting period"
            fi
        fi

        # If we timed out, check if configuration actually exists
        if [[ ! -f "${OE_ROOT}/sites/docker-completed" ]]; then
            local config_state
            config_state=$(is_configured)
            if [[ "${config_state}" = "1" ]]; then
                # Configuration exists, create completion marker
                touch "${OE_ROOT}/sites/docker-completed" 2>/dev/null || true
                echo "Configuration detected, marking swarm as completed"
            fi
        fi
    fi
}

# If we're the leader, create initiation marker and restore swarm pieces.
prepare_swarm_leader() {
    if [[ "${AUTHORITY}" = "yes" ]]; then
        touch "${OE_ROOT}/sites/docker-initiated"
        update_leader_heartbeat

        # Restore swarm-pieces if needed (for swarm mode with empty volumes)
        if [[ ! -f /etc/ssl/openssl.cnf ]]; then
            echo "Restoring empty /etc/ssl directory..."
            rsync --owner --group --perms --recursive --links /swarm-pieces/ssl /etc/
        fi
        if [[ ! -d "${OE_ROOT}/sites/default" ]]; then
            echo "Restoring empty ${OE_ROOT}/sites directory..."
            rsync --owner --group --perms --recursive --links /swarm-pieces/sites "${OE_ROOT}/"
        fi
    fi
}

# Handles swarm mode coordination: leader election and follower waiting.
handle_swarm_mode() {
    # Skip coordination if swarm mode isn't enabled
    if [[ "${SWARM_MODE}" != "yes" ]]; then
        return 0
    fi

    # Try to become the leader
    try_become_leader

    # Flex followers can prepare local source/dependencies while the leader
    # configures the shared sites volume, then wait before reading sqlconf.php.
    if [[ "${AUTHORITY}" = "no" && ! -f "${OE_ROOT}/sites/docker-completed" ]]; then
        SWARM_WAIT_DEFERRED=yes
        echo "Deferring docker-leader wait until local flex build is ready"
        return 0
    fi

    wait_for_swarm_completion
    prepare_swarm_leader
}

# ============================================================================
# SWARM MODE COORDINATION (MAIN EXECUTION)
# ============================================================================
handle_swarm_mode
[[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat

# ============================================================================
# SSL/TLS CERTIFICATE CONFIGURATION
# ============================================================================
# Configure SSL/TLS certificates. In swarm mode, followers may generate
# their own self-signed certs if /etc/ssl is not shared.
# Leaders always generate certs; followers only if certs don't exist
if [[ "${AUTHORITY}" = "yes" || ! -f /etc/ssl/certs/webserver.cert.pem || ! -f /etc/ssl/private/webserver.key.pem ]]; then
    sh ssl.sh &
    SSL_PID=$!
fi

# ============================================================================
# FLEX CONTAINER: RUNTIME SOURCE CODE FETCHING
# ============================================================================
# The flex container fetches OpenEMR source code from git at runtime.
# This allows testing different branches, tags, or forks without rebuilding
# the Docker image. The source is cloned to /var/www/localhost/htdocs/openemr
# before any dependency building or configuration occurs.
if [[ -f /var/www/localhost/htdocs/auto_configure.php ]] && [[ "${EMPTY}" != "yes" ]] &&
   [[ "${EASY_DEV_MODE_NEW}" != "yes" ]]; then
    echo "Configuring a new flex openemr docker"
    if [[ "${FLEX_REPOSITORY:-}" = "" ]]; then
        echo "Missing FLEX_REPOSITORY environment setting, so using https://github.com/openemr/openemr.git"
        FLEX_REPOSITORY="https://github.com/openemr/openemr.git"
    fi
    if [[ "${FLEX_REPOSITORY_BRANCH:-}" = "" ]] &&
       [[ "${FLEX_REPOSITORY_TAG:-}" = "" ]]; then
        echo "Missing FLEX_REPOSITORY_BRANCH or FLEX_REPOSITORY_TAG environment setting, so using FLEX_REPOSITORY_BRANCH setting of master"
        FLEX_REPOSITORY_BRANCH="master"
    fi

    cd /

    if [[ "${FLEX_REPOSITORY_BRANCH}" != "" ]]; then
        echo "Collecting ${FLEX_REPOSITORY_BRANCH} branch from ${FLEX_REPOSITORY} repository"
        git clone "${FLEX_REPOSITORY}" --branch "${FLEX_REPOSITORY_BRANCH}" --depth 1
    else
        echo "Collecting ${FLEX_REPOSITORY_TAG} tag from ${FLEX_REPOSITORY} repository"
        git clone "${FLEX_REPOSITORY}"
        cd openemr
        git checkout "${FLEX_REPOSITORY_TAG}"
        cd ../
    fi
    if [[ "${AUTHORITY}" = "yes" ]] &&
       [[ "${SWARM_MODE}" = "yes" ]]; then
        touch openemr/sites/default/docker-initiated
    fi
    if [[ "${AUTHORITY}" = "no" ]] &&
       [[ "${SWARM_MODE}" = "yes" ]]; then
        # non-leader is building so remove the openemr/sites directory to avoid breaking anything in leader's build
        rm -fr openemr/sites
    fi
    rsync --ignore-existing --recursive --links --exclude .git openemr /var/www/localhost/htdocs/
    rm -fr openemr
    cd /var/www/localhost/htdocs/
fi

# ============================================================================
# EASY DEV MODE: USE LOCAL REPOSITORY
# ============================================================================
# When EASY_DEV_MODE_NEW is enabled, use a local repository mounted at /openemr
# instead of fetching from git. This is useful for development where the code
# is already available in a volume mount.
if [[ "${EASY_DEV_MODE_NEW}" = "yes" ]]; then
    echo "EASY_DEV_MODE_NEW enabled: Using local repository from /openemr"
    rsync --ignore-existing --recursive --links --exclude .git /openemr /var/www/localhost/htdocs/
fi

# ============================================================================
# RUNTIME DEPENDENCY BUILDING
# ============================================================================
# Unlike versioned containers that build dependencies at Docker build time,
# the flex container builds dependencies at runtime. This allows flexibility
# to test different branches/tags that may have different dependency requirements.
#
# Dependencies are only built if they don't already exist, supporting scenarios
# where users mount volumes with pre-built dependencies or use cached builds.
# Separate checks allow npm builds even when vendor already exists (or vice versa).
NEED_COMPOSER_BUILD=false
NEED_NPM_BUILD=false
RAN_ANY_BUILD=false

if [[ -f /var/www/localhost/htdocs/auto_configure.php ]] && [[ "${FORCE_NO_BUILD_MODE}" != "yes" ]]; then
    # Check if composer/vendor build is needed
    if [[ ! -d /var/www/localhost/htdocs/openemr/vendor ]] || { [[ -d /var/www/localhost/htdocs/openemr/vendor ]] && [[ -z "$(ls -A /var/www/localhost/htdocs/openemr/vendor || true)" ]]; }; then
        NEED_COMPOSER_BUILD=true
    fi

    # Check if npm build is needed (node_modules missing/empty OR public missing/empty)
    if [[ ! -d /var/www/localhost/htdocs/openemr/node_modules ]] || { [[ -d /var/www/localhost/htdocs/openemr/node_modules ]] && [[ -z "$(ls -A /var/www/localhost/htdocs/openemr/node_modules || true)" ]]; } ||
       [[ ! -d /var/www/localhost/htdocs/openemr/public ]] || { [[ -d /var/www/localhost/htdocs/openemr/public ]] && [[ -z "$(ls -A /var/www/localhost/htdocs/openemr/public || true)" ]]; }; then
        NEED_NPM_BUILD=true
    fi
fi

if [[ "${NEED_COMPOSER_BUILD}" = "true" ]] || [[ "${NEED_NPM_BUILD}" = "true" ]]; then
    cd /var/www/localhost/htdocs/openemr

    # Install PHP dependencies if needed
    if [[ "${NEED_COMPOSER_BUILD}" = "true" ]]; then
        # Try to configure a GitHub token for composer to avoid rate limiting.
        # Returns 0 on success, 1 on failure.
        try_github_token() {
            local token="$1"
            local token_desc="$2"

            local rate_limit_response
            local rate_limit
            local rate_limit_message

            rate_limit_response=$(curl -s -H "Authorization: token ${token}" https://api.github.com/rate_limit)
            rate_limit=$(jq '.rate.remaining' <<< "${rate_limit_response}")
            rate_limit_message=$(jq '.message' <<< "${rate_limit_response}")

            printf 'Message received from api request is "%s"\n' "${rate_limit_message}"
            printf 'Number of github api requests remaining is "%s"\n' "${rate_limit}"

            # Validate the response
            if [[ ${rate_limit_message} = '"Bad credentials"' ]]; then
                echo "${token_desc} is bad, so did not work"
                return 1
            elif [[ -z ${rate_limit} ]]; then
                echo "github token rate limit is empty, so did not work"
                return 1
            elif [[ ${rate_limit} = *[!0-9]* ]]; then
                printf 'github token rate limit is not an integer ("%s")\n' "${rate_limit}"
                return 1
            elif (( rate_limit < 100 )); then
                echo "${token_desc} rate limit is now < 100, so did not work"
                return 1
            fi

            # Token is valid, configure composer
            if composer config --global --auth github-oauth.github.com "${token}"; then
                echo "${token_desc} worked"
                return 0
            fi

            echo "${token_desc} did not work"
            return 1
        }

        token_configured=false

        # if there is a raw github composer token supplied, then try to use it
        if [[ -n ${GITHUB_COMPOSER_TOKEN} ]]; then
            echo 'trying raw github composer token'
            # shellcheck disable=SC2310
            if try_github_token "${GITHUB_COMPOSER_TOKEN}" 'raw github composer token'; then
                token_configured=true
            fi
        fi

        # if there is no raw github composer token supplied or it was invalid, try a base64 encoded one (if it was supplied)
        if [[ ${token_configured} != true && -n ${GITHUB_COMPOSER_TOKEN_ENCODED} ]]; then
            echo 'trying encoded github composer token'
            decoded_token=$(base64 -d <<< "${GITHUB_COMPOSER_TOKEN_ENCODED}")
            # shellcheck disable=SC2310
            if try_github_token "${decoded_token}" 'encoded github composer token'; then
                token_configured=true
            fi
        fi

        # if there is no raw github composer token and/or base64 encoded one supplied or they were invalid, then try a character code encoded one (if it was supplied)
        if [[ ${token_configured} != true && -n ${GITHUB_COMPOSER_TOKEN_ENCODED_ALTERNATE} ]]; then
            echo 'trying alternate encoded github composer token'
            # Word splitting is intentional here to convert space-separated string to array
            # shellcheck disable=SC2206
            codes=(${GITHUB_COMPOSER_TOKEN_ENCODED_ALTERNATE})
            decoded_token=$(printf '%b' "$(printf '\\%03o' "${codes[@]}")")
            # shellcheck disable=SC2310
            if try_github_token "${decoded_token}" 'alternate encoded github composer token'; then
                token_configured=true
            fi
        fi

        # install php dependencies
        if [[ "${DEVELOPER_TOOLS}" = "yes" ]]; then
            composer install
        else
            composer install --no-dev
        fi
        RAN_ANY_BUILD=true
    fi

    # Install frontend dependencies and build if needed
    if [[ "${NEED_NPM_BUILD}" = "true" ]] && [[ -f /var/www/localhost/htdocs/openemr/package.json ]]; then
        # install frontend dependencies (need unsafe-perm to run as root)
        # IN ALPINE 3.14+, there is an odd permission thing happening where need to give non-root ownership
        #  to several places ('node_modules' and 'public') in flex environment that npm is accessing via:
        #    'chown -R apache:1000 node_modules'
        #    'chown -R apache:1000 ccdaservice/node_modules'
        #    'chown -R apache:1000 public'
        # WILL KEEP TRYING TO REMOVE THESE LINES IN THE FUTURE SINCE APPEARS TO LIKELY BE A FLEETING NPM BUG WITH --unsafe-perm SETTING
        #  should be ready to remove then the following npm error no long shows up on the build:
        #    "ERR! warning: unable to access '/root/.config/git/attributes': Permission denied"
        if [[ -d node_modules ]]; then
            chown -R apache:1000 node_modules
        fi
        if [[ -d ccdaservice/node_modules ]]; then
            chown -R apache:1000 ccdaservice/node_modules
        fi
        if [[ -d public ]]; then
            chown -R apache:1000 public
        fi
        npm install --unsafe-perm
        # build css
        npm run build
        RAN_ANY_BUILD=true
    fi

    if [[ "${NEED_NPM_BUILD}" = "true" ]] && [[ -f /var/www/localhost/htdocs/openemr/ccdaservice/package.json ]]; then
        # install ccdaservice
        cd /var/www/localhost/htdocs/openemr/ccdaservice
        npm install --unsafe-perm
        cd /var/www/localhost/htdocs/openemr
        RAN_ANY_BUILD=true
    fi

    # clean up and optimize (only if we ran any builds)
    if [[ "${RAN_ANY_BUILD}" = "true" ]]; then
        composer global require phing/phing
        /root/.composer/vendor/bin/phing vendor-clean
        /root/.composer/vendor/bin/phing assets-clean
        composer global remove phing/phing

        # optimize
        composer dump-autoload --optimize --apcu
    fi

    cd /var/www/localhost/htdocs
fi

if [[ "${SWARM_WAIT_DEFERRED}" = "yes" ]]; then
    wait_for_swarm_completion
    SWARM_WAIT_DEFERRED=no
    prepare_swarm_leader
fi

if [[ "${AUTHORITY}" = "yes" ]] ||
   [[ "${SWARM_MODE}" != "yes" ]]; then
    if [[ -f /var/www/localhost/htdocs/auto_configure.php ]] &&
       [[ "${EASY_DEV_MODE}" != "yes" ]]; then
        chmod 666 /var/www/localhost/htdocs/openemr/sites/default/sqlconf.php
    fi
fi

# Set ownership to apache user (optimized: use {} + for batch operations)
if [[ -f /var/www/localhost/htdocs/auto_configure.php ]]; then
    # Use {} + instead of {} \; to batch chown operations for better performance
    find /var/www/localhost/htdocs/openemr/ -name ".git" -prune -o -exec chown apache:apache {} + 2>/dev/null || true
fi

CONFIG=$(php -r "require_once('/var/www/localhost/htdocs/openemr/sites/default/sqlconf.php'); echo \$config;")
if [[ "${AUTHORITY}" = "no" ]] &&
   [[ "${CONFIG}" = "0" ]]; then
    echo "Critical failure! An OpenEMR worker is trying to run on a missing configuration."
    echo " - Is this due to a Kubernetes grant hiccup?"
    echo "The worker will now terminate."
    exit 1
fi

# key/cert management (if key/cert exists in /root/certs/.. and not in sites/default/documents/certificates, then it will be copied into it)
#  current use case is bringing in as secret(s) in kubernetes, but can bring in as shared volume or directly brought in during docker build
#   dir structure:
#    /root/certs/mysql/server/mysql-ca (supported)
#    /root/certs/mysql/client/mysql-cert (supported)
#    /root/certs/mysql/client/mysql-key (supported)
#    /root/certs/couchdb/couchdb-ca (supported)
#    /root/certs/couchdb/couchdb-cert (supported)
#    /root/certs/couchdb/couchdb-key (supported)
#    /root/certs/ldap/ldap-ca (supported)
#    /root/certs/ldap/ldap-cert (supported)
#    /root/certs/ldap/ldap-key (supported)
#    /root/certs/redis/.. (not yet supported)
MYSQLCA=false
if [[ -f /root/certs/mysql/server/mysql-ca ]] &&
   [[ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-ca ]]; then
    echo "copied over mysql-ca"
    cp /root/certs/mysql/server/mysql-ca /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-ca
    # for specific issue in docker and kubernetes that is required for successful openemr adodb/laminas connections
    MYSQLCA=true
fi
MYSQLCERT=false
if [[ -f /root/certs/mysql/server/mysql-cert ]] &&
   [[ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-cert ]]; then
    echo "copied over mysql-cert"
    cp /root/certs/mysql/server/mysql-cert /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-cert
    # for specific issue in docker and kubernetes that is required for successful openemr adodb/laminas connections
    MYSQLCERT=true
fi
MYSQLKEY=false
if [[ -f /root/certs/mysql/server/mysql-key ]] &&
   [[ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-key ]]; then
    echo "copied over mysql-key"
    cp /root/certs/mysql/server/mysql-key /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-key
    # for specific issue in docker and kubernetes that is required for successful openemr adodb/laminas connections
    MYSQLKEY=true
fi
if [[ -f /root/certs/couchdb/couchdb-ca ]] &&
   [[ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-ca ]]; then
    echo "copied over couchdb-ca"
    cp /root/certs/couchdb/couchdb-ca /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-ca
fi
if [[ -f /root/certs/couchdb/couchdb-cert ]] &&
   [[ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-cert ]]; then
    echo "copied over couchdb-cert"
    cp /root/certs/couchdb/couchdb-cert /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-cert
fi
if [[ -f /root/certs/couchdb/couchdb-key ]] &&
   [[ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-key ]]; then
    echo "copied over couchdb-key"
    cp /root/certs/couchdb/couchdb-key /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-key
fi
if [[ -f /root/certs/ldap/ldap-ca ]] &&
   [[ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-ca ]]; then
    echo "copied over ldap-ca"
    cp /root/certs/ldap/ldap-ca /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-ca
fi
if [[ -f /root/certs/ldap/ldap-cert ]] &&
   [[ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-cert ]]; then
    echo "copied over ldap-cert"
    cp /root/certs/ldap/ldap-cert /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-cert
fi
if [[ -f /root/certs/ldap/ldap-key ]] &&
   [[ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-key ]]; then
    echo "copied over ldap-key"
    cp /root/certs/ldap/ldap-key /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-key
fi

if [[ "${AUTHORITY}" = "yes" ]]; then
    if [[ "${CONFIG}" = "0" ]] &&
       [[ "${MYSQL_HOST}" != "" ]] &&
       [[ "${MYSQL_ROOT_PASS}" != "" ]] &&
       [[ "${EMPTY}" != "yes" ]] &&
       [[ "${MANUAL_SETUP}" != "yes" ]]; then

        echo "Running quick setup!"
        setup_retries=0
        setup_delay=1
        # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
        while ! auto_setup; do
            (( setup_retries++ ))
            if [[ ${setup_retries} -eq 1 ]]; then
            echo "Couldn't set up. Any of these reasons could be what's wrong:"
            echo " - You didn't spin up a MySQL container or connect your OpenEMR container to a mysql instance"
            echo " - MySQL is still starting up and wasn't ready for connection yet"
                echo " - The MySQL credentials were incorrect"
            elif [[ ${setup_retries} -le 5 ]]; then
                echo "Retrying setup (attempt ${setup_retries})..."
            fi
            # Use shorter initial delay, increase gradually (1s -> 2s)
            sleep "${setup_delay}"
            if [[ ${setup_retries} -lt 3 ]]; then
                setup_delay=1
            else
                setup_delay=2
            fi
        done
        echo "Setup Complete!"
    fi
fi

if [[ "${AUTHORITY}" = "yes" ]] &&
   [[ "${CONFIG}" = "1" ]] &&
   [[ "${MANUAL_SETUP}" != "yes" ]] &&
   [[ "${EASY_DEV_MODE}" != "yes" ]] &&
   [[ "${EMPTY}" != "yes" ]]; then
    # OpenEMR has been configured

    if [[ -f /var/www/localhost/htdocs/auto_configure.php ]]; then
        cd /var/www/localhost/htdocs/openemr/
        # This section only runs once after above configuration since auto_configure.php gets removed after this script
        echo "Setting file/dir permissions to 400/500 (optimized batch operations)"

        PERM_START=$(date +%s.%N 2>/dev/null || date +%s)

        # Optimized: Set all directories to 500 (batch operation using {} +)
        # Exclude sites/default/documents as it needs different permissions
        find . -type d -not -path "./sites/default/documents/*" -not -perm 500 -exec chmod 500 {} + 2>/dev/null || true

        # Optimized: Set all files to 400 (batch operation using {} +)
        # Exclude sites/default/documents and openemr.sh
        find . -type f -not -path "./sites/default/documents/*" -not -path './openemr.sh' -not -perm 400 -exec chmod 400 {} + 2>/dev/null || true

        echo "Default file permissions set, allowing writing to specific directories"
        chmod 700 /var/www/localhost/htdocs/openemr.sh 2>/dev/null || true

        # Set documents directory permissions (batch operation)
        if [[ -d sites/default/documents ]]; then
            find sites/default/documents -not -perm 700 -exec chmod 700 {} + 2>/dev/null || true
        fi

        PERM_END=$(date +%s.%N 2>/dev/null || date +%s)
        PERM_DURATION=0
        if command -v python3 >/dev/null 2>&1; then
            PERM_DURATION=$(python3 -c "print(round(${PERM_END} - ${PERM_START}, 2))" 2>/dev/null || echo "0")
        else
            PERM_DURATION=$((PERM_END - PERM_START))
        fi
        if [[ "${PERM_DURATION}" != "0" ]]; then
            echo "[TIMING] File permissions took ${PERM_DURATION}s"
        fi

        echo "Removing remaining setup scripts"
        # Remove all setup scripts
        rm -f admin.php acl_upgrade.php setup.php sql_patch.php sql_upgrade.php ippf_upgrade.php
        echo "Setup scripts removed, we should be ready to go now!"
        cd /var/www/localhost/htdocs/
    fi
fi

if [[ -f /var/www/localhost/htdocs/auto_configure.php ]]; then
    if [[ "${EASY_DEV_MODE_NEW}" = "yes" ]] || [[ "${INSANE_DEV_MODE}" = "yes" ]]; then
        # need to copy this script somewhere so the easy/insane dev environment can use it
        cp /var/www/localhost/htdocs/auto_configure.php /root/
        # save couchdb initial data folder to support devtools snapshots
        rsync --recursive --links /couchdb/data /couchdb/original/
    fi
    # trickery to support devtools in insane dev environment (note the easy dev does this with a shared volume)
    if [[ "${INSANE_DEV_MODE}" = "yes" ]]; then
        mkdir /openemr
        rsync --recursive --links /var/www/localhost/htdocs/openemr/sites /openemr/
    fi
fi

if ${MYSQLCA} ; then
    # for specific issue in docker and kubernetes that is required for successful openemr adodb/laminas connections
    echo "adjusted permissions for mysql-ca"
    chmod 744 /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-ca
fi
if ${MYSQLCERT} ; then
    # for specific issue in docker and kubernetes that is required for successful openemr adodb/laminas connections
    echo "adjusted permissions for mysql-cert"
    chmod 744 /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-cert
fi
if ${MYSQLKEY} ; then
    # for specific issue in docker and kubernetes that is required for successful openemr adodb/laminas connections
    echo "adjusted permissions for mysql-key"
    chmod 744 /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-key
fi

if [[ "${AUTHORITY}" = "yes" ]] &&
   [[ "${SWARM_MODE}" = "yes" ]] &&
   [[ -f /var/www/localhost/htdocs/auto_configure.php ]]; then
    # Set flag that the docker-leader configuration is complete
    touch /var/www/localhost/htdocs/openemr/sites/docker-completed
    rm -f /var/www/localhost/htdocs/openemr/sites/docker-leader
fi

# ensure the auto_configure.php script has been removed (unless in MANUAL_SETUP mode)
if [[ "${MANUAL_SETUP}" != "yes" ]]; then
rm -f /var/www/localhost/htdocs/auto_configure.php
fi

if [[ "${REDIS_SERVER}" != "" ]] &&
   [[ ! -f /etc/php-redis-configured ]]; then

    # Wait for Redis to be available before configuring sessions.
    # If Redis is configured but unreachable, fail loudly rather than silently
    # falling back to file sessions (which would break downstream orchestration).
    # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
    if ! wait_for_redis; then
        echo "ERROR: Redis server '${REDIS_SERVER}' is configured but not reachable after retries. Exiting."
        exit 1
    fi

    # Support the following redis auth:
    #   No username and No password set (using redis default user with nopass set)
    #   Both username and password set (using the redis user and pertinent password)
    #   Only password set (using redis default user and pertinent password)
    #   NOTE that only username set is not supported (in this case will ignore the username
    #      and use no username and no password set mode)
    # REDIS_SERVER may be "host" or "host:port"; avoid double port (tcp://host:6379:6379)
    if [[ "${REDIS_SERVER}" == *:* ]]; then
        REDIS_PATH="tcp://${REDIS_SERVER}"
    else
        REDIS_PATH="tcp://${REDIS_SERVER}:6379"
    fi
    if [[ "${REDIS_USERNAME}" != "" ]] &&
       [[ "${REDIS_PASSWORD}" != "" ]]; then
        echo "redis setup with username and password"
        REDIS_PATH="${REDIS_PATH}?auth[user]=${REDIS_USERNAME}&auth[pass]=${REDIS_PASSWORD}"
    elif [[ "${REDIS_PASSWORD}" != "" ]]; then
        echo "redis setup with password"
        # only a password, thus using the default user which redis has set a password for
        REDIS_PATH="${REDIS_PATH}?auth[pass]=${REDIS_PASSWORD}"
    else
        # no user or password, thus using the default user which is set to nopass in redis
        # so just keeping original REDIS_PATH: REDIS_PATH="${REDIS_PATH}"
        echo "redis setup"
    fi

    # Configure PHP to use Redis for sessions via conf.d include file
    {
        printf 'session.save_handler = redis\n'
        printf 'session.save_path = "%s"\n' "${REDIS_PATH}"
    } > "/etc/php${PHP_VERSION_ABBR?}/conf.d/99-redis-sessions.ini"

    # Verify configuration was applied correctly
    ACTUAL_HANDLER=$(php -r "echo ini_get('session.save_handler');")
    ACTUAL_PATH=$(php -r "echo ini_get('session.save_path');")

    if [[ "${ACTUAL_HANDLER}" != "redis" ]]; then
        echo "ERROR: Failed to configure session.save_handler. Expected 'redis', got '${ACTUAL_HANDLER}'"
        exit 1
    fi

    if [[ "${ACTUAL_PATH}" != "${REDIS_PATH}" ]]; then
        echo "ERROR: Failed to configure session.save_path. Expected '${REDIS_PATH}', got '${ACTUAL_PATH}'"
        exit 1
    fi

    echo "Redis session configuration verified successfully"

    # Ensure only configure this one time
    touch /etc/php-redis-configured
fi

# ============================================================================
# PHP EXTENSION CONFIGURATION (Coverage/Debug/Performance)
# ============================================================================
# Configure PHP extensions based on the requested mode:
#   - PCOV_ON=1: Lightweight code coverage (pcov), opcache enabled but no JIT
#   - XDEBUG_ON/XDEBUG_IDE_KEY: Full debugging (xdebug), opcache disabled
#   - Neither: Maximum performance with opcache JIT
#
# Note: PCOV and XDebug are mutually exclusive. PCOV takes precedence if both
# are set, as it's typically used in CI where only coverage is needed.

if [[ "${PCOV_ON}" = true ]]; then
   # PCOV mode: lightweight coverage collection
   # PCOV works with opcache but NOT with JIT (JIT interferes with coverage)
   sh pcov.sh
   if [[ ! -f /etc/php-opcache-jit-configured ]]; then
      # Keep opcache enabled but disable JIT for coverage accuracy
      echo "opcache.jit=off" >> "/etc/php${PHP_VERSION_ABBR?}/php.ini"
      touch /etc/php-opcache-jit-configured
   fi
elif [[ "${XDEBUG_IDE_KEY}" != "" ]] || [[ "${XDEBUG_ON}" = 1 ]]; then
   # XDebug mode: full debugging and profiling support
   sh xdebug.sh
   # XDebug requires opcache to be disabled
   if [[ ! -f /etc/php-opcache-jit-configured ]]; then
      echo "opcache.enable=0" >> "/etc/php${PHP_VERSION_ABBR?}/php.ini"
      touch /etc/php-opcache-jit-configured
   fi
else
   # Performance mode: opcache with JIT for maximum speed
   # Note: opcache is already enabled, just adding JIT settings
   if [[ ! -f /etc/php-opcache-jit-configured ]]; then
      {
        echo "opcache.jit=tracing"
        echo "opcache.jit_buffer_size=100M"
      } >> "/etc/php${PHP_VERSION_ABBR?}/php.ini"
      touch /etc/php-opcache-jit-configured
   fi
fi

# ============================================================================
# WAIT FOR SSL CERTIFICATE GENERATION
# ============================================================================
# Ensure SSL certificate generation completes before starting Apache.
# For leaders, ssl.sh failure should fail the script; for followers, tolerate failures.
if [[ -n "${SSL_PID:-}" ]] && ! wait "${SSL_PID}" 2>/dev/null; then
    [[ "${AUTHORITY}" = "yes" ]] && exit 1
    echo "Warning: Could not configure SSL certificates (may be read-only)"
fi
unset SSL_PID

echo
echo "Love OpenEMR? You can now support the project via the open collective:"
echo " > https://opencollective.com/openemr/donate"
echo

if [[ "${OPERATOR}" = "yes" ]]; then
    echo 'Starting apache!'
    exec /usr/sbin/httpd -D FOREGROUND
fi

echo 'OpenEMR configuration tasks have concluded.'
