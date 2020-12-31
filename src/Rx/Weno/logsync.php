<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../interface/globals.php");
require_once ($GLOBALS["srcdir"] . "/formatting.inc.php");

use OpenEMR\Rx\Weno\LogProperties;
use OpenEMR\Rx\Weno\TransmitProperties;
use OpenEMR\Common\Logging\EventAuditLogger;

//TODO convert this entire file to a class
$wenoProperties = new TransmitProperties();
$provider_info = $wenoProperties->getProviderEmail();
$logsync = new LogProperties();

/**
 * checks to see if the file exist and if it does was it put there today?
 * The idea behind this is to automate the log information download and import into the database.
 * This should only execute once a day no matter how many times it is called.
 * The idea was to include in the index file to be executed when the prescription called.
 */
$today = date ("F d Y");
$filedate = $logsync->doesLogFileExist();
//die if the dates match or the file does not exist
if ($today === $filedate) {
    die;
}

$logurlparam = $logsync->logEcps();
$syncLogs = "https://test.wenoexchange.com/en/EPCS/DownloadNewRxSyncDataVal?useremail=";
if ($logurlparam == 'error') {
    echo xlt("Cipher failure check encryption key");
    exit;
}

$urlOut = $syncLogs.$provider_info['email']."&data=".urlencode($logurlparam);

$ch = curl_init($urlOut);
curl_setopt($ch, CURLOPT_TIMEOUT, 200);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$rpt = curl_exec($ch);
if(curl_errno($ch)){
    throw new Exception(curl_error($ch));
}
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if($statusCode == 200){
    file_put_contents($logsync->rxsynclog, $rpt);
    $logstring = "prescrition log import initiated successfully";
    EventAuditLogger::instance()->newEvent("prescritions_log", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$logstring");
} else {
    EventAuditLogger::instance()->newEvent("prescritions_log", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$statusCode");
}

$l = 0;
if(file_exists($logsync->rxsynclog)) {
    $records = fopen($logsync->rxsynclog, "r");

    while (! feof($records)) {
        $line = fgetcsv($records);

        if ($l <= 2) {
            $l++; continue;
        }
        if (!isset($line[1])) {
            continue;
        }
        if (isset($line[4])) {
            $logsync->messageid = isset($line[4]) ? $line[4] : null;
            $is_saved = $logsync->checkMessageId();
            if ($is_saved > 0) {
                continue;
            }
        }
            $pr = isset($line[2]) ? $line[2] : null;
            $provider = explode(":", $pr);
            $windate = isset($line[16]) ? $line[16] : null;
            $idate = substr(trim($windate), 0, -5);
            $idate = explode(" ", $idate);
            $idate = explode("/", $idate[0]);
            $year = isset($idate[2]) ? $idate[2] : null;
            $month = isset($idate[0]) ? $idate[0] : null;
            $day = isset($idate[1]) ? $idate[1] : null;
            $idate = $year . '-' . $month . '-' . $day;
            $ida = filter_var($idate, FILTER_SANITIZE_NUMBER_INT);
            $p = isset($line[1]) ? $line[1] : null;
            $pid = filter_var($p, FILTER_SANITIZE_NUMBER_INT);
            $r = isset($line[22]) ? $line[22] : null;
            $refills = filter_var($r, FILTER_SANITIZE_NUMBER_INT);

            $logsync->id = '';
            $logsync->active = 1;
            $logsync->date_added = $ida;
            $logsync->patient_id = $pid;
            $logsync->drug = isset($line[11]) ? str_replace('"', '', $line[11]) : null;
            $logsync->form = isset($line[19]) ? $line[19] : null;
            $logsync->quantity = isset($line[18]) ? $line[18] : null;
            $logsync->refills = $refills;
            $logsync->substitute = isset($line[14]) ? $line[14] : null;
            $logsync->note = isset($line[21]) ? $line[21] : null;
            $logsync->rxnorm_drugcode = isset($line[12]) ? $line[12] : null;
            $logsync->provider_id = $provider[0];
            $logsync->prescriptionguid = isset($line[4]) ? $line[4] : null;
            $logsync->insertPrescriptions();

            ++$l;
    }
    fclose($records);
}
