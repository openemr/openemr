<?php

/**
 * ConditionRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\ConditionService;
use OpenEMR\RestControllers\RestControllerHelper;

class ConditionRestController
{
    private $conditionService;

    public function __construct()
    {
        $this->conditionService = new ConditionService();
    }

    /**
     * Fetches a single condition resource by id.
     * @param $uuid - The condition uuid identifier in string format.
     */
    public function getOne($uuid)
    {
        $processingResult = $this->conditionService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns condition resources which match an optional search criteria.
     */
    public function getAll($search = array())
    {
        $processingResult = $this->conditionService->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
