<?php

/**
 * Eligibility transfer service for ClaimRev.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

if (!defined('OPENEMR_GLOBALS_LOADED')) {
    http_response_code(404);
    exit();
}

use OpenEMR\Billing\EDI270;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\BaseService;

class EligibilityTransfer extends BaseService
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_SEND_ERROR = 'senderror';
    public const STATUS_SEND_RETRY = 'retry';
    public const TABLE_NAME = 'mod_claimrev_eligibility';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public static function sendWaitingEligibility(): void
    {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $testMode = $bootstrap->getGlobalConfig()->isTestModeEnabled();

        if ($testMode) {
            // Resolve all queued requests via the mock; the cron service
            // still drains the queue but never hits the live API.
            /** @var array<int, array{id: int|string, request_json?: string, pid?: int|string, payer_responsibility?: string}> */
            $waitingEligibility = EligibilityData::getEligibilityCheckByStatus(self::STATUS_WAITING);
            foreach ($waitingEligibility as $row) {
                $eid = $row['id'];
                $rowPid = TypeCoerce::asInt($row['pid'] ?? 0);
                $rowPr = TypeCoerce::asString($row['payer_responsibility'] ?? 'P');
                if ($rowPid === 0) {
                    self::saveEligibility(null, $eid);
                    continue;
                }
                $result = EligibilityMockService::buildResponse($rowPid, $rowPr);
                self::saveEligibility($result, $eid);
            }
            return;
        }

        try {
            $api = ClaimRevApi::makeFromGlobals();
        } catch (ClaimRevAuthenticationException) {
            return;
        }

        /** @var array<int, array{id: int|string, request_json?: string}> */
        $waitingEligibility = EligibilityData::getEligibilityCheckByStatus(self::STATUS_WAITING);
        self::sendEligibility($waitingEligibility, $api);

        /** @var array<int, array{id: int|string}> */
        $retryEligibility = EligibilityData::getEligibilityResults(self::STATUS_SEND_RETRY, 60);
        self::retryEligibility($retryEligibility, $api);
    }

    /**
     * @param array<int, array{id: int|string}> $retryEligibility
     */
    public static function retryEligibility(array $retryEligibility, ClaimRevApi $api): void
    {
        foreach ($retryEligibility as $eligibility) {
            $eid = $eligibility['id'];
            try {
                $result = $api->getEligibilityResult((string) $eid);
            } catch (ClaimRevApiException) {
                self::saveEligibility(null, $eid);
                continue;
            }
            self::saveEligibility($result, $eid);
        }
    }

    /**
     * @param array<int, array{id: int|string, request_json?: string}> $waitingEligibility
     */
    public static function sendEligibility(array $waitingEligibility, ClaimRevApi $api): void
    {
        foreach ($waitingEligibility as $eligibility) {
            $eid = $eligibility['id'];
            $request_json = $eligibility['request_json'] ?? '';

            $elig = json_decode($request_json);
            if (!is_object($elig)) {
                self::saveEligibility(null, $eid);
                continue;
            }
            try {
                $result = $api->uploadEligibility($elig);
            } catch (ClaimRevApiException) {
                self::saveEligibility(null, $eid);
                continue;
            }
            self::saveEligibility($result, $eid);
        }
    }

    /**
     * Product property keys in the individual JSON, keyed by product ID.
     */
    private const PRODUCT_KEYS = [
        1 => 'eligibility',
        2 => 'demographicInfo',
        3 => 'coverageDiscovery',
        5 => 'mbiFinderResults',
    ];

    /**
     * Send eligibility request immediately (real-time) and save the result.
     *
     * Works for any product: Eligibility (1), Demographics (2), Coverage Discovery (3), MBI Finder (5).
     *
     * @param int|string      $pid                 Patient ID
     * @param string          $payerResponsibility Payer responsibility code (e.g. 'primary')
     * @param list<int>       $productsToRun       Product IDs to check
     * @param string|null     $eventDate           Optional appointment date
     * @param int|string|null $facilityId          Optional facility ID
     * @param int|string|null $providerId          Optional provider ID
     * @return array{success: bool, message: string, eid?: int|string, coverageStatus?: string, payerName?: string}
     */
    public static function sendImmediate(
        int|string $pid,
        string $payerResponsibility,
        array $productsToRun = [1],
        ?string $eventDate = null,
        int|string|null $facilityId = null,
        int|string|null $providerId = null,
    ): array {
        $bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
        $testMode = $bootstrap->getGlobalConfig()->isTestModeEnabled();

        // In test mode skip the live API entirely; the mock builds a
        // SharpRevenue-shaped response so the rest of the flow (saveEligibility,
        // mappedData rendering, AI chat) works without a real payer.
        $api = null;
        if (!$testMode) {
            try {
                $api = ClaimRevApi::makeFromGlobals();
            } catch (ClaimRevException) {
                return ['success' => false, 'message' => 'Failed to connect to ClaimRev API'];
            }
        }

        $pidInt = (int) $pid;
        $formattedPr = ValueMapping::mapPayerResponsibility($payerResponsibility);

        // Check for existing record to merge into
        $existingRecord = EligibilityData::getExistingRecord($pidInt, $formattedPr);
        $existingIndividual = [];
        if ($existingRecord !== null && strtolower(TypeCoerce::asString($existingRecord['status'] ?? '')) === 'success') {
            $decoded = json_decode(TypeCoerce::asString($existingRecord['individual_json'] ?? ''), true);
            if (is_array($decoded)) {
                /** @var array<string, mixed> $existingIndividual */
                $existingIndividual = $decoded;
            }
        }

        // Delete and recreate the record for the API call
        EligibilityData::removeEligibilityCheck($pidInt, $formattedPr);

        $requestObjects = EligibilityObjectCreator::buildObject($pid, $payerResponsibility, $eventDate, $facilityId, $providerId, $productsToRun);
        if ($requestObjects === []) {
            return ['success' => false, 'message' => 'No insurance data found for patient'];
        }

        // Save to DB first to get the record ID
        EligibilityObjectCreator::saveToDatabase($requestObjects, $pid);

        // Now fetch the waiting record we just created
        $eligRecord = EligibilityData::getEligibilityResult($pidInt, $payerResponsibility);
        $eid = null;
        foreach ($eligRecord as $rec) {
            $waitingRecords = EligibilityData::getEligibilityCheckByStatus(self::STATUS_WAITING);
            foreach ($waitingRecords as $wr) {
                if ($wr['pid'] == $pid && $wr['payer_responsibility'] == $formattedPr) {
                    $eid = $wr['id'];
                    break;
                }
            }
            break;
        }

        if ($eid === null) {
            return ['success' => false, 'message' => 'Failed to create eligibility request'];
        }
        if (!is_int($eid) && !is_string($eid)) {
            $eid = TypeCoerce::asString($eid);
        }

        // Send immediately via the API (or build a mock response in test mode)
        $req = $requestObjects[0];
        if ($testMode) {
            $result = EligibilityMockService::buildResponse($pidInt, $payerResponsibility, $productsToRun);
        } else {
            try {
                $result = $api->uploadEligibility($req);
            } catch (ClaimRevApiException) {
                self::saveEligibility(null, $eid);
                return ['success' => false, 'message' => 'API call failed', 'eid' => $eid];
            }

            // If retryLater, poll for results (like the portal does for coverage discovery)
            if ($result['retryLater'] ?? false) {
                $claimRevResultId = TypeCoerce::asString($result['claimRevResultId'] ?? '');
                if ($claimRevResultId !== '') {
                    $result = self::pollForResults($api, $claimRevResultId, $result);
                }
            }
        }

        // Track claimRevResultId per product so the AI chat can reference the right one
        $newResultIdStr = TypeCoerce::asString($result['claimRevResultId'] ?? '');
        $existingResultIds = [];
        if ($existingRecord !== null) {
            $existingResponse = json_decode(TypeCoerce::asString($existingRecord['response_json'] ?? ''), true);
            if (is_array($existingResponse) && is_array($existingResponse['_productResultIds'] ?? null)) {
                /** @var array<int|string, string> $existingResultIds */
                $existingResultIds = $existingResponse['_productResultIds'];
            }
        }
        // Map each product we just ran to the new claimRevResultId
        foreach ($productsToRun as $productId) {
            if ($newResultIdStr !== '') {
                $existingResultIds[$productId] = $newResultIdStr;
            }
        }
        $result['_productResultIds'] = $existingResultIds;

        // Merge new results with existing individual data
        if ($existingIndividual !== []) {
            $result = self::mergeProductResults($result, $existingIndividual, $productsToRun);
        }

        self::saveEligibility($result, $eid);

        // Extract coverage status from the result to return to the caller
        $coverageStatus = 'Complete';
        $payerName = '';
        $mappedData = $result['mappedData'] ?? null;
        if (is_array($mappedData) && isset($mappedData['individuals']) && is_array($mappedData['individuals'])) {
            $firstKey = array_key_first($mappedData['individuals']);
            $individual = $firstKey !== null ? ($mappedData['individuals'][$firstKey] ?? null) : null;
            if (is_array($individual)) {
                // Try eligibility first, then coverage discovery
                $eligData = $individual['eligibility'] ?? $individual['coverageDiscovery'] ?? null;
                if (is_array($eligData) && $eligData !== []) {
                    $firstEligKey = array_key_first($eligData);
                    $firstElig = $eligData[$firstEligKey] ?? null;
                    if (is_array($firstElig)) {
                        $coverageStatus = TypeCoerce::asString($firstElig['status'] ?? 'Complete');
                        $payerInfo = $firstElig['payerInfo'] ?? null;
                        if (is_array($payerInfo)) {
                            $payerName = TypeCoerce::asString($payerInfo['payerName'] ?? '');
                        }
                    }
                }
            }
        }

        return [
            'success' => true,
            'message' => $coverageStatus,
            'eid' => $eid,
            'coverageStatus' => $coverageStatus,
            'payerName' => $payerName,
        ];
    }

    /**
     * Poll the API for async results (matches portal behavior).
     *
     * Polls every 3 seconds, up to 20 attempts. Returns the final result
     * or the original result if polling times out.
     *
     * @param array<string, mixed> $originalResult Fallback if polling fails
     * @return array<string, mixed>
     */
    private static function pollForResults(ClaimRevApi $api, string $claimRevResultId, array $originalResult): array
    {
        $maxPolls = 20;
        $pollInterval = 3; // seconds

        for ($i = 0; $i < $maxPolls; $i++) {
            sleep($pollInterval);

            try {
                $visit = $api->getSharpRevenueVisit($claimRevResultId);
            } catch (ClaimRevApiException) {
                continue;
            }

            $sharp = is_array($visit['sharpRevenueData'] ?? null) ? $visit['sharpRevenueData'] : null;
            $sharpMapped = is_array($sharp['mappedData'] ?? null) ? $sharp['mappedData'] : null;
            $visitMapped = is_array($visit['mappedData'] ?? null) ? $visit['mappedData'] : null;
            $status = ($sharpMapped['status'] ?? null) ?? ($visitMapped['status'] ?? null);

            if ($status !== null) {
                $statusLower = strtolower(TypeCoerce::asString($status));
                if ($statusLower === 'complete' || $statusLower === 'error') {
                    // Extract the SharpRevenue response in the same format saveEligibility expects
                    /** @var array<string, mixed> $result */
                    $result = $sharp ?? $visit;
                    return $result;
                }
            }
        }

        // Timed out — return whatever we had from the initial call
        return $originalResult;
    }

    /**
     * Merge new API results with existing individual data.
     *
     * New product results overwrite their corresponding keys.
     * Existing product results that weren't re-run are preserved.
     *
     * @param array<string, mixed> $newResult The new API response
     * @param array<string, mixed> $existingIndividual The previously stored individual JSON
     * @param array<int> $productsRun Product IDs that were just run
     * @return array<string, mixed> The merged result
     */
    private static function mergeProductResults(array $newResult, array $existingIndividual, array $productsRun): array
    {
        $mappedData = $newResult['mappedData'] ?? null;
        if (!is_array($mappedData)) {
            return $newResult;
        }
        $individuals = $mappedData['individuals'] ?? null;
        if (!is_array($individuals) || $individuals === []) {
            return $newResult;
        }

        $key = array_key_first($individuals);
        $newIndividual = $individuals[$key] ?? null;
        if (!is_array($newIndividual)) {
            return $newResult;
        }

        // For each product that was NOT run, preserve the existing data
        foreach (self::PRODUCT_KEYS as $productId => $propertyKey) {
            if (!in_array($productId, $productsRun) && isset($existingIndividual[$propertyKey])) {
                $newIndividual[$propertyKey] = $existingIndividual[$propertyKey];
            }
        }

        // Preserve insuranceFinderStatus from existing data if coverage discovery wasn't re-run
        if (!in_array(3, $productsRun) && isset($existingIndividual['insuranceFinderStatus'])) {
            $newIndividual['insuranceFinderStatus'] = $existingIndividual['insuranceFinderStatus'];
        }

        $individuals[$key] = $newIndividual;
        $mappedData['individuals'] = $individuals;
        $newResult['mappedData'] = $mappedData;

        return $newResult;
    }

    /**
     * @param array<string, mixed>|null $result
     * @param int|string $eid
     */
    public static function saveEligibility(?array $result, $eid): void
    {
        if ($result === null) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, null, true, 'no results', null, null, null);
            return;
        }
        $encoded = json_encode($result, JSON_UNESCAPED_SLASHES);
        $payload = $encoded !== false ? $encoded : null;

        if (!isset($result['responseMessage'])) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, 'missing responseMessage Property', null, null, null);
            return;
        }
        if (!isset($result['mappedData'])) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, ' missing MappedData Property', null, null, null);
            return;
        }

        /** @var string */
        $responseMessage = $result['responseMessage'];
        /** @var array<string, mixed> */
        $mappedData = $result['mappedData'];

        if ($result['isFatalError'] ?? false) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, $responseMessage, null, null, null);
            return;
        }
        if (!isset($mappedData['individuals']) || !is_array($mappedData['individuals'])) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, $responseMessage . ' missing individuals Property', null, null, null);
            return;
        }

        /** @var array<int|string, array<string, mixed>> */
        $individuals = $mappedData['individuals'];
        $individual = $individuals[array_key_first($individuals)] ?? null;
        if ($individual === null) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, $responseMessage . ' missing individual Property', null, null, null);
            return;
        }


        // Note: retryLater from Zoll is unreliable — it's often set even when
        // results are present, and the retry mechanism doesn't reliably follow up.
        // Process whatever results came back regardless of this flag.

        $eligibility = null;
        $eligibility_json = null;
        $raw271 = null;

        // Process eligibility results (Product 1) if present
        if (isset($individual['eligibility']) && is_array($individual['eligibility']) && $individual['eligibility'] !== []) {
            /** @var array<int|string, array<string, mixed>> */
            $eligibilities = $individual['eligibility'];
            $firstKey = array_key_first($eligibilities);
            $eligibility = $firstKey !== null ? $eligibilities[$firstKey] : null;
            $encodedElig = json_encode($eligibility, JSON_UNESCAPED_SLASHES);
            $eligibility_json = $encodedElig !== false ? $encodedElig : null;

            if (is_array($eligibility) && isset($eligibility['raw271']) && $eligibility['raw271'] !== '') {
                $raw271 = TypeCoerce::asString($eligibility['raw271']);
                $siteDir = OEGlobalsBag::getInstance()->getString('OE_SITE_DIR');
                $reportFolder = 'f271';
                $savePath = $siteDir . '/documents/edi/history/' . $reportFolder . '/';
                if (!file_exists($savePath)) {
                    mkdir($savePath, 0750, true);
                }

                $fileName = TypeCoerce::asString($result['claimRevResultId'] ?? '');
                $filePathName = $savePath . $fileName . '.txt';
                file_put_contents($filePathName, $raw271);
                chmod($filePathName, 0640);

                // Populate native OpenEMR eligibility tables so the Insurance
                // card's Eligibility tab shows data from ClaimRev.
                self::populateNativeEligibility($raw271, $eid);
            }
        }

        $encodedFinal = json_encode($result, JSON_UNESCAPED_SLASHES);
        $payload = $encodedFinal !== false ? $encodedFinal : null;
        $encodedIndividual = json_encode($individual, JSON_UNESCAPED_SLASHES);
        $individual_json = $encodedIndividual !== false ? $encodedIndividual : null;

        EligibilityData::updateEligibilityRecord($eid, self::STATUS_SUCCESS, null, $payload, true, $responseMessage, $raw271, $eligibility_json, $individual_json);
    }

    /**
     * Feed raw 271 EDI through OpenEMR's native parser to populate the
     * eligibility_verification and benefit_eligibility tables.
     *
     * Injects a REF*EJ*{pid} segment so the parser reliably matches the
     * patient even if name/DOB matching would fail.
     *
     * @param string $raw271 Raw X12 271 EDI content
     * @param int|string $eid ClaimRev eligibility record ID
     */
    public static function populateNativeEligibility(string $raw271, int|string $eid): void
    {
        // Look up the PID for this eligibility record
        $row = QueryUtils::querySingleRow(
            'SELECT pid FROM mod_claimrev_eligibility WHERE id = ?',
            [$eid]
        );
        $pid = TypeCoerce::asInt($row['pid'] ?? 0);
        if ($pid === 0) {
            return;
        }

        // Inject a REF*EJ*{pid} segment so the native 271 parser can
        // reliably identify the patient.  Insert it right after the first
        // DMG segment (subscriber demographics), which is where REF*EJ
        // would normally appear in a standard 271.
        if (!str_contains($raw271, 'REF*EJ*')) {
            // Insert after the first DMG segment's tilde terminator
            $dmgPos = strpos($raw271, 'DMG*');
            if ($dmgPos !== false) {
                $tildePos = strpos($raw271, '~', $dmgPos);
                if ($tildePos !== false) {
                    $raw271 = substr($raw271, 0, $tildePos + 1)
                        . 'REF*EJ*' . $pid . '~'
                        . substr($raw271, $tildePos + 1);
                }
            }
        }

        try {
            EDI270::parseEdi271($raw271);
        } catch (\RuntimeException | \LogicException) {
            // Non-fatal — ClaimRev's own eligibility display still works.
            // The native Insurance card tab just won't be populated.
        }
    }
}
