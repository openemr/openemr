<?php

namespace OpenEMR\RestControllers;

use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\SurgeryService;

class SurgeryRestController
{
    private $surgeryService;

    public function __construct()
    {
        $this->surgeryService = new SurgeryService();
    }

    /**
     * Fetches a single surgery resource by id.
     * @param $uuid- The surgery uuid identifier in string format.
     */
    public function getOne($uuid)
    {
        $processingResult = $this->surgeryService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns surgery resources which match an optional search criteria.
     */
    public function getAll($search = array())
    {
        $processingResult = $this->surgeryService->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
