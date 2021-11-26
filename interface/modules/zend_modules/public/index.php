<?php

/**
 * openemr/interface/modules/zend_modules/public/index.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T.Paul <jacob@zhservices.com>
 * @author    Shalini Balakrishnan <shalini@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
use Laminas\Console\Request as ConsoleRequest;

//fetching controller name and action name from the SOAP request
$urlArray = explode('/', ($_SERVER['REQUEST_URI'] ?? ''));
$countUrlArray = count($urlArray);
preg_match('/\/(\w*)\?/', ($_SERVER['REQUEST_URI'] ?? ''), $matches);
$actionName = $matches[1] ?? '';
$controllerName = $urlArray[$countUrlArray - 2] ?? '';

//skipping OpenEMR authentication if the controller is SOAP and action is INDEX
//SOAP authentication is done in the controller EncounterccdadispatchController
if (!empty($_REQUEST['recipient']) && ($_REQUEST['recipient'] === 'patient') && $_REQUEST['site'] && $controllerName) {
    $ignoreAuth_onsite_portal = false;
    if (!empty($_REQUEST['me'])) {
        session_id($_REQUEST['me']);
        session_start();
    }
    if ($_SESSION['pid'] && $_SESSION['sessionUser'] === '-patient-' && $_SESSION['portal_init']) {
        // Onsite portal was validated and patient authorized and re-validated via forwarded session.
        $ignoreAuth_onsite_portal = true;
    }
}

if (!empty($_REQUEST['me']) && $_REQUEST['sent_by_app'] === 'core_api') {
    // pick up already running session from api's
    session_id($_REQUEST['me']);
    session_start();
}

if (php_sapi_name() === 'cli' && count($argv) != 0) {
    $ignoreAuth = true;
    $siteDefault = 'default';
    foreach ($argv as $arg) {
        if (str_contains($arg, "--site=")) {
            $siteDefault = explode("=", $arg)[1];
        }
    }
    $_GET['site'] = $siteDefault;
    // Since from command line, set $sessionAllowWrite since need to set site_id session and no benefit to set to false
    $sessionAllowWrite = true;
}

require_once(__DIR__ . "/../../../globals.php");
require_once(__DIR__ . "/../../../../library/forms.inc");
require_once(__DIR__ . "/../../../../library/options.inc.php");

chdir(dirname(__DIR__));

// Run the application!
/** @var OpenEMR/Core/ModulesApplication
 * Defined in globals.php
*/
if (!empty($GLOBALS['modules_application'])) {
    // $time_start = microtime(true);
    // run the request lifecycle.  The application has already inited in the globals.php
    $GLOBALS['modules_application']->run();
    // $time_end = microtime(true);
    // echo "App runtime: " . ($time_end - $time_start) . "<br />";
} else {
    die("global modules_application is not defined.  Cannot run zend module request");
}
