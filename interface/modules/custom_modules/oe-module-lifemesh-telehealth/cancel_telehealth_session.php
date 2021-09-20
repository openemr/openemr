<?php
/*
 *
 * @package      OpenEMR
 * @link               https://www.open-emr.org
 *
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */


require_once dirname(__DIR__, 3) . "/globals.php";;
require_once "controller/Container.php";

/** @var TYPE_NAME $eventid */
$eventid = $_GET['eid'];

$action = new OpenEMR\Modules\LifeMesh\Container();

$credentials = $action->getDatabase();

$accountinfo = $credentials->getCredentials();

$encryptedaccountinfo = base64_encode($accountinfo[1] . ":" . $accountinfo[0]);

$cancel = $action->getAppDispatch();

echo $cancel->cancelSession($encryptedaccountinfo, $eventid, $GLOBALS['unique_installation_id'],'cancelSession');


