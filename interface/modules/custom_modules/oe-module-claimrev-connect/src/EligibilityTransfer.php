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

    use OpenEMR\Services\BaseService;
    use OpenEMR\Modules\ClaimRevConnector\ClaimRevApi;
    use OpenEMR\Modules\ClaimRevConnector\EligibilityData;

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

    public static function sendWaitingEligibility()
    {
        $token = ClaimRevApi::GetAccessToken();
        $waitingEligibility = EligibilityData::getEligibilityCheckByStatus(self::STATUS_WAITING);
        EligibilityTransfer::sendEligibility($waitingEligibility, $token);

        $retryEligibility = EligibilityData::getEligibilityResults(self::STATUS_SEND_RETRY, 60);
        EligibilityTransfer::retryEligibility($retryEligibility, $token);
    }

    public static function retryEligibility($retryEligibility, $token)
    {
        foreach ($retryEligibility as $eligibility) {
            $eid = $eligibility['id'];
            $result = ClaimRevApi::getEligibilityResult($eid, $token);
            EligibilityTransfer::saveEligibility($result, $eid);
        }
    }
    public static function sendEligibility($waitingEligibility, $token)
    {
        foreach ($waitingEligibility as $eligibility) {
            $eid = $eligibility['id'];
            $request_json = $eligibility['request_json'];

            $elig = json_decode($request_json);
            $result = ClaimRevApi::uploadEligibility($elig, $token);
            EligibilityTransfer::saveEligibility($result, $eid);
        }
    }
    public static function saveEligibility($result, $eid)
    {
        if (false === $result) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, null, true, 'no results', null, null, null);
            return;
        }
        $payload = json_encode($result, JSON_UNESCAPED_SLASHES);

        if (!property_exists($result, 'responseMessage')) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, 'missing responseMessage Property', null, null, null);
            return;
        }
        if (!property_exists($result, 'mappedData')) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, ' missing MappedData Property', null, null, null);
            return;
        }

        $responseMessage = $result->responseMessage;
        $mappedData = $result->mappedData;

        if ($result->isFatalError) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, $responseMessage, null, null, null);
            return;
        }
        if (!property_exists($mappedData, 'individuals')) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, $responseMessage . ' missing individuals Property', null, null, null);
            return;
        }

        $individuals = $mappedData->individuals;
        $individual = $individuals[ array_key_first($individuals) ];
        if ($individual === null) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, $responseMessage . ' missing individual Property', null, null, null);
            return;
        }


        if (!property_exists($individual, 'eligibility')) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_ERROR, null, $payload, true, $responseMessage . ' missing eligibility Property', null, null, null);
            return;
        }

        $eligibilities = $individual->eligibility;
        $eligibility = $eligibilities[ array_key_first($eligibilities) ];

        $raw271 = null;
        if (property_exists($eligibility, 'raw271')) {
            $raw271 = $eligibility->raw271;
            $siteDir = $GLOBALS['OE_SITE_DIR'];
            $reportFolder = "f271";
            $savePath = $siteDir . '/documents/edi/history/' . $reportFolder . '/';
            if (!file_exists($savePath)) {
                // Create a direcotry
                mkdir($savePath, 0777, true);
            }

            $fileText = $raw271;
            $fileName = $result->claimRevResultId;
            $filePathName =  $savePath . $fileName . '.txt';
            file_put_contents($filePathName, $fileText);
            chmod($filePathName, 0777);
        }

        if ($result->retryLater) {
            EligibilityData::updateEligibilityRecord($eid, self::STATUS_SEND_RETRY, null, $payload, true, $responseMessage, null, null, null);
            return;
        }

        $payload = json_encode($result, JSON_UNESCAPED_SLASHES);
        $eligibility_json = json_encode($eligibility, JSON_UNESCAPED_SLASHES);
        $individual_json = json_encode($individual, JSON_UNESCAPED_SLASHES);

        EligibilityData::updateEligibilityRecord($eid, self::STATUS_SUCCESS, null, $payload, true, $responseMessage, $raw271, $eligibility_json, $individual_json);
    }
}
