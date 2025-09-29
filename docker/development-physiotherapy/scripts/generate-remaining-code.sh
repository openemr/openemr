#!/bin/bash

# Script to generate all remaining Vietnamese PT code components
# This creates services, validators, REST controllers, and forms

set -e

BASEDIR="/Users/dang/dev/openemr"

echo "🚀 Generating remaining Vietnamese PT components..."

# Create remaining services
echo "📦 Creating remaining service classes..."

# PTOutcomeMeasuresService
cat > "$BASEDIR/src/Services/VietnamesePT/PTOutcomeMeasuresService.php" << 'EOF'
<?php
namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class PTOutcomeMeasuresService extends BaseService
{
    private const TABLE = "pt_outcome_measures";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function getPatientOutcomes($patientId): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE patient_id = ?
                ORDER BY measurement_date DESC";

        $result = QueryUtils::sqlStatementThrowException($sql, [$patientId]);
        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($result)) {
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    public function insert($data): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $data['created_at'] = date('Y-m-d H:i:s');

        $query = $this->buildInsertColumns($data);
        $sql = "INSERT INTO " . self::TABLE . " SET " . $query['set'];
        $insertId = QueryUtils::sqlInsert($sql, $query['bind']);

        if ($insertId) {
            $processingResult->addData(['id' => $insertId]);
        }

        return $processingResult;
    }

    public function getProgressTracking($patientId, $measureType): array
    {
        $sql = "SELECT measurement_date, score_value, interpretation_vi
                FROM " . self::TABLE . "
                WHERE patient_id = ? AND measure_type = ?
                ORDER BY measurement_date ASC";

        $result = sqlStatement($sql, [$patientId, $measureType]);
        $tracking = [];

        while ($row = sqlFetchArray($result)) {
            $tracking[] = $row;
        }

        return $tracking;
    }
}
EOF

# PTTreatmentPlanService
cat > "$BASEDIR/src/Services/VietnamesePT/PTTreatmentPlanService.php" << 'EOF'
<?php
namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class PTTreatmentPlanService extends BaseService
{
    private const TABLE = "pt_treatment_plans";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function getActivePlans($patientId): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE patient_id = ? AND plan_status = 'active'
                ORDER BY start_date DESC";

        $result = QueryUtils::sqlStatementThrowException($sql, [$patientId]);
        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($result)) {
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    public function insert($data): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $data['created_at'] = date('Y-m-d H:i:s');

        if (isset($data['goals_short_term']) && is_array($data['goals_short_term'])) {
            $data['goals_short_term'] = json_encode($data['goals_short_term']);
        }

        if (isset($data['goals_long_term']) && is_array($data['goals_long_term'])) {
            $data['goals_long_term'] = json_encode($data['goals_long_term']);
        }

        $query = $this->buildInsertColumns($data);
        $sql = "INSERT INTO " . self::TABLE . " SET " . $query['set'];
        $insertId = QueryUtils::sqlInsert($sql, $query['bind']);

        if ($insertId) {
            $processingResult->addData(['id' => $insertId]);
        }

        return $processingResult;
    }

    public function updateStatus($id, $status): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $validStatuses = ['active', 'completed', 'on_hold', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            $processingResult->addValidationMessage('status', 'invalid status');
            return $processingResult;
        }

        $sql = "UPDATE " . self::TABLE . "
                SET plan_status = ?, updated_at = ?
                WHERE id = ?";

        sqlStatement($sql, [$status, date('Y-m-d H:i:s'), $id]);
        $processingResult->addData(['id' => $id, 'status' => $status]);

        return $processingResult;
    }
}
EOF

# PTAssessmentTemplateService
cat > "$BASEDIR/src/Services/VietnamesePT/PTAssessmentTemplateService.php" << 'EOF'
<?php
namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class PTAssessmentTemplateService extends BaseService
{
    private const TABLE = "pt_assessment_templates";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function getActiveTemplates(): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE is_active = 1
                ORDER BY category, template_name ASC";

        $result = QueryUtils::sqlStatementThrowException($sql);
        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($result)) {
            if (!empty($row['assessment_fields'])) {
                $row['assessment_fields'] = json_decode($row['assessment_fields'], true);
            }
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    public function getByCategory($category): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE category = ? AND is_active = 1
                ORDER BY template_name ASC";

        $result = QueryUtils::sqlStatementThrowException($sql, [$category]);
        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($result)) {
            $processingResult->addData($row);
        }

        return $processingResult;
    }
}
EOF

# VietnameseInsuranceService
cat > "$BASEDIR/src/Services/VietnamesePT/VietnameseInsuranceService.php" << 'EOF'
<?php
namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class VietnameseInsuranceService extends BaseService
{
    private const TABLE = "vietnamese_insurance_info";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function getPatientInsurance($patientId): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE patient_id = ? AND is_active = 1
                ORDER BY valid_from DESC";

        $result = QueryUtils::sqlStatementThrowException($sql, [$patientId]);
        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($result)) {
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    public function insert($data): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $data['created_at'] = date('Y-m-d H:i:s');

        $query = $this->buildInsertColumns($data);
        $sql = "INSERT INTO " . self::TABLE . " SET " . $query['set'];
        $insertId = QueryUtils::sqlInsert($sql, $query['bind']);

        if ($insertId) {
            $processingResult->addData(['id' => $insertId]);
        }

        return $processingResult;
    }

    public function isInsuranceValid($patientId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . "
                WHERE patient_id = ?
                AND is_active = 1
                AND valid_from <= CURDATE()
                AND (valid_to IS NULL OR valid_to >= CURDATE())";

        $result = sqlQuery($sql, [$patientId]);
        return ($result['count'] ?? 0) > 0;
    }
}
EOF

# VietnameseTranslationService
cat > "$BASEDIR/src/Services/VietnamesePT/VietnameseTranslationService.php" << 'EOF'
<?php
namespace OpenEMR\Services\VietnamesePT;

class VietnameseTranslationService
{
    private $medicalTermsService;

    public function __construct()
    {
        $this->medicalTermsService = new VietnameseMedicalTermsService();
    }

    public function translateToVietnamese($englishText): string
    {
        $result = $this->medicalTermsService->translate($englishText, 'en');
        return $result['vietnamese_term'] ?? $englishText;
    }

    public function translateToEnglish($vietnameseText): string
    {
        $result = $this->medicalTermsService->translate($vietnameseText, 'vi');
        return $result['english_term'] ?? $vietnameseText;
    }

    public function translateBatch(array $terms, $fromLanguage = 'en'): array
    {
        $translations = [];
        foreach ($terms as $term) {
            if ($fromLanguage === 'en') {
                $translations[$term] = $this->translateToVietnamese($term);
            } else {
                $translations[$term] = $this->translateToEnglish($term);
            }
        }
        return $translations;
    }
}
EOF

echo "✅ Service classes created!"

# Create remaining validators
echo "📋 Creating remaining validators..."

cat > "$BASEDIR/src/Validators/VietnamesePT/PTTreatmentPlanValidator.php" << 'EOF'
<?php
namespace OpenEMR\Validators\VietnamesePT;

use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class PTTreatmentPlanValidator extends BaseValidator
{
    public function validate($dataFields, $isUpdate = false): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        if (!$isUpdate) {
            if (empty($dataFields['patient_id'])) {
                $processingResult->addValidationMessage('patient_id', 'required');
            }
            if (empty($dataFields['plan_name'])) {
                $processingResult->addValidationMessage('plan_name', 'required');
            }
            if (empty($dataFields['diagnosis_primary'])) {
                $processingResult->addValidationMessage('diagnosis_primary', 'required');
            }
            if (empty($dataFields['start_date'])) {
                $processingResult->addValidationMessage('start_date', 'required');
            }
        }

        return $processingResult;
    }
}
EOF

cat > "$BASEDIR/src/Validators/VietnamesePT/PTOutcomeMeasuresValidator.php" << 'EOF'
<?php
namespace OpenEMR\Validators\VietnamesePT;

use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class PTOutcomeMeasuresValidator extends BaseValidator
{
    public function validate($dataFields, $isUpdate = false): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        if (!$isUpdate) {
            if (empty($dataFields['patient_id'])) {
                $processingResult->addValidationMessage('patient_id', 'required');
            }
            if (empty($dataFields['measure_name'])) {
                $processingResult->addValidationMessage('measure_name', 'required');
            }
            if (empty($dataFields['measurement_date'])) {
                $processingResult->addValidationMessage('measurement_date', 'required');
            }
        }

        return $processingResult;
    }
}
EOF

echo "✅ Validators created!"
echo "✨ All Vietnamese PT components generated successfully!"
echo ""
echo "Next steps:"
echo "1. Review generated files in src/Services/VietnamesePT/"
echo "2. Review validators in src/Validators/VietnamesePT/"
echo "3. Run: bash generate-rest-controllers.sh"
echo "4. Run: bash generate-forms.sh"