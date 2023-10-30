<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  omegasystemsgroup.com
 *  @copyright Copyright (c) 2023 omegasystemsgroup.com <info@omegasystemsgroup.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../../globals.php");

use OpenEMR\Modules\WenoModule\Services\LogProperties;
use OpenEMR\Common\Acl\AclMain;

/*
 * access control is on Weno side based on the user login
 */
if (!AclMain::aclCheckCore('patient', 'med')) {
    echo xlt('Prescriptions Review Not Authorized');
    exit;
}

$logproperties = new LogProperties();
$result = $logproperties->logSync();


