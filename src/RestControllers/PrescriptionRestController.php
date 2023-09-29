<?php

/**
 * PrescriptionRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\PrescriptionService;

class PrescriptionRestController
{
    /**
     * White list of search/insert fields
     */
    private const WHITELISTED_FIELDS = [
        "start_date",
        "route",
        "encounter_uuid",
        "drug",
        "drug_id",
        "quantity",
        "form_id",
        "route_id",
        "interval_id",
        "dosage",
        "size",
        "refills",
        "per_refill",
        "note",
        "medication",
        "substitute",
        "rxnorm_drugcode",
        "drug_dosage_instructions",
        "enddate",
        "usage_category",
        "usage_category_title",
        "request_intent",
        "request_intent_title",
        "active",
    ];

    private $prescriptionService;

    public function __construct()
    {
        $this->prescriptionService = new PrescriptionService();
    }

    /**
     * Fetches a single prescription resource by id.
     * @param $uuid - The prescription uuid identifier in string format.
     */
    public function getOne($uuid)
    {
        $processingResult = $this->prescriptionService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns prescription resources which match an optional search criteria.
     */
    public function getAll($search = array())
    {
        $processingResult = $this->prescriptionService->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    public function post($puuid, $data)
    {
        $filteredData = $this->prescriptionService->filterData($data, static::WHITELISTED_FIELDS);
        $filteredData['puuid'] = $puuid;
        $processingResult = $this->prescriptionService->insert($filteredData);

        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    public function put($puuid, $presuuid, array $data)
    {
        $filterDate = $this->prescriptionService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->prescriptionService->update($puuid, $presuuid, $filterDate);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    public function delete($puuid, $presuuid)
    {
        $processingResult = $this->prescriptionService->delete($puuid, $presuuid);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }
}
