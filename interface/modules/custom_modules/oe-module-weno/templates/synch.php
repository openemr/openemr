<?php

/**
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  omegasystemsgroup.com
 *  @author     Jerry Padgett <sjpadgett@gmail.com>
 *  @copyright  Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 *  @copyright  Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__DIR__, 4) . "/globals.php");

use OpenEMR\Modules\WenoModule\Services\LogProperties;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\WenoModule\Services\TransmitProperties;

/*
 * access control is on Weno side based on the user login
 */
if (!AclMain::aclCheckCore('patients', 'rx')) {
    echo TransmitProperties::styleErrors(xlt('Prescriptions Review Not Authorized'));
    exit;
}

$logProperties = new LogProperties();
try {
    $task = $_REQUEST['key'] ?? $_POST['key'] ??  '';
    $result = $logProperties->logSync($task);
} catch (Exception $e) {
    $result = false;
    error_log('Error syncing log: ' . errorLogEscape($e->getMessage()));
}

if ($result) {
    http_response_code(200);
} else {
    http_response_code(500);
}
