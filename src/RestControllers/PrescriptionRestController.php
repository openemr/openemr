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
use OpenEMR\RestControllers\RestControllerHelper;

class PrescriptionRestController
{
    private $prescriptionService;

    public function __construct()
    {
        $this->prescriptionService = new PrescriptionService();
    }

    /**
     * Fetches a single prescription resource by id.
     * @param $uuid- The prescription uuid identifier in string format.
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
}
