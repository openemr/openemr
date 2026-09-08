<?php

/**
 * =======================================
 * OpenEMR Automated Configuration Script
 * =======================================
 * This script performs the initial setup and configuration of OpenEMR when
 * the container starts for the first time. It's like an automated installer
 * that sets up the database, creates all necessary tables, and configures
 * OpenEMR for use.
 *
 * What this script does:
 *   1. Loads OpenEMR's installer class (from Composer autoloader)
 *   2. Sets up default configuration values (database, admin user, etc.)
 *   3. Allows these defaults to be overridden via command-line arguments
 *   4. Runs the OpenEMR installer to create the database and initial setup
 *
 * This script is called automatically by openemr.sh during container
 * startup, but can also be run manually if needed.
 *
 * Usage:
 *   php auto_configure.php [key=value] [key=value] ...
 *   php auto_configure.php -f "key=value key=value ..."
 *
 * Example:
 *   php auto_configure.php server=mysql rootpass=secret iuserpass=secure123
 *   php auto_configure.php -f "server=mysql rootpass=secret login=openemr"
 * ============================================================================
 */

// Load OpenEMR's Composer autoloader
// This gives us access to all OpenEMR classes, including the Installer class
require_once('/var/www/localhost/htdocs/openemr/vendor/autoload.php');

// Support older OpenEMR versions (5.0.2 and lower) that may not have
// password_hashing.php in the vendor directory
if (file_exists('/var/www/localhost/htdocs/openemr/library/authentication/password_hashing.php')) {
    // This is to support older code (OpenEMR 5.0.2 and lower)
    require_once('/var/www/localhost/htdocs/openemr/library/authentication/password_hashing.php');
}

use OpenEMR\Common\Logging\SystemLogger;

// ============================================================================
// DEFAULT CONFIGURATION SETTINGS
// ============================================================================
// These are the default values that will be used if not overridden via
// command-line arguments. These defaults are suitable for a typical Docker
// development environment, but should be changed in production.
//
// The openemr.sh script passes custom values via command-line arguments
// (e.g., "server=mysql", "rootpass=secret") to override these defaults.

$installSettings = [];

// ----------------------------------------------------------------------------
// Initial Administrator Account Settings
// ----------------------------------------------------------------------------
// These settings control the first admin user account that gets created
// during installation. This is the account you'll use to log into OpenEMR
// for the first time.

$installSettings['iuser']                    = 'admin';         // Admin username
$installSettings['iuname']                   = 'Administrator'; // Admin full name
$installSettings['iuserpass']                = 'pass';          // Admin password (CHANGE IN PRODUCTION!)
$installSettings['igroup']                   = 'Default';       // User group for admin

// ----------------------------------------------------------------------------
// Database Connection Settings
// ----------------------------------------------------------------------------
// These settings tell OpenEMR how to connect to the MySQL/MariaDB database.
// The "server" is the database hostname (e.g., "mysql", "mysql-lb").
// The "loginhost" is the hostname of the web server (used for some internal checks).

$installSettings['server']                   = 'localhost';    // MySQL server hostname
$installSettings['loginhost']                = 'localhost';    // Web server hostname
$installSettings['port']                     = '3306';         // MySQL port (default: 3306)

// ----------------------------------------------------------------------------
// Database Root Credentials
// ----------------------------------------------------------------------------
// These are used to create the OpenEMR database and user account.
// In production, use strong passwords and consider using a dedicated
// database setup script instead of root credentials.

$installSettings['root']                     = 'root';         // MySQL root username
$installSettings['rootpass']                 = 'BLANK';        // MySQL root password (BLANK = empty)

// ----------------------------------------------------------------------------
// OpenEMR Database User Credentials
// ----------------------------------------------------------------------------
// These are the credentials for the dedicated database user that OpenEMR
// will use to connect to the database. This user is created during installation
// and has permissions only for the OpenEMR database.

$installSettings['login']                    = 'openemr';      // OpenEMR database username
$installSettings['pass']                     = 'openemr';      // OpenEMR database password
$installSettings['dbname']                   = 'openemr';      // Database name

// ----------------------------------------------------------------------------
// Database Character Encoding
// ----------------------------------------------------------------------------
// This controls how text is stored in the database. utf8mb4_general_ci is
// the recommended setting as it supports all Unicode characters including
// emojis and special characters from various languages.

$installSettings['collate']                  = 'utf8mb4_general_ci'; // Character encoding

// ----------------------------------------------------------------------------
// Site Configuration
// ----------------------------------------------------------------------------
// OpenEMR supports multi-site installations (multiple instances sharing one
// codebase). For single-site installations, use "default".

$installSettings['site']                     = 'default';      // Site identifier

// ----------------------------------------------------------------------------
// Advanced Options (typically not used in standard installations)
// ----------------------------------------------------------------------------
// These options are for advanced use cases like cloning existing installations
// or setting up multi-site configurations. Most installations leave these as "BLANK".

$installSettings['source_site_id']           = 'BLANK';        // Source site for cloning
$installSettings['clone_database']           = 'BLANK';        // Clone existing database
$installSettings['no_root_db_access']        = 'BLANK';        // Don't use root for DB setup
$installSettings['development_translations'] = 'BLANK';        // Enable dev translations

// ============================================================================
// COMMAND-LINE ARGUMENT PARSING
// ============================================================================
// This section reads command-line arguments (passed from openemr.sh) and
// overrides the default settings above. Arguments can be provided in two formats:
//
// Format 1: Individual key=value pairs
//   php auto_configure.php server=mysql rootpass=secret
//
// Format 2: Single -f flag with space-separated key=value pairs
//   php auto_configure.php -f "server=mysql rootpass=secret login=openemr"
//
// The -f format is useful when passing all settings as a single string from
// the shell script (which is how openemr.sh calls this script).

for ($i=1; $i < count($argv); $i++) {
    if ($argv[$i] == '-f' && isset($argv[$i+1])) {
        // Handle case where a single string contains all parameters
        // This format is used by openemr.sh: php auto_configure.php -f "server=mysql rootpass=root"
        $configString = $argv[$i+1];
        $configPairs = preg_split('/\s+/', trim($configString));
        foreach ($configPairs as $pair) {
            if (str_contains($pair, '=')) {
                [$index, $value] = explode('=', $pair, 2);
                $installSettings[$index] = $value;
            }
        }
        // Skip the next argument since we already processed it
        $i++;
    } else {
        // Handle standard key=value parameters
        // Format: php auto_configure.php server=mysql rootpass=secret
        $indexandvalue = explode("=", $argv[$i]);
        $index = $indexandvalue[0];              // The setting name (e.g., "server")
        $value = $indexandvalue[1] ?? '';        // The setting value (e.g., "mysql")

        // Override the default setting with the command-line value
        $installSettings[$index] = $value;
    }
}

// ============================================================================
// CONVERT "BLANK" VALUES TO EMPTY STRINGS
// ============================================================================
// Some settings use "BLANK" as a placeholder to mean "empty" or "not set".
// This section converts all "BLANK" values to actual empty strings, which
// is what the installer expects.

$tempInstallSettings = [];
foreach ($installSettings as $setting => $value) {
    // If the value is "BLANK", convert it to an empty string
    if ($value == "BLANK") {
        $value = '';
    }
    $tempInstallSettings[$setting] = $value;
}
$installSettings = $tempInstallSettings;

// ============================================================================
// RUN THE OPENEMR INSTALLER
// ============================================================================
// This is where the actual installation happens. The Installer class:
//   1. Connects to the database
//   2. Creates the database and user (if they don't exist)
//   3. Creates all necessary database tables
//   4. Sets up the initial configuration
//   5. Creates the admin user account
//
// The quick_install() method does all of this automatically.

// Install and configure OpenEMR using the Installer class
$installer = new Installer($installSettings, new SystemLogger());

// Run the installer and check if it succeeded
if (! $installer->quick_install()) {
    // Installation failed - throw an exception with the error message
    // This will cause the container startup to fail, which is the correct
    // behavior since OpenEMR cannot run without proper installation
    throw new Exception("ERROR: " . $installer->error_message . "\n");
} else {
    // Installation succeeded - print the debug message
    // This typically includes information about what was created and configured
    echo $installer->debug_message . "\n";
}
