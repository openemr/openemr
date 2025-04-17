<?php

/**
 * API index page for receiving requests from the OpenEMR clinician requests.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// since we are working inside the portal we have to use the portal session verification logic here...
require_once "../../../../globals.php";

use Comlink\OpenEMR\Modules\TeleHealthModule\Bootstrap;

$kernel = $GLOBALS['kernel'];
$bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel);
$roomController = $bootstrap->getTeleconferenceRoomController(false);

$action = $_REQUEST['action'] ?? '';
$queryVars = $_REQUEST ?? [];
$queryVars['pid'] = $_SESSION['pid'] ?? null;
$queryVars['authUser'] = $_SESSION['authUser'] ?? null;
if (!empty($_SERVER['HTTP_APICSRFTOKEN'])) {
    $queryVars['csrf_token'] = $_SERVER['HTTP_APICSRFTOKEN'];
}
$roomController->dispatch($action, $queryVars);
exit;
