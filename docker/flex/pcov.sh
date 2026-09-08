#!/usr/bin/env bash
# ============================================================================
# OpenEMR Flex PCOV Configuration Script
# ============================================================================
# This script configures PCOV for PHP code coverage collection.
# PCOV is a lightweight alternative to XDebug specifically designed for
# code coverage, offering significantly faster execution.
#
# Environment Variables:
#   PCOV_ON - Set to "1" to enable PCOV for code coverage
#
# Note:
#   - PCOV and XDebug cannot be loaded simultaneously
#   - PCOV is coverage-only; use XDebug if you need debugging/profiling
#   - PCOV works with opcache enabled (unlike XDebug in debug mode)
#
# Usage:
#   Called automatically by openemr.sh when PCOV is enabled
# ============================================================================

set -euo pipefail

# ============================================================================
# VALIDATION
# ============================================================================
# Ensure PCOV is actually requested before proceeding.

# PCOV_ON is normalized to "true"/"false" by openemr.sh before calling this script
# shellcheck disable=SC2154
if [[ "${PCOV_ON}" != true ]]; then
    echo "Error: PCOV script called but PCOV_ON is not enabled" >&2
    exit 1
fi

# ============================================================================
# INSTALL AND CONFIGURE PCOV
# ============================================================================
# Install the PCOV PHP extension and configure it in php.ini.
# This only runs once per container to avoid repeated installations.

if [[ ! -f /etc/php-pcov-configured ]]; then
    echo "Installing PCOV extension..."

    # Install PCOV extension from Alpine package repository
    apk update
    apk add --no-cache "php${PHP_VERSION_ABBR?}-pecl-pcov"

    # Configure PCOV in php.ini
    # Note: PHP_VERSION_ABBR is set by the Dockerfile (e.g., "84" for PHP 8.4)
    {
        echo "; ========================================================================"
        echo "; PCOV Configuration (Code Coverage)"
        echo "; ========================================================================"
        echo "; Load PCOV extension"
        echo "extension=pcov"
        echo ""
        echo "; Enable PCOV (can be disabled at runtime via pcov.enabled=0)"
        echo "pcov.enabled=1"
        echo ""
        echo "; Directory filter - only collect coverage for files under this path"
        echo "; Default covers the OpenEMR installation directory"
        echo "pcov.directory=/var/www/localhost/htdocs/openemr"
        echo ""
        echo "; Exclude vendor and node_modules from coverage collection"
        echo "pcov.exclude=\"~/(vendor|node_modules)/~\""
        echo ""
    } >> "/etc/php${PHP_VERSION_ABBR}/php.ini"

    # Ensure only configure this one time
    touch /etc/php-pcov-configured
    echo "PCOV installed and configured"
fi

echo "PCOV configuration completed"
