<?php
namespace OpenEMR\RestControllers\VietnamesePT;

use OpenEMR\Services\VietnamesePT\PTAssessmentService;
use OpenEMR\RestControllers\RestControllerHelper;

class PTAssessmentRestController
{
    private $service;

    private const WHITELISTED_FIELDS = [
        'patient_id', 'encounter_id', 'assessment_date', 'therapist_id',
        'chief_complaint_en', 'chief_complaint_vi', 'pain_level',
        'pain_location_en', 'pain_location_vi', 'pain_description_en', 'pain_description_vi',
        'functional_goals_en', 'functional_goals_vi', 'treatment_plan_en', 'treatment_plan_vi',
        'language_preference', 'status', 'rom_measurements', 'strength_measurements', 'balance_assessment'
    ];

    public function __construct()
    {
        $this->service = new PTAssessmentService();
    }

    public function getAll($search = [])
    {
        $processingResult = $this->service->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    public function getOne($id)
    {
        $processingResult = $this->service->getOne($id);
        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    public function post($data)
    {
        $filteredData = $this->service->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->service->insert($filteredData);
        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    public function put($id, $data)
    {
        $filteredData = $this->service->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->service->update($id, $filteredData);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    public function delete($id)
    {
        $processingResult = $this->service->delete($id);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    public function getPatientAssessments($patientId)
    {
        $processingResult = $this->service->getPatientAssessments($patientId);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    public function searchVietnamese($term, $language = 'vi')
    {
        $processingResult = $this->service->searchByVietnameseText($term, $language);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
