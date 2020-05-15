<?php

/**
 * While creating new encounter this code is used to change the "Billing Facility:".
 * This happens on change of the "Facility:" field.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Jacob T.Paul <jacob@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\FacilityService;

if (!CsrfUtils::verifyCsrfToken($_REQUEST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}
if ($_GET['mode'] === 'get_pos') {
    // put here for encounter facility changes sjp
    $fid = $_GET['facility_id'] ? (int)$_GET['facility_id'] : exit('0');
    $pos = sqlQuery("SELECT pos_code FROM facility WHERE id = ?", array($fid));

    echo json_encode(((int)$pos['pos_code'] < 10) ? ("0" . $pos['pos_code']) : $pos['pos_code']);
    exit();
}
if ($_GET['mode'] === 'get_user_data') {
    // put here for encounter user changes sjp
    $provider_id = $_GET['provider_id'] ? (int)$_GET['provider_id'] : exit('0');
    $facilityService = new FacilityService();
    $fac = $facilityService->getFacilityForUser($provider_id);
    $fid = $fac['id'];
    $pos = ((int)$fac['pos_code'] < 10) ? ("0" . $fac['pos_code']) : $fac['pos_code'];
    $isBilling = $fac['billing_location'];

    echo json_encode(array($fid, $pos, $isBilling));
    exit();
}
$pid = $_POST['pid'];
$facility = $_POST['facility'];
$date = $_POST['date'];
$q = sqlStatement("SELECT pc_billing_location FROM openemr_postcalendar_events WHERE pc_pid=? AND pc_eventDate=? AND pc_facility=?", array($pid, $date, $facility));
$row = sqlFetchArray($q);
billing_facility('billing_facility', $row['pc_billing_location']);
