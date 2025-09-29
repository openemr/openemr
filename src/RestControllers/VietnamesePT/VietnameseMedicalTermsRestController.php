<?php
namespace OpenEMR\RestControllers\VietnamesePT;

use OpenEMR\Services\VietnamesePT\VietnameseMedicalTermsService;
use OpenEMR\RestControllers\RestControllerHelper;

class VietnameseMedicalTermsRestController
{
    private $service;

    public function __construct()
    {
        $this->service = new VietnameseMedicalTermsService();
    }

    public function getAll($search = [])
    {
        $processingResult = $this->service->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    public function search($term, $language = 'en')
    {
        $processingResult = $this->service->searchTerms($term, $language);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    public function translate($term, $fromLanguage = 'en')
    {
        $result = $this->service->translate($term, $fromLanguage);
        $processingResult = new \OpenEMR\Validators\ProcessingResult();
        if ($result) {
            $processingResult->addData($result);
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        } else {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }
    }

    public function getCategories()
    {
        $categories = $this->service->getCategories();
        $processingResult = new \OpenEMR\Validators\ProcessingResult();
        $processingResult->addData($categories);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }
}
