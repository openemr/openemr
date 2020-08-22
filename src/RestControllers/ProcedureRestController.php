<?php

/**
 * ProcedureRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\ProcedureService;
use OpenEMR\RestControllers\RestControllerHelper;

class ProcedureRestController
{
    private $procedureService;

    public function __construct()
    {
        $this->procedureService = new ProcedureService();
    }

    /**
     * Fetches a single procedure resource by id.
     * @param $uuid- The procedure uuid identifier in string format.
     */
    public function getOne($uuid)
    {
        $processingResult = $this->procedureService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns procedure resources which match an optional search criteria.
     */
    public function getAll($search = array())
    {
        $processingResult = $this->procedureService->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
