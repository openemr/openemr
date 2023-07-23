<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\EligibilityData;
use OpenEMR\Modules\ClaimRevConnector\EligibilityInquiryRequest;
use OpenEMR\Modules\ClaimRevConnector\InformationReceiver;
use OpenEMR\Modules\ClaimRevConnector\SubscriberPatientEligibilityRequest;
use OpenEMR\Modules\ClaimRevConnector\RevenueToolsRequest;
use OpenEMR\Modules\ClaimRevConnector\RevenueToolsPayer;

class EligibilityObjectCreator
{
    public static function buildRevenueToolsRequest($pid, $pr, $eventDate = null, $providerId = null, $facilityId = null)
    {
        $facilityName = "";
        $facilityState = "";
        $facilityNpi = "";
        $providerNpi = "";
        $providerPinCode = "";

        $useFacility = $GLOBALS['oe_claimrev_config_use_facility_for_eligibility'];
        $serviceTypeCodes = $GLOBALS['oe_claimrev_config_service_type_codes'];
        $accountNumber = "";
        $productsToRun = array(1);


        $revenueTools = new RevenueToolsRequest();
        $revenueTools->requestingSoftware = "openEmr ClaimRev Connect";
        $revenueTools->accountNumber = $accountNumber;
        $revenueTools->payerResponsibility = $pr;
        $revenueTools->includeCredit = false;
        $revenueTools->serviceTypeCodes = explode(",", $serviceTypeCodes);
        $revenueTools->productsToRun = $productsToRun;


        if ($eventDate == null) {
            $revenueTools->serviceBeginDate = date("Y-m-d");
            $revenueTools->serviceEndDate = date("Y-m-d");
        } else {
            $revenueTools->serviceBeginDate = $eventDate;
            $revenueTools->serviceEndDate = $eventDate;
        }

        //only 1 will come back here
        $patientData = EligibilityData::getPatientData($pid);

        if ($patientData != null) {
            if ($facilityId == null) {
                $facilityId = $patientData['facility_id'];
            }
            if ($providerId == null || $providerId < 1) {
                $providerId = $patientData['providerID'];
            }

            $facilityData = EligibilityData::getFacilityData($facilityId);
            $providerData = EligibilityData::getProviderData($providerId);

            if ($facilityData != null) {
                $facilityName = $facilityData['facility_name'];
                $facilityState = $facilityData['facility_state'];
                $facilityNpi = $facilityData['facility_npi'];
            }

            if ($providerData != null) {
                $providerPinCode = $providerData['provider_pin'];
                $providerNpi = $providerData['provider_npi'];
            }

            $revenueTools->practiceName = $facilityName;
            $revenueTools->practiceState = $facilityState;
            $revenueTools->npi = $facilityNpi;

            if ($useFacility == false) {
                $revenueTools->npi = $providerNpi;
            }

            $revenueTools->patientFirstName = $patientData['fname'];
            $revenueTools->patientLastName = $patientData['lname'];
            $revenueTools->patientGender = $patientData['sex'];
            $revenueTools->patientDob = $patientData['dob'];
            $revenueTools->patientSsn = $patientData['ss'];
            $revenueTools->patientAddress1 = $patientData['street'];
            $revenueTools->patientCity = $patientData['city'];
            $revenueTools->patientState = $patientData['state'];
            $revenueTools->patientZip = $patientData['postal_code'];
            $revenueTools->patientEmailAddress = $patientData['email'];

            $revenueTools->pinCode = $providerPinCode;
        }
        return $revenueTools;
    }
    public static function buildObject($pid, $payer_responsibility, $eventDate = null, $facilityId = null, $providerId = null)
    {
        $results = array();
        $resultSubscribers = EligibilityData::getSubscriberData($pid, $payer_responsibility);
        foreach ($resultSubscribers as $subscriberRow) {
            $payers = array();
            $pr = ValueMapping::mapPayerResponsibility($subscriberRow['type']);
            $revenueTools = EligibilityObjectCreator::buildRevenueToolsRequest($pid, $pr, $eventDate, $providerId, $facilityId);
            $payer = new RevenueToolsPayer();
            $payer->payerNumber = $subscriberRow['payerId'];
            $payer->payerName = $subscriberRow['payer_name'];
            $payer->subscriberNumber = $subscriberRow['policy_number'];
            $revenueTools->subscriberFirstName = $subscriberRow['subscriber_fname'];
            $revenueTools->subscriberLastName = $subscriberRow['subscriber_lname'];
            if ($subscriberRow['subscriber_dob'] != "0000-00-00") {
                $revenueTools->subscriberDob = $subscriberRow['subscriber_dob'];
            }

            array_push($payers, $payer);
            $revenueTools->payers = $payers;
        }
        array_push($results, $revenueTools);

        return $results;
    }

    public static function saveSingleToDatabase($req, $pid)
    {

        $stale_age = $GLOBALS['oe_claimrev_eligibility_results_age'];
        //status of re-check if results are still waiting on claimrev site

        //if it's greater than aged date then lets remove completely from the tables, the new one will handle it. We don't care about statuses
        $sql = "DELETE FROM mod_claimrev_eligibility WHERE pid = ? AND payer_responsibility = ? AND (datediff(now(),create_date) >= ? or status in('error','waiting','creating') ) ";
        $sqlarr = array($pid,$req->payerResponsibility, $stale_age);
        $result = sqlStatement($sql, $sqlarr);

        $sql = "SELECT * FROM mod_claimrev_eligibility WHERE pid = ? AND payer_responsibility = ?";
        $sqlarr = array($pid,$req->payerResponsibility);
        $result = sqlStatement($sql, $sqlarr);
        if (sqlNumRows($result) <= 0) {
            $status = "creating";
            $sql = "INSERT INTO mod_claimrev_eligibility (pid,payer_responsibility,status,create_date) VALUES(?,?,?,NOW())";

            $sqlarr = array($pid,$req->payerResponsibility,$status);
            $result = sqlInsert($sql, $sqlarr);
            $status = "waiting";

            $req->originatingSystemId = strval($result);
            $json = json_encode($req, true);
            $sql = "UPDATE mod_claimrev_eligibility SET request_json = ?, status = ? where id = ?";
            $sqlarr = array($json,$status,$result);
            sqlStatement($sql, $sqlarr);
        }
    }
    public static function saveToDatabase($requests, $pid)
    {
        //oe_claimrev_eligibility_results_age
        //lets check for status for waiting or error and replace the json and reset-status, what to do if inprogress??

        foreach ($requests as $req) {
            EligibilityObjectCreator::saveSingleToDatabase($req, $pid);
        }
    }
}
