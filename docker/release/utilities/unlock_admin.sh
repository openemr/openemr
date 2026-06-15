#!/bin/sh
# ============================================================================
# OpenEMR Admin Unlock Wrapper Script
# ============================================================================
# 
# Purpose:
#   Convenience wrapper script for unlock_admin.php that ensures proper
#   working directory and passes the new password argument through.
# 
# Usage:
#   ./unlock_admin.sh <new_password>
# 
# Example:
#   ./unlock_admin.sh MyNewSecurePassword123
# 
# Requirements:
#   - Must be run from within the OpenEMR container
#   - Requires unlock_admin.php to be present in /root/
# 
# ============================================================================

# Change to /root directory where unlock_admin.php is located
cd /root && php ./unlock_admin.php "$1"
