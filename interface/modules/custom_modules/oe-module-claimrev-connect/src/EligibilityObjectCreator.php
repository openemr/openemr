<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\BC\Utilities;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ClaimRevConnector\EligibilityData;
use OpenEMR\Modules\ClaimRevConnector\RevenueToolsPayer;
use OpenEMR\Modules\ClaimRevConnector\RevenueToolsRequest;

class EligibilityObjectCreator
{
    /**
     * @param int|string                $pid
     * @param string                    $pr             Mapped payer responsibility code
     * @param string|null               $eventDate
     * @param int|string|null           $providerId
     * @param int|string|null           $facilityId
     * @param list<int>|null            $productsToRun
     */
    public static function buildRevenueToolsRequest(
        int|string $pid,
        string $pr,
        ?string $eventDate = null,
        int|string|null $providerId = null,
        int|string|null $facilityId = null,
        ?array $productsToRun = null,
    ): RevenueToolsRequest {
        $facilityName = "";
        $facilityState = "";
        $facilityNpi = "";
        $providerNpi = "";
        $providerPinCode = "";

        $useFacility = OEGlobalsBag::getInstance()->getBoolean('oe_claimrev_config_use_facility_for_eligibility');
        $serviceTypeCodesRaw = OEGlobalsBag::getInstance()->getString('oe_claimrev_config_service_type_codes');
        $serviceTypeCodes = array_values(array_filter(
            array_map(trim(...), explode(',', $serviceTypeCodesRaw)),
            static fn(string $code): bool => $code !== '',
        ));
        $accountNumber = "";
        if ($productsToRun === null || $productsToRun === []) {
            $productsToRun = [1];
        }


        $revenueTools = new RevenueToolsRequest();
        $revenueTools->requestingSoftware = "openEmr ClaimRev Connect";
        $revenueTools->accountNumber = $accountNumber;
        $revenueTools->payerResponsibility = $pr;
        $revenueTools->includeCredit = false;
        $revenueTools->serviceTypeCodes = $serviceTypeCodes === [] ? null : $serviceTypeCodes;
        $revenueTools->productsToRun = $productsToRun;


        if ($eventDate == null) {
            $revenueTools->serviceBeginDate = date("Y-m-d");
            $revenueTools->serviceEndDate = date("Y-m-d");
        } else {
            $revenueTools->serviceBeginDate = $eventDate;
            $revenueTools->serviceEndDate = $eventDate;
        }

        //only 1 will come back here
        $patientData = EligibilityData::getPatientData((int) $pid);

        if ($patientData != null) {
            if ($facilityId == null) {
                $facilityId = TypeCoerce::asInt($patientData['facility_id'] ?? 0);
            }
            if ($providerId == null || (int) $providerId < 1) {
                $providerId = TypeCoerce::asInt($patientData['providerID'] ?? 0);
            }

            $facilityData = EligibilityData::getFacilityData((int) $facilityId);
            $providerData = EligibilityData::getProviderData((int) $providerId);

            if ($facilityData != null) {
                $facilityName = TypeCoerce::asString($facilityData['facility_name'] ?? '');
                $facilityState = TypeCoerce::asString($facilityData['facility_state'] ?? '');
                $facilityNpi = TypeCoerce::asString($facilityData['facility_npi'] ?? '');
            }

            if ($providerData != null) {
                $providerPinCode = TypeCoerce::asString($providerData['provider_pin'] ?? '');
                $providerNpi = TypeCoerce::asString($providerData['provider_npi'] ?? '');
            }

            $revenueTools->practiceName = $facilityName;
            $revenueTools->practiceState = $facilityState;
            $revenueTools->npi = $facilityNpi;

            if ($useFacility === false) {
                $revenueTools->npi = $providerNpi;
            }

            $revenueTools->patientFirstName = TypeCoerce::asString($patientData['fname'] ?? '');
            $revenueTools->patientLastName = TypeCoerce::asString($patientData['lname'] ?? '');
            $revenueTools->patientGender = TypeCoerce::asString($patientData['sex'] ?? '');
            $revenueTools->patientDob = TypeCoerce::asString($patientData['dob'] ?? '');
            $revenueTools->patientSsn = TypeCoerce::asString($patientData['ss'] ?? '');
            $revenueTools->patientAddress1 = TypeCoerce::asString($patientData['street'] ?? '');
            $revenueTools->patientCity = TypeCoerce::asString($patientData['city'] ?? '');
            $revenueTools->patientState = TypeCoerce::asString($patientData['state'] ?? '');
            $revenueTools->patientZip = TypeCoerce::asString($patientData['postal_code'] ?? '');
            $revenueTools->patientEmailAddress = TypeCoerce::asString($patientData['email'] ?? '');

            $revenueTools->pinCode = $providerPinCode;
        }
        return $revenueTools;
    }
    /**
     * @param int|string                $pid
     * @param string                    $payer_responsibility
     * @param string|null               $eventDate
     * @param int|string|null           $facilityId
     * @param int|string|null           $providerId
     * @param list<int>|null            $productsToRun
     * @return list<RevenueToolsRequest>
     */
    public static function buildObject(
        int|string $pid,
        string $payer_responsibility,
        ?string $eventDate = null,
        int|string|null $facilityId = null,
        int|string|null $providerId = null,
        ?array $productsToRun = null,
    ): array {
        $results = [];
        $resultSubscribers = EligibilityData::getSubscriberData((int) $pid, $payer_responsibility);
        // Null productsToRun defaults to eligibility (see buildRevenueToolsRequest).
        $hasEligibility = $productsToRun === null || in_array(1, $productsToRun, true);
        $hasMbiFinder = $productsToRun !== null && in_array(5, $productsToRun, true);

        // Coverage Discovery, Demographics, and MBI Finder don't need an
        // insurance row — they query the payer using patient demographics.
        // When the patient has no insurance and the caller isn't asking for
        // Eligibility, build a single request from patient data alone.
        if ($resultSubscribers === [] && !$hasEligibility) {
            $pr = ValueMapping::mapPayerResponsibility($payer_responsibility);
            $results[] = EligibilityObjectCreator::buildRevenueToolsRequest($pid, $pr, $eventDate, $providerId, $facilityId, $productsToRun);
            return $results;
        }

        foreach ($resultSubscribers as $subscriberRow) {
            $pr = ValueMapping::mapPayerResponsibility(TypeCoerce::asString($subscriberRow['type'] ?? ''));
            $revenueTools = EligibilityObjectCreator::buildRevenueToolsRequest($pid, $pr, $eventDate, $providerId, $facilityId, $productsToRun);
            $subscriberNumber = TypeCoerce::asString($subscriberRow['policy_number'] ?? '');
            $revenueTools->subscriberFirstName = TypeCoerce::asString($subscriberRow['subscriber_fname'] ?? '');
            $revenueTools->subscriberLastName = TypeCoerce::asString($subscriberRow['subscriber_lname'] ?? '');
            $subscriberDob = TypeCoerce::asString($subscriberRow['subscriber_dob'] ?? '');
            if (!Utilities::isDateEmpty($subscriberDob)) {
                $revenueTools->subscriberDob = $subscriberDob;
            }

            if ($hasEligibility) {
                $payer = new RevenueToolsPayer();
                $payer->payerNumber = TypeCoerce::asString($subscriberRow['payerId'] ?? '');
                $payer->payerName = TypeCoerce::asString($subscriberRow['payer_name'] ?? '');
                $payer->subscriberNumber = $subscriberNumber;
                $revenueTools->payers = [$payer];
            }

            // MBI Finder reads the subscriber id from the top-level field; the
            // payers array is intentionally omitted for non-eligibility products.
            if ($hasMbiFinder) {
                $revenueTools->subscriberId = $subscriberNumber;
            }

            $results[] = $revenueTools;
        }

        return $results;
    }

    /**
     * @param int|string $pid
     */
    public static function saveSingleToDatabase(RevenueToolsRequest $req, int|string $pid): void
    {
        // Symfony ParameterBag::getInt() throws on non-numeric values (incl. empty
        // string), and the global comes from a config screen that defaults to ''.
        // Read as string and coerce so an unset value falls back to 0.
        $stale_age_raw = OEGlobalsBag::getInstance()->getString('oe_claimrev_eligibility_results_age', '0');
        $stale_age = is_numeric($stale_age_raw) ? (int) $stale_age_raw : 0;

        // If the existing record is too old or in a non-terminal state, drop
        // it so the new one can take over.  We don't care about successful
        // statuses — those should not be replaced.
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM mod_claimrev_eligibility WHERE pid = ? AND payer_responsibility = ? "
            . "AND (datediff(now(),create_date) >= ? or status in('error','waiting','creating') )",
            [$pid, $req->payerResponsibility, $stale_age]
        );

        $existing = QueryUtils::fetchRecords(
            "SELECT id FROM mod_claimrev_eligibility WHERE pid = ? AND payer_responsibility = ?",
            [$pid, $req->payerResponsibility]
        );
        if ($existing === []) {
            $newId = QueryUtils::sqlInsert(
                "INSERT INTO mod_claimrev_eligibility (pid,payer_responsibility,status,create_date) VALUES(?,?,?,NOW())",
                [$pid, $req->payerResponsibility, "creating"]
            );

            $req->originatingSystemId = (string) $newId;
            $json = json_encode($req, JSON_UNESCAPED_SLASHES);
            QueryUtils::sqlStatementThrowException(
                "UPDATE mod_claimrev_eligibility SET request_json = ?, status = ? where id = ?",
                [$json, "waiting", $newId]
            );
        }
    }

    /**
     * @param list<RevenueToolsRequest> $requests
     * @param int|string                $pid
     */
    public static function saveToDatabase(array $requests, int|string $pid): void
    {
        foreach ($requests as $req) {
            EligibilityObjectCreator::saveSingleToDatabase($req, $pid);
        }
    }
}
