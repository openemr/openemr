<?php

require_once("../../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\FacilityService;
use OpenEMR\Billing\BillingUtilities;

if (!CsrfUtils::verifyCsrfToken($_REQUEST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$pid = isset($_GET['pid']) ? $_GET['pid'] : "";
$encounter = isset($_GET['encounter']) ? $_GET['encounter'] : "";
$codevalue = isset($_GET['code']) ? $_GET['code'] : array();

if(empty($pid) || empty($encounter) || empty($codevalue)) {
    echo json_encode(array());
    exit();
}

$billresult = BillingUtilities::getBillingByEncounter($pid, $encounter, "*");
$billItems = array();

foreach ($codevalue as $codeItem) {
    list($code, $modifier) = explode(":", $codeItem);
    $isexist = false;
    foreach ($billresult as $billItem) {
        if($billItem['code_type'] == $code && $billItem['code'] == $modifier) {
            $isexist = true;

            if (in_array($code, array("CPT4", "HCPCS"))) {
                $billItems[$billItem['code_type'].':'.$billItem['code']] = array('is_exist' => 1, 'confirm' => 1);
                break;
            } else {
                //$billItems[$billItem['code_type'].':'.$billItem['code']] = array('is_exist' => 1);
                //break;
            }
        }
    }

    if($isexist === false) {
        $billItems[$code.':'.$modifier] = array('is_exist' => 0);
    }
}

echo json_encode($billItems);
exit();