<?php
/**
 * ============================================================================
 * OpenEMR Admin Account Unlock and Password Reset Utility
 * ============================================================================
 *
 * Purpose:
 *   Unlocks the admin user account (user ID 1) and changes the password
 *   from the default 'pass' to a user-supplied password.
 *
 * Usage:
 *   php unlock_admin.php <new_password>
 *
 * Example:
 *   php unlock_admin.php MyNewSecurePassword123
 *
 * Requirements:
 *   - Must be run from within the OpenEMR container
 *   - Requires access to OpenEMR globals.php and database
 *   - Admin user (ID 1) must exist in the database
 *
 * Security Notes:
 *   - Sets $GLOBALS['secure_password'] = 0 to bypass password complexity checks
 *   - Uses AuthUtils to properly hash the new password
 *   - Intended for password recovery scenarios in Docker environments
 *
 * ============================================================================
 */

// Get new password from command line argument
$newPassword = $argv[1];

// Set site context to 'default'
$_GET['site'] = 'default';

// Bypass authentication checks (required for utility scripts)
$ignoreAuth = 1;

// Load OpenEMR globals and database connection
require_once("/var/www/localhost/htdocs/openemr/interface/globals.php");

// Use OpenEMR's AuthUtils
// See code for AuthUtils here: https://github.com/openemr/openemr/blob/master/src/Common/Auth/AuthUtils.php
use OpenEMR\Common\Auth\AuthUtils;

// Disable password complexity requirements for recovery scenarios
$GLOBALS['secure_password'] = 0;

// Unlock the admin user account (set active = 1)
sqlStatement("UPDATE `users` SET `active` = 1 WHERE `id` = 1");

// Update password from default 'pass' to user-supplied password
$currentPassword = "pass";
$unlockUpdatePassword = new AuthUtils();
$unlockUpdatePassword->updatePassword(1, 1, $currentPassword, $newPassword);

// Display error message if password update failed
if (!empty($unlockUpdatePassword->getErrorMessage())) {
    echo "ERROR: " . $unlockUpdatePassword->getErrorMessage() . "\n";
}
