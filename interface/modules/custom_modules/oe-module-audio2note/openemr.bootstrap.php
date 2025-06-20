<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Bootstrap file for the OpenEMR Audio to Note Integration module.
 *
 * Currently minimal for MVP. Can be expanded later for autoloading,
 * service registration, or other initialization tasks if needed.
 */

// Minimal bootstrap content.
// OpenEMR's module loader expects this file to exist.

// Attempt to explicitly include OpenEMR's main autoloader first.
// The path assumes this bootstrap file is in interface/modules/custom_modules/your_module_name/
$openemrVendorAutoload = __DIR__ . '/../../../../vendor/autoload.php';
if (file_exists($openemrVendorAutoload)) {
    require_once $openemrVendorAutoload;
} else {
    // Fallback or log error if OpenEMR's main autoloader isn't found at the expected path.
    // This might happen if the module is placed in a non-standard directory structure.
    error_log("OpenemrAudio2Note Bootstrap: Could not find OpenEMR's main vendor/autoload.php at: " . $openemrVendorAutoload);
}

// Then include the module's own autoloader if it exists.
$moduleVendorAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($moduleVendorAutoload)) {
    require_once $moduleVendorAutoload;
} else {
    // This is problematic if your module has its own Composer dependencies (like Guzzle).
    error_log("OpenemrAudio2Note Bootstrap: Module's own vendor/autoload.php not found at: " . $moduleVendorAutoload . ". This is expected if the module has no specific composer dependencies or if composer install was not run in the module directory.");
}

// Additional early initialization logic for the module can be placed here if necessary.

?>
