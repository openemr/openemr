<?php

/**
 * Handles API requests for patient portal.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//landing page definition -- where to go if something goes wrong
// this should trim the following path /interface/modules/custom_modules/oe-module-comlink-telehealth/public/
// this should get us to the main openemr directory and include the webroot path if we have it
// we have to do this as we don't have access to the globals.php file yet.
$originalPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = dirname(dirname(dirname(dirname(dirname(dirname($originalPath))))));
$query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
$redirect = $originalPath . "?";
if (!empty($query)) {
    $redirect .= $query;
}
// need to retain the webroot if we have one
$landingpage = $basePath . "portal/index.php?site=" . urlencode($_GET['site_id'] ?? '') . "&redirect=" . urlencode($redirect);
$skipLandingPageError = true;

// since we are working inside the portal we have to use the portal session verification logic here...
require_once "../../../../../portal/verify_session.php";

use Comlink\OpenEMR\Modules\TeleHealthModule\Bootstrap;

$kernel = $GLOBALS['kernel'];
$bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel);
$roomController = $bootstrap->getTeleconferenceRoomController(true);
if (!empty($_SERVER['HTTP_APICSRFTOKEN'])) {
    $queryVars['csrf_token'] = $_SERVER['HTTP_APICSRFTOKEN'];
}
$action = $_GET['action'] ?? '';
$queryVars = $_GET ?? [];
$queryVars['pid'] = $_SESSION['pid']; // we overwrite any pid value to make sure we only grab this patient.
$roomController->dispatch($action, $queryVars);
exit;
