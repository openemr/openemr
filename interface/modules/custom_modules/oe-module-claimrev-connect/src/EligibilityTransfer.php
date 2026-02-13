<?php

/**
 * Eligibility transfer service for ClaimRev.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector;

if (!defined('OPENEMR_GLOBALS_LOADED')) {
    http_response_code(404);
    exit();
}

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
     * @param array<string, mixed>|null $result
     * @param int|string $eid
     */
    public static function saveEligibility(?array $result, $eid): void
    {
        if ($result === null) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, null, true, 'no results', null, null, null);
            return;
        }
        $payload = json_encode($result, JSON_UNESCAPED_SLASHES);

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


        if (!isset($individual['eligibility']) || !is_array($individual['eligibility'])) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, $responseMessage . ' missing eligibility Property', null, null, null);
            return;
        }

        /** @var array<int|string, array<string, mixed>> */
        $eligibilities = $individual['eligibility'];
        $eligibility = $eligibilities[array_key_first($eligibilities)] ?? null;

        $raw271 = null;
        if (is_array($eligibility) && isset($eligibility['raw271'])) {
            $raw271 = $eligibility['raw271'];
            $siteDir = $GLOBALS['OE_SITE_DIR'];
            $reportFolder = 'f271';
            $savePath = $siteDir . '/documents/edi/history/' . $reportFolder . '/';
            if (!file_exists($savePath)) {
                mkdir($savePath, 0750, true);
            }

            $fileText = $raw271;
            $fileName = $result['claimRevResultId'];
            $filePathName = $savePath . $fileName . '.txt';
            file_put_contents($filePathName, $fileText);
            chmod($filePathName, 0640);
        }

        if ($result['retryLater'] ?? false) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_RETRY, null, $payload, true, $responseMessage, null, null, null);
            return;
        }

        $payload = json_encode($result, JSON_UNESCAPED_SLASHES);
        $eligibility_json = json_encode($eligibility, JSON_UNESCAPED_SLASHES);
        $individual_json = json_encode($individual, JSON_UNESCAPED_SLASHES);

        EligibilityData::updateEligibilityRecord($eid, self::STATUS_SUCCESS, null, $payload, true, $responseMessage, $raw271, $eligibility_json, $individual_json);
    }
}
