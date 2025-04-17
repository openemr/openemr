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

// since we are working inside the portal we have to use the portal session verification logic here...
require_once "../../../../../portal/verify_session.php";

use Comlink\OpenEMR\Modules\TeleHealthModule\Bootstrap;



$kernel = $GLOBALS['kernel'];
$bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel);
$roomController = $bootstrap->getTeleconferenceRoomController(true);

$action = $_GET['action'] ?? '';
$queryVars = $_GET ?? [];
$queryVars['pid'] = $_SESSION['pid']; // we overwrite any pid value to make sure we only grab this patient.
$roomController->dispatch($action, $queryVars);
exit;
