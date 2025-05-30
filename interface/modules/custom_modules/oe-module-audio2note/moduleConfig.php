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
 * OpenEMR Audio to Note Integration module configuration file.
 */

// Ensure globals.php is loaded for OpenEMR environment
require_once dirname(__FILE__, 4) . '/globals.php';

use OpenEMR\Core\ModulesClassLoader;

// Register namespace for this module.
// This is crucial for OpenEMR to find classes within your module's src/ directory.
$classLoader = new ModulesClassLoader($GLOBALS['fileroot']);
$classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\OpenemrAudio2Note\\", __DIR__ . DIRECTORY_SEPARATOR . 'src');

// --- Robust Site ID Initialization ---
// This logic is borrowed from interface/globals.php to ensure site_id is set in session
// before the template, which might rely on $GLOBALS['site_id'], is loaded.
if (empty($_SESSION['site_id']) || !empty($_GET['site'])) {
    $site_id_to_set = 'default'; // Default site
    if (!empty($_GET['site'])) {
        $site_id_to_set = $_GET['site'];
    } elseif (!empty($_SERVER['HTTP_HOST'])) {
        // Attempt to derive from HTTP_HOST if not 'default' and directory exists
        $derived_site_id = $_SERVER['HTTP_HOST'];
        if (is_dir($GLOBALS['OE_SITES_BASE'] . "/" . $derived_site_id)) {
            $site_id_to_set = $derived_site_id;
        }
    }

    // Basic validation for site_id
    if (empty($site_id_to_set) || preg_match('/[^A-Za-z0-9\\-.]/', $site_id_to_set)) {
        // Log invalid site_id issues if they occur.
        // error_log("moduleConfig.php: Invalid site_id detected or derived: " . htmlspecialchars($site_id_to_set ?? '', ENT_QUOTES));
    } else {
        if (!isset($_SESSION['site_id']) || $_SESSION['site_id'] !== $site_id_to_set) {
            $_SESSION['site_id'] = $site_id_to_set;
        }
    }
}

// After attempting to set $_SESSION['site_id'], ensure $GLOBALS['site_id'] reflects this.
if (isset($_SESSION['site_id'])) {
    if (!isset($GLOBALS['site_id']) || $GLOBALS['site_id'] !== $_SESSION['site_id']) {
        $GLOBALS['site_id'] = $_SESSION['site_id'];
    }
    // Ensure site-specific paths are correct based on the now definitive $GLOBALS['site_id']
    if (isset($GLOBALS['OE_SITES_BASE']) && isset($GLOBALS['webroot']) && isset($GLOBALS['site_id'])) {
        $GLOBALS['OE_SITE_DIR'] = $GLOBALS['OE_SITES_BASE'] . "/" . $GLOBALS['site_id'];
        $GLOBALS['OE_SITE_WEBROOT'] = $GLOBALS['webroot'] . "/sites/" . $GLOBALS['site_id'];
    }
} else {
    // Fallback if session site_id is still not set.
    // error_log("moduleConfig.php: Warning - \$_SESSION['site_id'] is still empty after explicit set attempt.");
    if (!isset($GLOBALS['site_id'])) {
        $GLOBALS['site_id'] = 'default'; // Last resort
        // error_log("moduleConfig.php: Setting \$GLOBALS['site_id'] to 'default' as a last resort.");
        if (isset($GLOBALS['OE_SITES_BASE']) && isset($GLOBALS['webroot'])) {
            $GLOBALS['OE_SITE_DIR'] = $GLOBALS['OE_SITES_BASE'] . "/default";
            $GLOBALS['OE_SITE_WEBROOT'] = $GLOBALS['webroot'] . "/sites/default";
        }
    }
}
// --- End Robust Site ID Initialization ---

// Indicate that this module uses a custom template for configuration.
$module_config = 1;

// Directly include the template file that will render the configuration form.
require_once __DIR__ . '/templates/audio_to_note_setup.php';

exit;
