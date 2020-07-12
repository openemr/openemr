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
    private const WHITELISTED_FIELDS = array();

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
        $supportedSearch = $this->allergyIntoleranceService->filterData($search, self::WHITELISTED_FIELDS);
        $processingResult = $this->allergyIntoleranceService->getAll($supportedSearch);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

}
