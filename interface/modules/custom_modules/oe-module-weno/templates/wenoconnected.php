<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Rx\Weno\WenoPharmaciesJson;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Rx\Weno\TransmitProperties;

//ensure user has proper access
if (!AclMain::aclCheckCore('patient', 'med')) {
    echo xlt('Pharmacy Import not authorized');
    exit;
}
function start_weno_pharmacy_sync()
{
    $cryptoGen = new CryptoGen();
    $transmitProperties = new TransmitProperties();
    $localPharmacyJson = new WenoPharmaciesJson(
        $cryptoGen,
        $transmitProperties
    );
    error_log('Background Services launched for the Weno pharmacies');

//check if the background service is active and set intervals to once a day
//Weno has decided to not force the import of pharmacies since they are using the iframe
//and the pharmacy can be selected at the time of creating the prescription.
    $value = $localPharmacyJson->checkBackgroundService();
    if ($value == 'active' || $value == 'live') {
        $status = $localPharmacyJson->storePharmacyDataJson();
        error_log('Weno pharmacies download complete');
        die;
    }
}
