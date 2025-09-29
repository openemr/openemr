<?php
namespace OpenEMR\RestControllers\VietnamesePT;

use OpenEMR\Services\VietnamesePT\PTAssessmentTemplateService;
use OpenEMR\RestControllers\RestControllerHelper;

class PTAssessmentTemplateRestController
{
    private $service;

    public function __construct()
    {
        $this->service = new PTAssessmentTemplateService();
    }

    public function getAll($search = [])
    {
        $processingResult = $this->service->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    public function getOne($id)
    {
        $processingResult = $this->service->getOne($id);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    public function post($data)
    {
        $processingResult = $this->service->insert($data);
        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    public function put($id, $data)
    {
        $processingResult = $this->service->update($id, $data);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    public function delete($id)
    {
        $processingResult = $this->service->delete($id);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }
}
