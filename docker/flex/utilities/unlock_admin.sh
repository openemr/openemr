#!/bin/sh
# ============================================================================
# OpenEMR Admin User Unlock and Password Reset - Shell Wrapper
# ============================================================================
# This is a convenience wrapper script for unlock_admin.php.
# It changes to the /root directory where unlock_admin.php is located
# and executes the PHP script with the provided password argument.
#
# Usage:
#   ./unlock_admin.sh <new_password>
#
# Example:
#   ./unlock_admin.sh mynewpassword123
#
# This script is typically executed from within the OpenEMR container
# or via docker exec.
# ============================================================================

cd /root && php ./unlock_admin.php "$1"
