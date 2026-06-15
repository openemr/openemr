#!/usr/bin/env bash
# ============================================================================
# OpenEMR Apache Container Startup Script
# ============================================================================
# This script runs when the container starts and handles all setup tasks needed
# to get OpenEMR running under Apache. It performs automated installation,
# configuration, and startup coordination for OpenEMR in containerized environments.
#
# Key Features:
#   - Automated database setup and configuration
#   - Multi-container coordination (swarm mode)
#   - SSL/TLS certificate management
#   - Redis session configuration
#   - Upgrade detection and execution
#   - File permissions management
#   - Process control and error handling
#
# Environment Variables:
#   See the script sections below for detailed environment variable documentation
#
# Usage:
#   Called automatically by Docker CMD, but can be run manually for testing
# ============================================================================

set -euo pipefail

# ============================================================================
# PATH CONFIGURATION
# ============================================================================
# Define paths used throughout the script for OpenEMR installation and configuration

OE_ROOT="/var/www/localhost/htdocs/openemr"
AUTO_CONFIG="/var/www/localhost/htdocs/openemr/auto_configure.php"
SQLCONF_FILE="${OE_ROOT}/sites/default/sqlconf.php"

# ============================================================================
# SHELL LIBRARY SOURCING
# ============================================================================
# Load helper functions from devtoolsLibrary.source
# This provides utility functions for database operations, configuration, etc.

# shellcheck source=SCRIPTDIR/utilities/devtoolsLibrary.source
. /root/devtoolsLibrary.source

# ============================================================================
# DATABASE CONFIGURATION
# ============================================================================
# OpenEMR requires a MySQL/MariaDB database. These variables control the
# database connection and credentials. Defaults are provided for development.

MYSQL_HOST="${MYSQL_HOST:-mysql}"                        # Database server hostname
MYSQL_PORT="${MYSQL_PORT:-3306}"                         # Database server port
MYSQL_ROOT_USER="${MYSQL_ROOT_USER:-root}"               # Database root username
MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-root}"               # Database root password
MYSQL_USER="${MYSQL_USER:-openemr}"                      # OpenEMR database username
MYSQL_PASS="${MYSQL_PASS:-openemr}"                      # OpenEMR database password
MYSQL_DATABASE="${MYSQL_DATABASE:-openemr}"              # Database name
MYSQL_COLLATION="${MYSQL_COLLATION:-utf8mb4_general_ci}" # Character encoding

# ============================================================================
# OPENEMR ADMIN USER CONFIGURATION
# ============================================================================
# Initial administrator account created during first-time setup.
# IMPORTANT: Change these defaults in production!

OE_USER="${OE_USER:-admin}"                   # Initial admin username
OE_USER_NAME="${OE_USER_NAME:-Administrator}" # Admin user full name
OE_PASS="${OE_PASS:-pass}"                    # Initial admin password (CHANGE IN PRODUCTION!)

# ============================================================================
# OPERATION MODE SETTINGS
# ============================================================================
# Control container behavior for different deployment scenarios

MANUAL_SETUP="${MANUAL_SETUP:-no}"  # Set to "yes" to skip automatic setup
K8S="${K8S:-}"                      # Kubernetes mode: "admin" or "worker"
SWARM_MODE="${SWARM_MODE:-no}"      # Set to "yes" for multi-container coordination

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

# Kubernetes-specific role assignment
if [[ "${K8S}" = "admin" ]]; then
    OPERATOR=no
elif [[ "${K8S}" = "worker" ]]; then
    AUTHORITY=no
fi

# ============================================================================
# DATABASE WAITING FUNCTIONS
# ============================================================================

# Waits for MySQL/MariaDB to be ready to accept connections.
# This is critical because the database container may start simultaneously
# with this container, and databases need time to initialize.
#
# Retries up to 60 times (2 minutes total) with 2-second intervals.
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
        # Only print message every 10 seconds to reduce log noise
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
    
    # Try to connect to Redis
    local -i retries=10
    while (( retries-- > 0 )); do
        if command -v nc >/dev/null 2>&1 && nc -z "${redis_host}" "${redis_port}" >/dev/null 2>&1; then
            return 0
        fi
        sleep 1
    done
    
    echo "Warning: Redis at ${REDIS_SERVER} not available, using file sessions" >&2
    return 1
}

# Checks if OpenEMR has already been configured.
# Returns "1" if configured, "0" if not configured yet.
is_configured() {
    php -r "if (is_file('${SQLCONF_FILE}')) { require '${SQLCONF_FILE}'; echo isset(\$config) && \$config ? 1 : 0; } else { echo 0; }" 2>/dev/null | tail -1 || echo 0
}

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

# Handles swarm mode coordination: leader election and follower waiting.
handle_swarm_mode() {
    # Skip coordination if swarm mode isn't enabled
    if [[ "${SWARM_MODE}" != "yes" ]]; then
        return 0
    fi

    # Try to become the leader
    try_become_leader

    # If we're a follower, wait for the leader to finish setup
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
                # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
                if try_become_leader; then
                    echo "Successfully became leader after previous leader failure"
                    break
                fi
            fi
            sleep 10
            (( waited += 10 ))
        done
        
        # Try one more time to become leader (in case leader died just as we timed out)
        # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
        if [[ ! -f "${OE_ROOT}/sites/docker-completed" ]] && try_become_leader; then
            echo "Promoted to leader after waiting period"
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

    # If we're the leader, create initiation marker and send heartbeat
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

# ============================================================================
# CERTIFICATE MANAGEMENT
# ============================================================================

# Copies SSL/TLS certificates from /root/certs/ to where OpenEMR expects them.
# These certificates are used for secure connections to MySQL, Redis, LDAP, etc.
manage_certificates() {
    local certs_dir="/root/certs"
    local dest_dir="${OE_ROOT}/sites/default/documents/certificates"
    
    # Create destination directory if it doesn't exist
    mkdir -p "${dest_dir}"

    # Helper function to copy certificate files
    cp_cert_file() {
        local src="${certs_dir}/mysql/server/${1}"
        local dst="${dest_dir}/${1}"
        if [[ -f "${src}" && ! -f "${dst}" ]]; then
            cp "${src}" "${dst}"
            chmod 744 "${dst}"
            echo "Copied ${1}"
        fi
    }
    cp_cert_file mysql-ca
    cp_cert_file mysql-cert
    cp_cert_file mysql-key

    # Copy CouchDB certificates (if provided)
    if [[ -f "${certs_dir}/couchdb/couchdb-ca" ]] &&
       [[ ! -f "${dest_dir}/couchdb-ca" ]]; then
        cp "${certs_dir}/couchdb/couchdb-ca" "${dest_dir}/couchdb-ca"
        echo "Copied couchdb-ca"
    fi
    if [[ -f "${certs_dir}/couchdb/couchdb-cert" ]] &&
       [[ ! -f "${dest_dir}/couchdb-cert" ]]; then
        cp "${certs_dir}/couchdb/couchdb-cert" "${dest_dir}/couchdb-cert"
        echo "Copied couchdb-cert"
    fi
    if [[ -f "${certs_dir}/couchdb/couchdb-key" ]] &&
       [[ ! -f "${dest_dir}/couchdb-key" ]]; then
        cp "${certs_dir}/couchdb/couchdb-key" "${dest_dir}/couchdb-key"
        echo "Copied couchdb-key"
    fi

    # Copy LDAP certificates (if provided)
    if [[ -f "${certs_dir}/ldap/ldap-ca" ]] &&
       [[ ! -f "${dest_dir}/ldap-ca" ]]; then
        cp "${certs_dir}/ldap/ldap-ca" "${dest_dir}/ldap-ca"
        echo "Copied ldap-ca"
    fi
    if [[ -f "${certs_dir}/ldap/ldap-cert" ]] &&
       [[ ! -f "${dest_dir}/ldap-cert" ]]; then
        cp "${certs_dir}/ldap/ldap-cert" "${dest_dir}/ldap-cert"
        echo "Copied ldap-cert"
    fi
    if [[ -f "${certs_dir}/ldap/ldap-key" ]] &&
       [[ ! -f "${dest_dir}/ldap-key" ]]; then
        cp "${certs_dir}/ldap/ldap-key" "${dest_dir}/ldap-key"
        echo "Copied ldap-key"
    fi

    # Copy Redis certificates (if provided)
    if [[ -f "${certs_dir}/redis/redis-ca" ]] &&
       [[ ! -f "${dest_dir}/redis-ca" ]]; then
        cp "${certs_dir}/redis/redis-ca" "${dest_dir}/redis-ca"
        chmod 744 "${dest_dir}/redis-ca"
        echo "Copied redis-ca"
    fi
    if [[ -f "${certs_dir}/redis/redis-cert" ]] &&
       [[ ! -f "${dest_dir}/redis-cert" ]]; then
        cp "${certs_dir}/redis/redis-cert" "${dest_dir}/redis-cert"
        chmod 744 "${dest_dir}/redis-cert"
        echo "Copied redis-cert"
    fi
    if [[ -f "${certs_dir}/redis/redis-key" ]] &&
       [[ ! -f "${dest_dir}/redis-key" ]]; then
        cp "${certs_dir}/redis/redis-key" "${dest_dir}/redis-key"
        chmod 744 "${dest_dir}/redis-key"
        echo "Copied redis-key"
    fi
}

# ============================================================================
# REDIS SESSION CONFIGURATION
# ============================================================================

# Configures OpenEMR to use Redis for session storage instead of files.
# Redis provides faster session access and enables horizontal scaling.
configure_redis_sessions() {
    # Skip if Redis isn't configured
    [[ -z "${REDIS_SERVER:-}" ]] && return 0
    
    # Wait for Redis to be available
    # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
    if ! wait_for_redis; then
        return 1  # Redis unavailable, fall back to file sessions
    fi

    # Parse Redis server address
    local redis_host
    local redis_port
    IFS=: read -r redis_host redis_port _ <<< "${REDIS_SERVER}"
    redis_port="${redis_port:-6379}"
    
    local redis_path="${redis_host}:${redis_port}"
    local get_connector="?"
    
    if [[ -n "${REDIS_USERNAME:-}" && -n "${REDIS_PASSWORD:-}" ]]; then
        redis_path="${redis_path}?auth[user]=${REDIS_USERNAME}\&auth[pass]=${REDIS_PASSWORD}"
        get_connector="\&"
    elif [[ -n "${REDIS_PASSWORD:-}" ]]; then
        redis_path="${redis_path}?auth[pass]=${REDIS_PASSWORD}"
        get_connector="\&"
    fi

    # Add TLS/SSL encryption if requested
    if [[ "${REDIS_X509:-}" = "yes" ]]; then
        redis_path="tls://${redis_path}${get_connector}stream[cafile]=file://${OE_ROOT}/sites/default/documents/certificates/redis-ca\&stream[local_cert]=file://${OE_ROOT}/sites/default/documents/certificates/redis-cert\&stream[local_pk]=file://${OE_ROOT}/sites/default/documents/certificates/redis-key"
    elif [[ "${REDIS_TLS:-}" = "yes" ]]; then
        redis_path="tls://${redis_path}${get_connector}stream[cafile]=file://${OE_ROOT}/sites/default/documents/certificates/redis-ca"
    else
        redis_path="tcp://${redis_path}"
    fi

    # Update PHP configuration to use Redis for sessions
    # Use a separate config file instead of sed to avoid pattern matching issues
    # (see openemr/openemr#9473)
    echo "Configuring Redis sessions: ${redis_path}"
    # shellcheck disable=SC2154  # PHP_VERSION_ABBR is set by environment/Dockerfile
    {
        echo "session.save_handler = redis"
        echo "session.save_path = \"${redis_path}\""
    } > "/etc/php${PHP_VERSION_ABBR}/conf.d/99-redis-sessions.ini"
    
    # Only create marker file if configuration was successful
    # This prevents false positives when Redis is unavailable
    touch /etc/php-redis-configured
    return 0
}

# ============================================================================
# UPGRADE DETECTION AND EXECUTION
# ============================================================================

# Checks if OpenEMR needs to be upgraded to a newer version.
# Compares version numbers from different locations to determine if upgrade is needed.
check_upgrade() {
    # Only the leader container handles upgrades
    if [[ "${AUTHORITY}" != "yes" ]]; then
        return 0
    fi

    # Read version numbers from different locations
    # Use integer types to guarantee numeric values defaulting to 0
    local -i docker_version_root=0
    local -i docker_version_code=0
    local -i docker_version_sites=0

    [[ -f /root/docker-version ]] && docker_version_root=$(cat /root/docker-version 2>/dev/null || echo 0)
    [[ -f "${OE_ROOT}/docker-version" ]] && docker_version_code=$(cat "${OE_ROOT}/docker-version" 2>/dev/null || echo 0)
    [[ -f "${OE_ROOT}/sites/default/docker-version" ]] && docker_version_sites=$(cat "${OE_ROOT}/sites/default/docker-version" 2>/dev/null || echo 0)

    # Upgrade needed if container/code versions match but sites version is older
    if [[ "${docker_version_root}" = "${docker_version_code}" ]] &&
       [[ "${docker_version_root}" -gt "${docker_version_sites}" ]]; then
        echo "Upgrade detected: ${docker_version_sites} -> ${docker_version_root}"
        run_upgrade
        return 0
    fi

    return 0
}

# Performs the actual OpenEMR upgrade by running upgrade scripts.
# Updates leader heartbeat periodically during execution to prevent stale leader detection.
run_upgrade() {
    echo "Starting OpenEMR upgrade process..."
    
    # Update heartbeat before starting upgrade
    [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
    
    # Verify OpenEMR is configured before upgrading
    local config_state
    config_state=$(is_configured)
    if [[ "${config_state}" != "1" ]]; then
        echo "Error: Cannot upgrade - OpenEMR is not configured yet" >&2
        return 1
    fi

    # Wait for MySQL to be available
    wait_for_mysql
    
    # Update heartbeat after MySQL wait
    [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
    
    # Read version numbers
    local docker_version_root
    docker_version_root=$(cat /root/docker-version 2>/dev/null || echo 0)
    local docker_version_sites
    docker_version_sites=$(cat "${OE_ROOT}/sites/default/docker-version" 2>/dev/null || echo 0)
    
    # Run filesystem upgrade scripts in sequence
    local c=${docker_version_sites}
    while [[ "${c}" -le "${docker_version_root}" ]]; do
        if [[ "${c}" -gt "${docker_version_sites}" ]]; then
            echo "Start: Processing fsupgrade-${c}.sh upgrade script"
            # Update heartbeat before each upgrade script
            [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
            sh "/root/fsupgrade-${c}.sh"
            echo "Completed: Processing fsupgrade-${c}.sh upgrade script"
            # Update heartbeat after each upgrade script
            [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
        fi
        (( c++ ))
    done
    
    # Update version marker
    echo -n "${docker_version_root}" > "${OE_ROOT}/sites/default/docker-version"
    echo "Version marker updated to: ${docker_version_root}"
    
    # Final heartbeat update
    [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
    
    echo "OpenEMR upgrade completed successfully"
}

# ============================================================================
# OPENEMR AUTO-CONFIGURATION
# ============================================================================

# Runs OpenEMR's automated setup script with performance optimizations.
# Updates leader heartbeat periodically during execution to prevent stale leader detection.
run_auto_configure() {
    [[ ! -f "${AUTO_CONFIG}" ]] && echo "auto_configure.php not found" >&2 && return 0

    echo "Running OpenEMR auto configuration..."

    # Update heartbeat before starting long-running operation
    [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
    
    # Create temporary file cache directory for opcache
    TMP_FILE_CACHE_LOCATION="/tmp/php-file-cache"
    mkdir -p "${TMP_FILE_CACHE_LOCATION}"

    # Create optimized PHP configuration for installation
    {
        echo "opcache.enable=1"
        echo "opcache.enable_cli=1"
        echo "opcache.file_cache=${TMP_FILE_CACHE_LOCATION}"
        echo "opcache.file_cache_only=1"
        echo "opcache.file_cache_consistency_checks=1"
        echo "opcache.enable_file_override=1"
        echo "opcache.max_accelerated_files=1000000"
    } > auto_configure.ini

    # Prepare configuration string using devtoolsLibrary function
    prepareVariables

    # Update heartbeat right before the long-running PHP command
    [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat

    # Run auto_configure with optimized PHP settings
    # Note: CONFIGURATION is a space-separated string like "server=mysql rootpass=root loginhost=%"
    # We need to split it and pass as separate arguments, not use -f flag (which doesn't exist)
    # Split CONFIGURATION into an array and pass each element as a separate argument
    read -r -a config_args <<< "${CONFIGURATION}"
    php -c auto_configure.ini auto_configure.php "${config_args[@]}" || return 1

    # Update heartbeat after PHP execution completes
    [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat

    # Clean up temporary files
    rm -rf "${TMP_FILE_CACHE_LOCATION}"
    rm -f auto_configure.ini

    # Verify configuration succeeded
    CONFIG=$(php -r "require_once('${SQLCONF_FILE}'); echo \$config;")
    if [[ "${CONFIG}" = "0" ]]; then
        echo "Error in auto-config. Configuration failed." >&2
        return 1
    fi

    echo "OpenEMR configured successfully"
}

# Removes OpenEMR's setup/installation scripts after initial configuration.
# These scripts are only needed during installation - after that, they're a
# security risk because they could allow someone to reconfigure or break OpenEMR.
cleanup_setup_scripts() {
    local config_state
    config_state=$(is_configured)
    
    # Only remove setup scripts if OpenEMR is configured
    if [[ "${config_state}" = "1" ]] && [[ -f "${AUTO_CONFIG}" ]]; then
        echo "Removing setup scripts (keeping upgrade scripts for future upgrades)..."
        # Remove only the initial installation scripts (not upgrade scripts)
        rm -f "${OE_ROOT}/admin.php" \
              "${OE_ROOT}/setup.php" \
              "${OE_ROOT}/auto_configure.php" \
              "${OE_ROOT}/acl_upgrade.php" \
              "${OE_ROOT}/sql_patch.php" \
              "${OE_ROOT}/sql_upgrade.php" \
              "${OE_ROOT}/ippf_upgrade.php"
        echo "Setup scripts removed (upgrade scripts preserved)"
    fi
}

# ============================================================================
# MAIN EXECUTION FLOW
# ============================================================================

# Initialize timing for performance analysis
SCRIPT_START_TIME=$(date +%s.%N 2>/dev/null || date +%s)
log_timing() {
    local step_name="$1"
    local current_time
    current_time=$(date +%s.%N 2>/dev/null || date +%s)
    local elapsed
    if command -v python3 >/dev/null 2>&1; then
        elapsed=$(python3 -c "print(round(${current_time} - ${SCRIPT_START_TIME}, 2))" 2>/dev/null || echo "0")
    else
        elapsed=$((current_time - SCRIPT_START_TIME))
    fi
    echo "[TIMING] Step ${step_name}: ${elapsed}s elapsed"
}

log_timing "0-Start"

# Step 1: Handle swarm mode coordination (if enabled)
handle_swarm_mode
[[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
log_timing "1-SwarmMode"

# Step 2: Configure SSL/TLS certificates
# Leaders generate certificates, followers wait for them or generate their own if needed
# Run in parallel with certificate management since they're independent operations
if [[ "${AUTHORITY}" = "yes" ]]; then
    sh ssl.sh &
    SSL_PID=$!
    SSL_IS_LEADER=1
else
    # Followers: try to generate self-signed certs if they don't exist
    # This handles cases where /etc/ssl is not shared between containers
    if [[ ! -f /etc/ssl/certs/webserver.cert.pem ]] || [[ ! -f /etc/ssl/private/webserver.key.pem ]]; then
        sh ssl.sh &
        SSL_PID=$!
        SSL_IS_LEADER=0
    fi
fi

# Step 3: Check for upgrades
check_upgrade
[[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
log_timing "3-UpgradeCheck"

# Step 4: Verify configuration exists (critical check for worker containers)
CONFIG=$(php -r "require_once('${SQLCONF_FILE}'); echo \$config;")
if [[ "${AUTHORITY}" = "no" ]] && [[ "${CONFIG}" = "0" ]]; then
    echo "Critical failure! An OpenEMR worker is trying to run on a missing configuration." >&2
    echo " - Is this due to a Kubernetes grant hiccup?" >&2
    echo "The worker will now terminate." >&2
    exit 1
fi
log_timing "4-ConfigCheck"

# Step 5: Copy SSL/TLS certificates for MySQL, Redis, LDAP, CouchDB
# Run in parallel with webserver SSL generation (independent operations)
# Must complete before Redis config if using TLS certificates
manage_certificates &
CERT_COPY_PID=$!

# Wait for both SSL generation and certificate copying to complete
if [[ -n "${SSL_PID:-}" ]]; then
    # For leaders, ssl.sh failure should fail the script; for followers, tolerate failures
    if [[ "${SSL_IS_LEADER:-0}" = "1" ]]; then
        wait "${SSL_PID}"
    else
        wait "${SSL_PID}" 2>/dev/null || echo "Warning: Could not configure SSL certificates (may be read-only)"
    fi
    unset SSL_PID SSL_IS_LEADER
fi
wait "${CERT_COPY_PID}" 2>/dev/null || true
unset CERT_COPY_PID

[[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
log_timing "2-SSL+5-Certificates"

# Step 6: Run auto-configuration (if needed)
# If Redis is configured, start waiting for it in parallel with MySQL wait
# since they're independent services and we'll need both eventually
AUTO_CONFIG_START=$(date +%s.%N 2>/dev/null || date +%s)
if [[ "${AUTHORITY}" = "yes" ]]; then
    if [[ "${CONFIG}" = "0" ]] &&
       [[ "${MYSQL_HOST}" != "" ]] &&
       [[ "${MYSQL_ROOT_PASS}" != "" ]] &&
       [[ "${MANUAL_SETUP}" != "yes" ]]; then
        # Start waiting for Redis in background if needed
        # (Redis wait is non-blocking and will be checked again later)
        if [[ -n "${REDIS_SERVER:-}" ]]; then
            wait_for_redis &
            REDIS_WAIT_PID=$!
        fi
        
        echo "Running quick setup!"
        setup_retries=0
        setup_delay=1
        # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
        while ! run_auto_configure; do
            (( setup_retries++ ))
            # Update heartbeat during retries to show leader is still active
            [[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
            if [[ ${setup_retries} -eq 1 ]]; then
                echo "Couldn't set up. Any of these reasons could be what's wrong:"
                echo " - You didn't spin up a MySQL container or connect your OpenEMR container to a mysql instance"
                echo " - MySQL is still starting up and wasn't ready for connection yet"
                echo " - The MySQL credentials were incorrect"
            elif [[ ${setup_retries} -le 5 ]]; then
                echo "Retrying setup (attempt ${setup_retries})..."
            fi
            # Use shorter initial delay, increase gradually (1s -> 1.5s -> 2s)
            sleep "${setup_delay}"
            if [[ ${setup_retries} -lt 3 ]]; then
                setup_delay=1
            elif [[ ${setup_retries} -lt 5 ]]; then
                setup_delay=2
            else
                setup_delay=2
            fi
        done
        
        # Wait for Redis wait process to complete if it was started
        if [[ -n "${REDIS_WAIT_PID:-}" ]]; then
            wait "${REDIS_WAIT_PID}" 2>/dev/null || true
            unset REDIS_WAIT_PID
        fi
        
        AUTO_CONFIG_END=$(date +%s.%N 2>/dev/null || date +%s)
        AUTO_CONFIG_DURATION=0
        if command -v python3 >/dev/null 2>&1; then
            AUTO_CONFIG_DURATION=$(python3 -c "print(round(${AUTO_CONFIG_END} - ${AUTO_CONFIG_START}, 2))" 2>/dev/null || echo "0")
        else
            AUTO_CONFIG_DURATION=$((AUTO_CONFIG_END - AUTO_CONFIG_START))
        fi
        echo "[TIMING] Auto-configuration took ${AUTO_CONFIG_DURATION}s"
        echo "Setup Complete!"
        
        # Set global settings from environment variables
        # Ensure variables are prepared before calling setGlobalSettings
        prepareVariables
        # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
        setGlobalSettings || true
        
        # Create version markers after successful installation
        if [[ -f /root/docker-version ]]; then
            installed_version=$(cat /root/docker-version 2>/dev/null || echo 0)
            echo "${installed_version}" > "${OE_ROOT}/sites/default/docker-version" 2>/dev/null || true
            echo "${installed_version}" > "${OE_ROOT}/docker-version" 2>/dev/null || true
            echo "Version marker created: ${installed_version}"
        fi
    fi
fi
[[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
log_timing "6-AutoConfig"

# Step 7: Update global settings if already configured
if [[ "${AUTHORITY}" = "yes" ]] &&
   [[ "${CONFIG}" = "1" ]] &&
   [[ "${MANUAL_SETUP}" != "yes" ]]; then
    # Ensure variables are prepared before calling setGlobalSettings
    prepareVariables
    # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
    setGlobalSettings || true
fi
[[ "${AUTHORITY}" = "yes" ]] && update_leader_heartbeat
log_timing "7-GlobalSettings"

# Step 8: Configure Redis sessions (if available)
log_timing "8-RedisStart"
if [[ -n "${REDIS_SERVER:-}" ]] && [[ ! -f /etc/php-redis-configured ]]; then
    # Support phpredis build from source (if PHPREDIS_BUILD is set)
    if [[ "${PHPREDIS_BUILD:-}" != "" ]]; then
      apk update
      apk del --no-cache "php${PHP_VERSION_ABBR}-redis"
      apk add --no-cache git "php${PHP_VERSION_ABBR}-dev" "php${PHP_VERSION_ABBR}-pecl-igbinary" gcc make g++
      mkdir /tmpredis
      cd /tmpredis
      git clone https://github.com/phpredis/phpredis.git
      cd /tmpredis/phpredis
      if [[ "${PHPREDIS_BUILD}" != "develop" ]]; then
          git reset --hard "${PHPREDIS_BUILD}"
      fi
      phpize83
      ./configure --with-php-config=/usr/bin/php-config83 --enable-redis-igbinary
      nproc_output=$(nproc --all) || nproc_output=1
      make -j "${nproc_output}"
      make install
      echo "extension=redis" > "/etc/php${PHP_VERSION_ABBR}/conf.d/20_redis.ini"
      rm -fr /tmpredis/phpredis
      apk del --no-cache git "php${PHP_VERSION_ABBR}-dev" gcc make g++
      cd "${OE_ROOT}"
    fi
    
    # Only create marker if configuration succeeds
    # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
    if configure_redis_sessions; then
        echo "Redis session configuration completed"
    else
        echo "Redis session configuration skipped (Redis unavailable)"
    fi
fi
log_timing "8-Redis"

# Step 9: Finalize permissions and cleanup setup scripts
# Note: Most file permissions are pre-set during Docker build (400 for files, 500 for dirs).
# This step only needs to lock down files that were writable during setup.
PERM_START=$(date +%s.%N 2>/dev/null || date +%s)
if [[ "${AUTHORITY}" = "yes" ]] || [[ "${SWARM_MODE}" = "yes" ]]; then
    if [[ "${CONFIG}" = "1" ]] && [[ "${MANUAL_SETUP}" != "yes" ]]; then
        if [[ -f "${AUTO_CONFIG}" ]]; then
            # This section only runs once after initial setup since auto_configure.php gets removed
            
            echo "Finalizing file permissions after setup..."
            
            # Lock down sqlconf.php (was writable during setup, now secure it)
            chmod 400 sites/default/sqlconf.php 2>/dev/null || true
            
            # Lock down sites/default directory
            chmod 500 sites/default 2>/dev/null || true
            
            # Ensure openemr.sh stays executable
            chmod 700 openemr.sh 2>/dev/null || true
            
            echo "File permissions finalized"
            
            cleanup_setup_scripts
        fi
    fi
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
log_timing "9-Permissions"

# Step 10: Fix certificate permissions (only once in swarm mode) - OPTIMIZED
# Batch certificate permission fixes for better performance
if [[ "${SWARM_MODE}" != "yes" ]] ||
   [[ ! -f "${OE_ROOT}/sites/docker-completed" ]]; then
    cert_dir="${OE_ROOT}/sites/default/documents/certificates"
    if [[ -d "${cert_dir}" ]]; then
        # Batch fix all certificate permissions in one operation
        find "${cert_dir}" -type f \( -name "mysql-*" -o -name "redis-*" -o -name "ldap-*" -o -name "couchdb-*" \) \
            ! -perm 744 -exec chmod 744 {} + 2>/dev/null || true
    fi
fi
log_timing "10-CertPerms"

# Step 11: Configure XDebug or PHP optimizations
if [[ "${XDEBUG_IDE_KEY:-}" != "" ]] || [[ "${XDEBUG_ON:-}" = 1 ]]; then
    sh xdebug.sh
    # Disable opcache when XDebug is enabled (they're incompatible)
    if [[ ! -f /etc/php-opcache-jit-configured ]]; then
        echo "opcache.enable=0" >> "/etc/php${PHP_VERSION_ABBR}/php.ini"
        touch /etc/php-opcache-jit-configured
    fi
else
    # Configure opcache JIT if XDebug is not being used
    if [[ ! -f /etc/php-opcache-jit-configured ]]; then
        echo "opcache.jit=tracing" >> "/etc/php${PHP_VERSION_ABBR}/php.ini"
        echo "opcache.jit_buffer_size=100M" >> "/etc/php${PHP_VERSION_ABBR}/php.ini"
        touch /etc/php-opcache-jit-configured
    fi
fi

# Step 12: Mark swarm completion (if in swarm mode)
if [[ "${AUTHORITY}" = "yes" ]] && [[ "${SWARM_MODE}" = "yes" ]]; then
    touch "${OE_ROOT}/sites/docker-completed"
    rm -f "${OE_ROOT}/sites/docker-leader"
fi

# Step 13: Signal instance ready (swarm mode only)
if [[ "${SWARM_MODE}" = "yes" ]]; then
    echo
    echo "swarm mode on: this instance is ready"
    echo
    touch /root/instance-swarm-ready
fi

# Step 14: Display support message
echo
echo "Love OpenEMR? You can now support the project via the open collective:"
echo " > https://opencollective.com/openemr/donate"
echo

# Step 15: Start Apache (if this container is an operator)
log_timing "15-PreApache"
if [[ "${OPERATOR}" = "yes" ]]; then
    SCRIPT_END_TIME=$(date +%s.%N 2>/dev/null || date +%s)
    TOTAL_DURATION=0
    if command -v python3 >/dev/null 2>&1; then
        TOTAL_DURATION=$(python3 -c "print(round(${SCRIPT_END_TIME} - ${SCRIPT_START_TIME}, 2))" 2>/dev/null || echo "0")
    else
        TOTAL_DURATION=$((SCRIPT_END_TIME - SCRIPT_START_TIME))
    fi
    echo "[TIMING] Total script execution time: ${TOTAL_DURATION}s before Apache start"
    echo 'Starting Apache!'
    exec /usr/sbin/httpd -D FOREGROUND
fi

# If not an operator, exit gracefully
echo 'OpenEMR configuration tasks have concluded.'
