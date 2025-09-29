#!/bin/bash

# Generate all Vietnamese PT REST Controllers

set -e

BASEDIR="/Users/dang/dev/openemr"
CONTROLLER_DIR="$BASEDIR/src/RestControllers/VietnamesePT"

echo "🎯 Generating REST Controllers..."

# 1. PTAssessmentRestController
cat > "$CONTROLLER_DIR/PTAssessmentRestController.php" << 'EOF'
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
EOF

# 2. VietnameseMedicalTermsRestController
cat > "$CONTROLLER_DIR/VietnameseMedicalTermsRestController.php" << 'EOF'
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
EOF

# 3. PTExercisePrescriptionRestController
cat > "$CONTROLLER_DIR/PTExercisePrescriptionRestController.php" << 'EOF'
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
EOF

# 4-8: Create remaining controllers
for controller in PTOutcomeMeasuresRestController PTTreatmentPlanRestController PTAssessmentTemplateRestController VietnameseInsuranceRestController VietnameseTranslationRestController; do

    service="${controller/RestController/Service}"

    cat > "$CONTROLLER_DIR/$controller.php" << EOF
<?php
namespace OpenEMR\RestControllers\VietnamesePT;

use OpenEMR\Services\VietnamesePT\\$service;
use OpenEMR\RestControllers\RestControllerHelper;

class $controller
{
    private \$service;

    public function __construct()
    {
        \$this->service = new $service();
    }

    public function getAll(\$search = [])
    {
        \$processingResult = \$this->service->getAll(\$search);
        return RestControllerHelper::handleProcessingResult(\$processingResult, 200, true);
    }

    public function getOne(\$id)
    {
        \$processingResult = \$this->service->getOne(\$id);
        return RestControllerHelper::handleProcessingResult(\$processingResult, 200);
    }

    public function post(\$data)
    {
        \$processingResult = \$this->service->insert(\$data);
        return RestControllerHelper::handleProcessingResult(\$processingResult, 201);
    }

    public function put(\$id, \$data)
    {
        \$processingResult = \$this->service->update(\$id, \$data);
        return RestControllerHelper::handleProcessingResult(\$processingResult, 200);
    }

    public function delete(\$id)
    {
        \$processingResult = \$this->service->delete(\$id);
        return RestControllerHelper::handleProcessingResult(\$processingResult, 200);
    }
}
EOF
    echo "✅ Created $controller"
done

echo "✨ All REST controllers created successfully!"
echo ""
echo "Created controllers:"
echo "  - PTAssessmentRestController"
echo "  - VietnameseMedicalTermsRestController"
echo "  - PTExercisePrescriptionRestController"
echo "  - PTOutcomeMeasuresRestController"
echo "  - PTTreatmentPlanRestController"
echo "  - PTAssessmentTemplateRestController"
echo "  - VietnameseInsuranceRestController"
echo "  - VietnameseTranslationRestController"