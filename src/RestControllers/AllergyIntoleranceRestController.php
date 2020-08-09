<?php

/**
 * AllergyIntoleranceRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\AllergyIntoleranceService;
use OpenEMR\RestControllers\RestControllerHelper;

class AllergyIntoleranceRestController
{
    private $allergyIntoleranceService;

    /**
     * White list of search/insert fields
     */
    private const WHITELISTED_FIELDS = array(
        'title',
        'begdate',
        'enddate',
        'diagnosis'
    );

    public function __construct()
    {
        $this->allergyIntoleranceService = new AllergyIntoleranceService();
    }

    /**
     * Fetches a single allergyIntolerance resource by id.
     * @param $uuid - The allergyIntolerance uuid identifier in string format.
     */
    public function getOne($uuid)
    {
        $processingResult = $this->allergyIntoleranceService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns allergyIntolerance resources which match an optional search criteria.
     */
    public function getAll($search = array())
    {
        $processingResult = $this->allergyIntoleranceService->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    public function post($puuid, $data)
    {
        $filteredData = $this->allergyIntoleranceService->filterData($data, self::WHITELISTED_FIELDS);
        $filteredData['puuid'] = $puuid;
        $processingResult = $this->allergyIntoleranceService->insert($filteredData);
        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    public function put($puuid, $uuid, $data)
    {
        $filteredData = $this->allergyIntoleranceService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->allergyIntoleranceService->update($uuid, $filteredData);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    public function delete($puuid, $uuid)
    {
        $processingResult = $this->allergyIntoleranceService->delete($puuid, $uuid);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }
}
