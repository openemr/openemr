<?php
namespace OpenEMR\RestControllers\VietnamesePT;

use OpenEMR\Services\VietnamesePT\PTExercisePrescriptionService;
use OpenEMR\RestControllers\RestControllerHelper;

class PTExercisePrescriptionRestController
{
    private $service;

    private const WHITELISTED_FIELDS = [
        'patient_id', 'encounter_id', 'exercise_name', 'exercise_name_vi',
        'description', 'description_vi', 'sets_prescribed', 'reps_prescribed',
        'duration_minutes', 'frequency_per_week', 'intensity_level',
        'instructions', 'instructions_vi', 'equipment_needed',
        'precautions', 'precautions_vi', 'start_date', 'end_date', 'prescribed_by'
    ];

    public function __construct()
    {
        $this->service = new PTExercisePrescriptionService();
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

    public function getPatientPrescriptions($patientId)
    {
        $processingResult = $this->service->getPatientPrescriptions($patientId);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
