# Vietnamese PT Feature - Implementation Guide

## Overview

This guide provides step-by-step instructions to complete the remaining 80% of the Vietnamese PT feature implementation.

**Status**: Database layer (100% ✅) + 2 Service classes created
**Remaining**: 6 services, 8 REST controllers, 6 forms, ACL, integration, reports

---

## What's Been Created

### ✅ Complete Components

1. **Database Layer** (100%)
   - 8 tables with Vietnamese collation
   - Stored procedures
   - 90 comprehensive tests

2. **Service Classes** (2/8 = 25%)
   - ✅ `PTAssessmentService.php` - COMPLETE
   - ✅ `VietnameseMedicalTermsService.php` - COMPLETE
   - ❌ 6 more services needed

3. **Documentation**
   - Test coverage README
   - Gap analysis
   - This implementation guide

---

## Step-by-Step Implementation

### Phase 1: Complete Service Layer (20-30 hours)

#### Service 3: PT Exercise Prescription Service

```bash
# Location: src/Services/VietnamesePT/PTExercisePrescriptionService.php
```

**Required Methods**:
- `getAll($search)` - Get all prescriptions
- `getOne($id)` - Get single prescription
- `getPatientPrescriptions($patientId)` - Get patient's exercises
- `insert($data)` - Create new prescription
- `update($id, $data)` - Update prescription
- `delete($id)` - Soft delete
- `getActivePrescriptions($patientId)` - Get active exercises

**Table**: `pt_exercise_prescriptions`

**Template**:
```php
<?php
namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class PTExercisePrescriptionService extends BaseService
{
    private const TABLE = "pt_exercise_prescriptions";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function getPatientPrescriptions($patientId): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE patient_id = ? AND is_active = 1
                ORDER BY start_date DESC";
        // ... implementation
    }

    // Add remaining methods following PTAssessmentService pattern
}
```

#### Service 4-8: Remaining Services

Create following same pattern:

1. **PTOutcomeMeasuresService.php** - Table: `pt_outcome_measures`
2. **PTTreatmentPlanService.php** - Table: `pt_treatment_plans`
3. **PTAssessmentTemplateService.php** - Table: `pt_assessment_templates`
4. **VietnameseInsuranceService.php** - Table: `vietnamese_insurance_info`
5. **VietnameseTranslationService.php** - Wrapper for medical terms translation

**Each service needs**:
- Constructor with table name
- CRUD methods (getAll, getOne, insert, update, delete)
- Patient-specific queries
- Search functionality
- Statistics/reporting methods

---

### Phase 2: Create Validators (4-6 hours)

#### Location: `src/Validators/VietnamesePT/`

**Files Needed**:
1. `PTAssessmentValidator.php`
2. `PTExercisePrescriptionValidator.php`
3. `PTTreatmentPlanValidator.php`
4. `PTOutcomeMeasuresValidator.php`

**Template**:
```php
<?php
namespace OpenEMR\Validators\VietnamesePT;

use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class PTAssessmentValidator extends BaseValidator
{
    public function validate($dataFields, $isUpdate = false): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // Required fields for new assessment
        if (!$isUpdate) {
            if (empty($dataFields['patient_id'])) {
                $processingResult->addValidationMessage('patient_id', 'required');
            }
            if (empty($dataFields['assessment_date'])) {
                $processingResult->addValidationMessage('assessment_date', 'required');
            }
        }

        // Validate pain level (0-10)
        if (isset($dataFields['pain_level'])) {
            if ($dataFields['pain_level'] < 0 || $dataFields['pain_level'] > 10) {
                $processingResult->addValidationMessage('pain_level', 'invalid range (0-10)');
            }
        }

        // Validate Vietnamese text encoding
        if (isset($dataFields['chief_complaint_vi'])) {
            if (!mb_check_encoding($dataFields['chief_complaint_vi'], 'UTF-8')) {
                $processingResult->addValidationMessage('chief_complaint_vi', 'invalid UTF-8 encoding');
            }
        }

        return $processingResult;
    }
}
```

---

### Phase 3: Create REST Controllers (12-16 hours)

#### Controller 1: PT Assessment REST Controller

**Location**: `src/RestControllers/VietnamesePT/PTAssessmentRestController.php`

```php
<?php
namespace OpenEMR\RestControllers\VietnamesePT;

use OpenEMR\Services\VietnamesePT\PTAssessmentService;
use OpenEMR\RestControllers\RestControllerHelper;

class PTAssessmentRestController
{
    private $service;

    private const WHITELISTED_FIELDS = [
        'patient_id',
        'encounter_id',
        'assessment_date',
        'therapist_id',
        'chief_complaint_en',
        'chief_complaint_vi',
        'pain_level',
        'pain_location_en',
        'pain_location_vi',
        'functional_goals_en',
        'functional_goals_vi',
        'treatment_plan_en',
        'treatment_plan_vi',
        'language_preference',
        'status'
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

    // Custom endpoint: Search by Vietnamese text
    public function searchVietnamese($searchTerm, $language = 'vi')
    {
        $processingResult = $this->service->searchByVietnameseText($searchTerm, $language);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    // Custom endpoint: Patient statistics
    public function getPatientStats($patientId)
    {
        $stats = $this->service->getPatientAssessmentStats($patientId);
        $processingResult = new \OpenEMR\Validators\ProcessingResult();
        $processingResult->addData($stats);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }
}
```

#### Remaining Controllers

Create 7 more controllers following same pattern:

1. `VietnameseMedicalTermsRestController.php`
2. `PTExercisePrescriptionRestController.php`
3. `PTOutcomeMeasuresRestController.php`
4. `PTTreatmentPlanRestController.php`
5. `PTAssessmentTemplateRestController.php`
6. `VietnameseInsuranceRestController.php`
7. `VietnameseTranslationRestController.php`

---

### Phase 4: Register REST Routes (2-3 hours)

#### Location: `_rest_routes.inc.php`

Add to existing routes file:

```php
// Vietnamese PT Assessment routes
RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/assessments'] = [
    'PTAssessmentRestController' => 'getAll',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/assessments/:id'] = [
    'PTAssessmentRestController' => 'getOne',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['POST /api/vietnamese-pt/assessments'] = [
    'PTAssessmentRestController' => 'post',
    'method' => 'POST',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['PUT /api/vietnamese-pt/assessments/:id'] = [
    'PTAssessmentRestController' => 'put',
    'method' => 'PUT',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['DELETE /api/vietnamese-pt/assessments/:id'] = [
    'PTAssessmentRestController' => 'delete',
    'method' => 'DELETE',
    'acl' => ['vietnamese-pt', 'write']
];

// Custom Vietnamese search endpoint
RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/assessments/search/:term'] = [
    'PTAssessmentRestController' => 'searchVietnamese',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

// Patient statistics endpoint
RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/patients/:id/assessment-stats'] = [
    'PTAssessmentRestController' => 'getPatientStats',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

// Medical terms routes
RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/medical-terms'] = [
    'VietnameseMedicalTermsRestController' => 'getAll',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/medical-terms/search/:term'] = [
    'VietnameseMedicalTermsRestController' => 'search',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/medical-terms/translate/:term'] = [
    'VietnameseMedicalTermsRestController' => 'translate',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

// Add remaining routes for other controllers...
```

---

### Phase 5: Create ACL Rules (2-3 hours)

#### Location: SQL migration or `sql/database.sql`

```sql
-- Vietnamese PT Module ACL Section
INSERT INTO `module_acl_sections` (`section_id`, `section_name`, `parent_section`, `section_identifier`, `module_id`)
VALUES
('vietnamese_pt', 'Vietnamese PT Module', '', 'vietnamese_pt', 0);

-- Vietnamese PT ACL Groups
INSERT INTO `module_acl_group_settings` (`module_id`, `group_id`, `section_id`, `allowed`, `activities`)
VALUES
(0, 1, 'vietnamese_pt', 1, 'write'), -- Administrators (full access)
(0, 2, 'vietnamese_pt', 1, 'view'),  -- Clinicians (view only)
(0, 3, 'vietnamese_pt', 1, 'write'); -- Physicians (full access)

-- Vietnamese PT User Permissions
INSERT INTO `module_acl_user_settings` (`module_id`, `user_id`, `section_id`, `allowed`, `activities`)
SELECT 0, `id`, 'vietnamese_pt', 1, 'write'
FROM `users`
WHERE `username` = 'admin'; -- Give admin full access

-- Vietnamese PT Specific Permissions
INSERT INTO `gacl_aro_groups` (`id`, `value`, `name`, `parent_id`, `lft`, `rgt`)
VALUES
(NULL, 'vietnamese_pt_view', 'Vietnamese PT - View', 10, 0, 0),
(NULL, 'vietnamese_pt_write', 'Vietnamese PT - Write', 10, 0, 0),
(NULL, 'vietnamese_pt_delete', 'Vietnamese PT - Delete', 10, 0, 0),
(NULL, 'vietnamese_pt_admin', 'Vietnamese PT - Admin', 10, 0, 0);
```

---

### Phase 6: Create Form Module (10-12 hours)

#### Directory Structure

```
interface/forms/vietnamese_pt_assessment/
├── new.php              # Create new assessment
├── view.php             # View existing assessment
├── save.php             # Save assessment
├── report.php           # Display in reports
├── info.txt             # Form registration info
└── templates/
    ├── assessment_form.php    # Main form template
    └── assessment_view.php    # View template
```

#### File 1: info.txt

```
Vietnamese PT Assessment
vietnamese_pt_assessment
1
1
Bilingual Vietnamese/English physiotherapy assessment form
```

#### File 2: new.php

```php
<?php
require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <title><?php echo xlt('Vietnamese PT Assessment'); ?></title>
    <meta charset="UTF-8">
</head>
<body class="body_top">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Vietnamese PT Assessment'); ?></h2>
            </div>
        </div>

        <form method="post" action="<?php echo $rootdir;?>/forms/vietnamese_pt_assessment/save.php?mode=new"
              name="pt_assessment" id="pt_assessment">

            <!-- Hidden fields -->
            <input type="hidden" name="pid" value="<?php echo attr($pid); ?>">
            <input type="hidden" name="encounter" value="<?php echo attr($encounter); ?>">
            <input type="hidden" name="assessment_date" value="<?php echo date('Y-m-d H:i:s'); ?>">

            <!-- Language Preference -->
            <div class="form-group">
                <label><?php echo xlt('Language Preference'); ?></label>
                <select name="language_preference" class="form-control">
                    <option value="vi"><?php echo xlt('Vietnamese'); ?></option>
                    <option value="en"><?php echo xlt('English'); ?></option>
                    <option value="both"><?php echo xlt('Both'); ?></option>
                </select>
            </div>

            <!-- Chief Complaint (Bilingual) -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo xlt('Chief Complaint (Vietnamese)'); ?></label>
                        <textarea name="chief_complaint_vi" class="form-control" rows="3"
                                  placeholder="Lý do khám chính..."></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo xlt('Chief Complaint (English)'); ?></label>
                        <textarea name="chief_complaint_en" class="form-control" rows="3"
                                  placeholder="Primary reason for visit..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Pain Assessment -->
            <div class="form-group">
                <label><?php echo xlt('Pain Level (0-10)'); ?></label>
                <input type="number" name="pain_level" class="form-control" min="0" max="10">
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo xlt('Pain Location (Vietnamese)'); ?></label>
                        <input type="text" name="pain_location_vi" class="form-control"
                               placeholder="Vị trí đau...">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo xlt('Pain Location (English)'); ?></label>
                        <input type="text" name="pain_location_en" class="form-control"
                               placeholder="Pain location...">
                    </div>
                </div>
            </div>

            <!-- Treatment Plan (Bilingual) -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo xlt('Treatment Plan (Vietnamese)'); ?></label>
                        <textarea name="treatment_plan_vi" class="form-control" rows="4"
                                  placeholder="Kế hoạch điều trị..."></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo xlt('Treatment Plan (English)'); ?></label>
                        <textarea name="treatment_plan_en" class="form-control" rows="4"
                                  placeholder="Treatment plan..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="form-group">
                <button type="submit" name="status" value="draft" class="btn btn-secondary">
                    <?php echo xlt('Save as Draft'); ?>
                </button>
                <button type="submit" name="status" value="completed" class="btn btn-primary">
                    <?php echo xlt('Save and Complete'); ?>
                </button>
                <button type="button" class="btn btn-default" onclick="top.restoreSession(); parent.closeTab(window.name, false);">
                    <?php echo xlt('Cancel'); ?>
                </button>
            </div>
        </form>
    </div>
</body>
</html>
```

#### File 3: save.php

```php
<?php
require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\VietnamesePT\PTAssessmentService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$service = new PTAssessmentService();

$data = [
    'patient_id' => $_POST['pid'] ?? null,
    'encounter_id' => $_POST['encounter'] ?? null,
    'assessment_date' => $_POST['assessment_date'] ?? date('Y-m-d H:i:s'),
    'therapist_id' => $_SESSION['authUserID'] ?? null,
    'chief_complaint_en' => $_POST['chief_complaint_en'] ?? '',
    'chief_complaint_vi' => $_POST['chief_complaint_vi'] ?? '',
    'pain_level' => $_POST['pain_level'] ?? null,
    'pain_location_en' => $_POST['pain_location_en'] ?? '',
    'pain_location_vi' => $_POST['pain_location_vi'] ?? '',
    'treatment_plan_en' => $_POST['treatment_plan_en'] ?? '',
    'treatment_plan_vi' => $_POST['treatment_plan_vi'] ?? '',
    'language_preference' => $_POST['language_preference'] ?? 'vi',
    'status' => $_POST['status'] ?? 'draft'
];

if ($_GET['mode'] == 'new') {
    $result = $service->insert($data);
    $formid = $result->getData()[0]['id'] ?? null;

    if ($formid) {
        // Register form in forms table
        addForm($data['encounter_id'], "Vietnamese PT Assessment", $formid,
                "vietnamese_pt_assessment", $data['patient_id'], 1);
    }
} else {
    $formid = $_GET['id'] ?? null;
    $result = $service->update($formid, $data);
}

formHeader("Redirecting....");
formJump();
formFooter();
?>
```

---

### Phase 7: Integration (6-8 hours)

#### Add to Patient Encounter Menu

**Location**: `interface/patient_file/encounter/forms.php`

```php
// Add Vietnamese PT Assessment to forms list
$formdir["vietnamese_pt_assessment"] = "Vietnamese PT Assessment";
```

#### Add to Patient Summary

**Location**: `interface/patient_file/summary/patient_summary.php`

```php
// Add Vietnamese PT widget to patient summary
<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <?php echo xlt('Vietnamese PT Assessments'); ?>
        </div>
        <div class="card-body">
            <?php
            $ptService = new \OpenEMR\Services\VietnamesePT\PTAssessmentService();
            $assessments = $ptService->getPatientAssessments($pid);
            foreach ($assessments->getData() as $assessment) {
                echo "<div class='pt-assessment-item'>";
                echo text($assessment['assessment_date']) . " - ";
                echo text($assessment['chief_complaint_vi'] ?? $assessment['chief_complaint_en']);
                echo "</div>";
            }
            ?>
        </div>
    </div>
</div>
```

---

## Testing Your Implementation

### 1. Test Service Classes

```bash
# Run Vietnamese test suite
vendor/bin/phpunit --testsuite vietnamese

# Run specific service tests
vendor/bin/phpunit tests/Tests/Services/Vietnamese/
```

### 2. Test REST API

```bash
# Get all assessments
curl -X GET "http://localhost/apis/default/api/vietnamese-pt/assessments" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Create assessment
curl -X POST "http://localhost/apis/default/api/vietnamese-pt/assessments" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "patient_id": 1,
    "encounter_id": 1,
    "chief_complaint_vi": "Đau lưng",
    "chief_complaint_en": "Back pain",
    "pain_level": 7,
    "status": "completed"
  }'
```

### 3. Test Forms

1. Navigate to patient encounter
2. Click "vietnamese_pt_assessment" form
3. Fill in bilingual data with Vietnamese characters
4. Save and verify data persistence

---

## Deployment Checklist

- [ ] All 8 service classes created
- [ ] All 8 REST controllers created
- [ ] All validators created
- [ ] REST routes registered
- [ ] ACL rules installed
- [ ] At least 1 form module complete
- [ ] Integration with patient summary
- [ ] Integration with encounter menu
- [ ] Tests passing (90/90)
- [ ] Vietnamese encoding verified
- [ ] PDF reports functional
- [ ] Documentation complete

---

## Getting Help

If you need assistance:
1. Review existing OpenEMR forms in `interface/forms/`
2. Check service patterns in `src/Services/`
3. Review REST controller examples in `src/RestControllers/`
4. Consult OpenEMR development documentation
5. Test with Vietnamese characters: Vật lý trị liệu, Đau lưng

---

**Document Version**: 1.0
**Last Updated**: 2025-09-29
**Estimated Completion Time**: 8-12 weeks (full-time)