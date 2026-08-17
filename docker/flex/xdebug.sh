#!/usr/bin/env bash
# ============================================================================
# OpenEMR Flex XDebug Configuration Script
# ============================================================================
# This script configures XDebug for PHP debugging and profiling.
# XDebug is a powerful debugging and profiling tool for PHP applications.
#
# Environment Variables:
#   XDEBUG_ON           - Set to "1" to enable XDebug (required)
#   XDEBUG_IDE_KEY      - IDE key for debugging session (required if XDEBUG_ON not set)
#   XDEBUG_CLIENT_HOST  - Hostname/IP of the debugging client (optional)
#   XDEBUG_CLIENT_PORT  - Port for debugging connection (default: 9003)
#   XDEBUG_PROFILER_ON  - Set to "1" to enable profiling in addition to debugging
#
# Usage:
#   Called automatically by openemr.sh when XDebug is enabled
# ============================================================================

set -euo pipefail

# ============================================================================
# VALIDATION
# ============================================================================
# Ensure XDebug is actually requested before proceeding.
# At least one of XDEBUG_ON or XDEBUG_IDE_KEY must be set.

if [[ "${XDEBUG_IDE_KEY:-}" = "" ]] && [[ "${XDEBUG_ON:-}" != 1 ]]; then
    echo "Error: XDebug requested but neither XDEBUG_ON nor XDEBUG_IDE_KEY is set" >&2
    exit 1
fi

# ============================================================================
# INSTALL AND CONFIGURE XDEBUG
# ============================================================================
# Install the XDebug PHP extension and configure it in php.ini.
# This only runs once per container to avoid repeated installations.

if [[ ! -f /etc/php-xdebug-configured ]]; then
    echo "Installing XDebug extension..."

    # Install XDebug extension from Alpine package repository
    apk update
    apk add --no-cache "php${PHP_VERSION_ABBR?}-pecl-xdebug"

    # Configure XDebug in php.ini
    # Note: PHP_VERSION_ABBR is set by the Dockerfile (e.g., "84" for PHP 8.4)
    {
        echo "; ========================================================================"
        echo "; XDebug Configuration"
        echo "; ========================================================================"
        echo "; Load XDebug extension"
        echo "zend_extension=/usr/lib/php${PHP_VERSION_ABBR}/modules/xdebug.so"
        echo ""
        echo "; Directory for XDebug output files (trace files, profiler output)"
        echo "xdebug.output_dir=/tmp"
        echo ""
        echo "; Start debugging session when triggered (via IDE or browser extension)"
        echo "xdebug.start_with_request=trigger"
        echo ""
        echo "; Use DBGp protocol (standard debugging protocol)"
        echo "xdebug.remote_handler=dbgp"
        echo ""
        echo "; Log file for XDebug messages and errors"
        echo "xdebug.log=/tmp/xdebug.log"
        echo ""
        echo "; Automatically discover the client host (useful for Docker)"
        echo "xdebug.discover_client_host=1"
        echo ""

        # Configure debugging mode (with optional profiling)
        if [[ "${XDEBUG_PROFILER_ON:-}" = 1 ]]; then
            echo "; Enable both debugging and profiling"
            echo "xdebug.mode=debug,profile"
            echo ""
            echo "; Profiler output filename pattern"
            echo "xdebug.profiler_output_name=cachegrind.out.%s"
        else
            echo "; Enable debugging only"
            echo "xdebug.mode=debug"
        fi
        echo ""

        # Configure client connection settings
        echo "; Port for debugging connection (default: 9003)"
        echo "xdebug.client_port=${XDEBUG_CLIENT_PORT:-9003}"

        # Override client host if explicitly set (useful for specific network configs)
        if [[ "${XDEBUG_CLIENT_HOST:-}" != "" ]]; then
            echo "; Manually configured client host (disables auto-discovery)"
            echo "xdebug.client_host=${XDEBUG_CLIENT_HOST}"
        fi

        # Set IDE key if provided (for IDE-specific configurations)
        if [[ "${XDEBUG_IDE_KEY:-}" != "" ]]; then
            echo "; IDE key for debugging session"
            echo "xdebug.idekey=${XDEBUG_IDE_KEY}"
        fi
        echo ""
    } >> "/etc/php${PHP_VERSION_ABBR}/php.ini"

    # Ensure only configure this one time
    touch /etc/php-xdebug-configured
    echo "XDebug installed and configured"
fi

# ============================================================================
# ENSURE XDEBUG LOG FILE EXISTS
# ============================================================================
# Create XDebug log file if it doesn't exist and set appropriate permissions.
# This prevents 'Xdebug: [Log Files] File could not be opened' error messages.
# Note: /tmp may be cleared between container restarts, so this runs every time.
if [[ ! -f /tmp/xdebug.log ]]; then
    touch /tmp/xdebug.log
fi
chmod 666 /tmp/xdebug.log

echo "XDebug configuration completed"
