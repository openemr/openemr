<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @author    Kofi Appiah <kkappiah@medsov.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Rx\Weno\Container;
use OpenEMR\Services\PharmacyService;


//ensure user has proper access
if (!AclMain::aclCheckCore('patients', 'rx')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Weno eRx")]);
    exit;
}

$pharmacyService = new PharmacyService();
$prim_pharmacy = $pharmacyService->getWenoPrimaryPharm($_SESSION['pid']) ?? [];
$alt_pharmacy = $pharmacyService->getWenoAlternatePharm($_SESSION['pid']) ?? [];


$container = new Container();

$wenoProperties = $container->getTransmitproperties();
$provider_info = $wenoProperties->getProviderEmail();
$urlParam = $wenoProperties->cipherpayload();          //lets encrypt the data
//$logsync = $container->getLogproperties();
//$logsync->logSync();

$vitals = $wenoProperties->getVitals();
$provider_name = $wenoProperties->getProviderName();
$patient_name = $wenoProperties->getPatientName();
$facility_name = $wenoProperties->getFacilityInfo();

$newRxUrl = "https://online.wenoexchange.com/en/NewRx/ComposeRx?useremail=";
if ($urlParam == 'error') {   //check to make sure there were no errors
    echo xlt("Cipher failure check encryption key");
    exit;
}
?>

<style>
    .row {
        display: flex;
        flex-direction: row;
    }
    .col {
        display: flex;
        flex-direction: column;
    }
    .mr-5 {
        margin-right: 50px;
    }
    .center {
        justify-content: center;
    }
</style>

<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8">
 <title><?php echo xlt('Weno eRx') ?></title>
</head>
<body >
     
<?php
    //**warning** do not add urlencode to  $provider_info['email']
    $urlOut = $newRxUrl . $provider_info['email'] . "&data=" . urlencode($urlParam);

?>

<div style="margin:30px">
    <div>
        <h2><?php echo xlt("ePrescribe"); ?></h2>
    </div>
    <div class="container">
        <div class="row center">
            <div class="col mr-5">
                <div class="row">
                    <div><b>Presriber</b>: <?php echo text($provider_name); ?> </div>
                </div>
                <div class="row">
                    <div><b>Patient Name</b>: <?php echo text($patient_name) ?></div>
                </div>
                <div class="row">
                    <div>
                        <b>Current Facility/Location</b>: <?php echo text($facility_name["name"])?>
                    </div>
                </div>
            </div>
            <div class="col mr-5" style="margin-top: -14px !important">
                <table>
                    <tr>
                        <th>Vitals</th>
                        <th>Date Observed</th>
                    </tr>
                    <tr>
                        <td>Height:<?php echo substr($vitals['height'], 0, -4) ?> </td>
                        <td><?php echo $vitals['date']?></td>
                    </tr>
                    <tr>
                        <td>Weight:<?php echo substr($vitals['weight'], 0, -4)?> </td>
                        <td><?php echo $vitals['date']?></td>
                    </tr>
                </table>
            </div>
            <div class="col">
                <div>Primary Pharmacy: <?php echo xlt($prim_pharmacy['business_name'] . " / " . $primary_pharmacy['address_line_1'] . " / " . $primary_pharmacy['city']); ?></div>
                <div>Weno Alt: <?php echo xlt($alt_pharmacy['business_name'] . " / " . $pharmacy['address_line_1'] . " / " . $pharmacy['city']); ?></div>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <iframe id="wenoIfram"
            title="Weno IFRAME"
            width="100%"
            height="900"
            src="<?php echo $urlOut; ?>">
        </iframe>
    </div>
</div>
</body>
</html>

