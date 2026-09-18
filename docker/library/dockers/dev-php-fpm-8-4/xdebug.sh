#!/usr/bin/env bash
# ============================================================================
# OpenEMR dev-php-fpm XDebug Configuration Script
# ============================================================================
# This script configures XDebug for PHP debugging, profiling, and coverage.
# Compatible with Debian-based php-fpm images using install-php-extensions.
#
# Environment Variables:
#   XDEBUG_ON           - Set to "1" to enable XDebug (required)
#   XDEBUG_IDE_KEY      - IDE key for debugging session (required if XDEBUG_ON not set)
#   XDEBUG_CLIENT_HOST  - Hostname/IP of the debugging client (optional)
#   XDEBUG_CLIENT_PORT  - Port for debugging connection (default: 9003)
#   XDEBUG_PROFILER_ON  - Set to "1" to enable profiling in addition to debugging
#   XDEBUG_MODE         - Override xdebug mode (e.g., "coverage" for CI)
#
# Usage:
#   Called by CI when coverage is enabled, or manually for debugging
# ============================================================================

set -euo pipefail

# ============================================================================
# NORMALIZE FLAGS
# ============================================================================
# Normalize boolean flags once at the start for consistent checking.

case ${XDEBUG_ON:-} in
    1|[Yy]es|[Tt]rue) xdebug_enabled=true ;;
    *) xdebug_enabled=false ;;
esac

case ${XDEBUG_PROFILER_ON:-} in
    1|[Yy]es|[Tt]rue) profiler_enabled=true ;;
    *) profiler_enabled=false ;;
esac

# ============================================================================
# VALIDATION
# ============================================================================
# Ensure XDebug is actually requested before proceeding.
# At least one of XDEBUG_ON or XDEBUG_IDE_KEY must be set.

if [[ -z "${XDEBUG_IDE_KEY:-}" && "${xdebug_enabled}" = false ]]; then
    echo "Error: XDebug requested but neither XDEBUG_ON nor XDEBUG_IDE_KEY is set" >&2
    exit 1
fi

# ============================================================================
# INSTALL AND CONFIGURE XDEBUG
# ============================================================================
# Install the XDebug PHP extension and configure it.
# This only runs once per container to avoid repeated installations.

XDEBUG_CONF_FILE="/usr/local/etc/php/conf.d/xdebug.ini"

if [[ ! -f /etc/php-xdebug-configured ]]; then
    echo "Installing XDebug extension..."

    # Install XDebug using install-php-extensions (works on Debian-based images)
    install-php-extensions xdebug

    # Determine xdebug mode
    if [[ -n "${XDEBUG_MODE:-}" ]]; then
        # Use explicitly set mode (e.g., "coverage" from CI)
        mode="${XDEBUG_MODE}"
    elif [[ "${profiler_enabled}" = true ]]; then
        mode="debug,profile"
    else
        mode="debug"
    fi

    # Configure XDebug
    {
        echo "; ========================================================================"
        echo "; XDebug Configuration"
        echo "; ========================================================================"
        echo
        echo "; XDebug mode: debug, profile, coverage, or combinations"
        echo "xdebug.mode=${mode}"
        echo
        echo "; Directory for XDebug output files (trace files, profiler output)"
        echo "xdebug.output_dir=/tmp"
        echo
        echo "; Start debugging session when triggered (via IDE or browser extension)"
        echo "xdebug.start_with_request=trigger"
        echo
        echo "; Log file for XDebug messages and errors"
        echo "xdebug.log=/tmp/xdebug.log"
        echo
        echo "; Automatically discover the client host (useful for Docker)"
        echo "xdebug.discover_client_host=1"
        echo
        echo "; Port for debugging connection (default: 9003)"
        echo "xdebug.client_port=${XDEBUG_CLIENT_PORT:-9003}"

        # Override client host if explicitly set
        if [[ -n "${XDEBUG_CLIENT_HOST:-}" ]]; then
            echo
            echo "; Manually configured client host (disables auto-discovery)"
            echo "xdebug.client_host=${XDEBUG_CLIENT_HOST}"
        fi

        # Set IDE key if provided
        if [[ -n "${XDEBUG_IDE_KEY:-}" ]]; then
            echo
            echo "; IDE key for debugging session"
            echo "xdebug.idekey=${XDEBUG_IDE_KEY}"
        fi

        # Profiler settings
        if [[ "${profiler_enabled}" = true || "${mode}" == *"profile"* ]]; then
            echo
            echo "; Profiler output filename pattern"
            echo "xdebug.profiler_output_name=cachegrind.out.%s"
        fi
        echo
    } > "${XDEBUG_CONF_FILE}"

    # Mark as configured
    touch /etc/php-xdebug-configured
    echo "XDebug installed and configured (mode: ${mode})"
fi

# ============================================================================
# ENSURE XDEBUG LOG FILE EXISTS
# ============================================================================
# Create XDebug log file if it doesn't exist and set appropriate permissions.
if [[ ! -f /tmp/xdebug.log ]]; then
    touch /tmp/xdebug.log
fi
chmod 666 /tmp/xdebug.log

echo "XDebug configuration completed"
