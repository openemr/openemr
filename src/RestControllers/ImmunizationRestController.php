<?php

/**
 * ImmunizationRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\ImmunizationService;
use OpenEMR\RestControllers\RestControllerHelper;

class ImmunizationRestController
{
    private $immunizationService;

    /**
     * White list of immunization search fields
     */
    private const WHITELISTED_FIELDS = array();

    public function __construct()
    {
        $this->immunizationService = new ImmunizationService();
    }

    /**
     * Fetches a single immunization resource by id.
     * @param $uuid- The immunization uuid identifier in string format.
     */
    public function getOne($uuid)
    {
        $processingResult = $this->immunizationService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns immunization resources which match an optional search criteria.
     */
    public function getAll($search = array())
    {
        $validSearchFields = $this->immunizationService->filterData($search, self::WHITELISTED_FIELDS);
        $processingResult = $this->immunizationService->getAll($validSearchFields);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    /**
     * Process a HTTP POST request used to create a immunization record.
     * @param $data - array of immunization fields.
     * @return a 201/Created status code and the immunization identifier if successful.
     */
    // public function post($data)
    // {
    //     $filteredData = $this->immunizationService->filterData($data, self::WHITELISTED_FIELDS);
    //     $processingResult = $this->immunizationService->insert($filteredData);
    //     return RestControllerHelper::handleProcessingResult($processingResult, 201);
    // }

    /**
     * Processes a HTTP PATCH request used to update an existing immunization record.
     * @param $uuid - The immunization uuid identifier in string format.
     * @param $data - array of immunization fields (full resource).
     * @return a 200/Ok status code and the immunization resource.
     */
    // public function patch($uuid, $data)
    // {
    //     $filteredData = $this->immunizationService->filterData($data, self::WHITELISTED_FIELDS);
    //     $processingResult = $this->immunizationService->update($uuid, $filteredData);
    //     return RestControllerHelper::handleProcessingResult($processingResult, 200);
    // }
}
