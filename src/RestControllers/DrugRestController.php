<?php

/**
 * DrugRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\DrugService;
use OpenEMR\RestControllers\RestControllerHelper;

class DrugRestController
{
    private $drugService;

    public function __construct()
    {
        $this->drugService = new DrugService();
    }

    /**
     * Fetches a single drug resource by id.
     * @param $uuid- The drug uuid identifier in string format.
     */
    public function getOne($uuid)
    {
        $processingResult = $this->drugService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns drug resources which match an optional search criteria.
     */
    public function getAll($search = array())
    {
        $processingResult = $this->drugService->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
