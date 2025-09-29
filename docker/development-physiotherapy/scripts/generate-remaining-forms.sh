#!/bin/bash

# Generate Vietnamese PT Forms - Treatment Plan and Outcome Measures
# This script creates the remaining form modules to achieve 100% completion

set -e

OPENEMR_ROOT="/Users/dang/dev/openemr"
FORMS_DIR="$OPENEMR_ROOT/interface/forms"

echo "🎯 Generating Vietnamese PT Forms..."
echo ""

# ============================================
# Treatment Plan Form
# ============================================

echo "Creating Treatment Plan Form..."

mkdir -p "$FORMS_DIR/vietnamese_pt_treatment_plan"

cat > "$FORMS_DIR/vietnamese_pt_treatment_plan/info.txt" << 'EOF'
Vietnamese PT Treatment Plan
vietnamese_pt_treatment_plan
1
1
Bilingual Vietnamese/English physiotherapy treatment plan form
EOF

cat > "$FORMS_DIR/vietnamese_pt_treatment_plan/new.php" << 'EOF'
<?php
$viewmode = 'new';
require_once("common.php");
EOF

cat > "$FORMS_DIR/vietnamese_pt_treatment_plan/common.php" << 'EOF'
<?php
require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if ($viewmode == 'update') {
    $obj = formFetch("pt_treatment_plans", $_GET["id"]);
} else {
    $obj = null;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Vietnamese PT Treatment Plan'); ?></title>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <style>
        .bilingual-group {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .vietnamese-field { background-color: #fff3cd; }
        .english-field { background-color: #d1ecf1; }
    </style>
</head>
<body class="body_top">
    <div class="container-fluid mt-3">
        <h2><?php echo xlt('Vietnamese PT Treatment Plan'); ?></h2>

        <form method="post" action="<?php echo $rootdir; ?>/forms/vietnamese_pt_treatment_plan/save.php?mode=<?php echo attr_url($viewmode); ?>&id=<?php echo attr_url($_GET['id'] ?? 0); ?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
            <input type="hidden" name="pid" value="<?php echo attr($pid); ?>">
            <input type="hidden" name="encounter" value="<?php echo attr($encounter); ?>">
            <input type="hidden" name="created_by" value="<?php echo attr($_SESSION['authUserID']); ?>">

            <div class="form-group">
                <label><?php echo xlt('Plan Name'); ?>:</label>
                <input type="text" name="plan_name" class="form-control" required
                       value="<?php echo attr($obj['plan_name'] ?? ''); ?>">
            </div>

            <div class="bilingual-group">
                <h4><?php echo xlt('Diagnosis'); ?></h4>
                <div class="form-group">
                    <label><?php echo xlt('Vietnamese'); ?>:</label>
                    <input type="text" name="diagnosis_vi" class="form-control vietnamese-field"
                           value="<?php echo attr($obj['diagnosis_vi'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo xlt('English'); ?>:</label>
                    <input type="text" name="diagnosis_en" class="form-control english-field"
                           value="<?php echo attr($obj['diagnosis_en'] ?? ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Start Date'); ?>:</label>
                        <input type="date" name="start_date" class="form-control"
                               value="<?php echo attr($obj['start_date'] ?? date('Y-m-d')); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Estimated Duration (weeks)'); ?>:</label>
                        <input type="number" name="estimated_duration_weeks" class="form-control" min="1"
                               value="<?php echo attr($obj['estimated_duration_weeks'] ?? '8'); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Status'); ?>:</label>
                        <select name="status" class="form-control">
                            <option value="active" <?php echo ($obj && $obj['status'] == 'active') ? 'selected' : 'selected'; ?>><?php echo xlt('Active'); ?></option>
                            <option value="completed" <?php echo ($obj && $obj['status'] == 'completed') ? 'selected' : ''; ?>><?php echo xlt('Completed'); ?></option>
                            <option value="on_hold" <?php echo ($obj && $obj['status'] == 'on_hold') ? 'selected' : ''; ?>><?php echo xlt('On Hold'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?php echo xlt('Save Treatment Plan'); ?></button>
                <button type="button" class="btn btn-secondary" onclick="top.restoreSession();window.location.href='<?php echo $GLOBALS['form_exit_url']; ?>'"><?php echo xlt('Cancel'); ?></button>
            </div>
        </form>
    </div>
</body>
</html>
EOF

cat > "$FORMS_DIR/vietnamese_pt_treatment_plan/save.php" << 'EOF'
<?php
require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\VietnamesePT\PTTreatmentPlanService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$service = new PTTreatmentPlanService();

if ($_GET["mode"] == "new") {
    $data = [
        'patient_id' => $_POST['pid'],
        'plan_name' => $_POST['plan_name'],
        'diagnosis_en' => $_POST['diagnosis_en'] ?? '',
        'diagnosis_vi' => $_POST['diagnosis_vi'] ?? '',
        'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
        'estimated_duration_weeks' => $_POST['estimated_duration_weeks'] ?? 8,
        'status' => $_POST['status'] ?? 'active',
        'created_by' => $_POST['created_by']
    ];

    $result = $service->insert($data);
    if (!$result->hasErrors()) {
        $formid = $result->getData()[0]['id'] ?? null;
        if ($formid) {
            addForm($_POST['encounter'], "Vietnamese PT Treatment Plan", $formid, "vietnamese_pt_treatment_plan", $_POST['pid'], 1);
        }
    }
} elseif ($_GET["mode"] == "update") {
    $data = [
        'plan_name' => $_POST['plan_name'],
        'diagnosis_en' => $_POST['diagnosis_en'] ?? '',
        'diagnosis_vi' => $_POST['diagnosis_vi'] ?? '',
        'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
        'estimated_duration_weeks' => $_POST['estimated_duration_weeks'] ?? 8,
        'status' => $_POST['status'] ?? 'active'
    ];
    $service->update($_GET["id"], $data);
}

formHeader("Redirecting....");
formJump();
formFooter();
EOF

echo "✅ Treatment Plan Form created"

# ============================================
# Outcome Measures Form
# ============================================

echo "Creating Outcome Measures Form..."

mkdir -p "$FORMS_DIR/vietnamese_pt_outcome"

cat > "$FORMS_DIR/vietnamese_pt_outcome/info.txt" << 'EOF'
Vietnamese PT Outcome Measures
vietnamese_pt_outcome
1
1
Bilingual Vietnamese/English physiotherapy outcome measures form
EOF

cat > "$FORMS_DIR/vietnamese_pt_outcome/new.php" << 'EOF'
<?php
$viewmode = 'new';
require_once("common.php");
EOF

cat > "$FORMS_DIR/vietnamese_pt_outcome/common.php" << 'EOF'
<?php
require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if ($viewmode == 'update') {
    $obj = formFetch("pt_outcome_measures", $_GET["id"]);
} else {
    $obj = null;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Vietnamese PT Outcome Measures'); ?></title>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <style>
        .bilingual-group {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body class="body_top">
    <div class="container-fluid mt-3">
        <h2><?php echo xlt('Vietnamese PT Outcome Measures'); ?></h2>

        <form method="post" action="<?php echo $rootdir; ?>/forms/vietnamese_pt_outcome/save.php?mode=<?php echo attr_url($viewmode); ?>&id=<?php echo attr_url($_GET['id'] ?? 0); ?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
            <input type="hidden" name="pid" value="<?php echo attr($pid); ?>">
            <input type="hidden" name="encounter" value="<?php echo attr($encounter); ?>">
            <input type="hidden" name="therapist_id" value="<?php echo attr($_SESSION['authUserID']); ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo xlt('Measure Type'); ?>:</label>
                        <select name="measure_type" class="form-control" required>
                            <option value="ROM" <?php echo ($obj && $obj['measure_type'] == 'ROM') ? 'selected' : ''; ?>>Range of Motion (ROM)</option>
                            <option value="Strength" <?php echo ($obj && $obj['measure_type'] == 'Strength') ? 'selected' : ''; ?>>Strength</option>
                            <option value="Pain" <?php echo ($obj && $obj['measure_type'] == 'Pain') ? 'selected' : ''; ?>>Pain Level</option>
                            <option value="Function" <?php echo ($obj && $obj['measure_type'] == 'Function') ? 'selected' : ''; ?>>Functional Status</option>
                            <option value="Balance" <?php echo ($obj && $obj['measure_type'] == 'Balance') ? 'selected' : ''; ?>>Balance</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo xlt('Measurement Date'); ?>:</label>
                        <input type="date" name="measurement_date" class="form-control" required
                               value="<?php echo attr($obj['measurement_date'] ?? date('Y-m-d')); ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Baseline Value'); ?>:</label>
                        <input type="number" step="0.1" name="baseline_value" class="form-control"
                               value="<?php echo attr($obj['baseline_value'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Current Value'); ?>:</label>
                        <input type="number" step="0.1" name="current_value" class="form-control" required
                               value="<?php echo attr($obj['current_value'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo xlt('Target Value'); ?>:</label>
                        <input type="number" step="0.1" name="target_value" class="form-control"
                               value="<?php echo attr($obj['target_value'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><?php echo xlt('Unit of Measurement'); ?>:</label>
                <input type="text" name="unit" class="form-control" placeholder="degrees, kg, 0-10 scale, etc."
                       value="<?php echo attr($obj['unit'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label><?php echo xlt('Notes'); ?>:</label>
                <textarea name="notes" class="form-control" rows="3"><?php echo text($obj['notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?php echo xlt('Save Outcome Measure'); ?></button>
                <button type="button" class="btn btn-secondary" onclick="top.restoreSession();window.location.href='<?php echo $GLOBALS['form_exit_url']; ?>'"><?php echo xlt('Cancel'); ?></button>
            </div>
        </form>
    </div>
</body>
</html>
EOF

cat > "$FORMS_DIR/vietnamese_pt_outcome/save.php" << 'EOF'
<?php
require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\VietnamesePT\PTOutcomeMeasuresService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$service = new PTOutcomeMeasuresService();

if ($_GET["mode"] == "new") {
    $data = [
        'patient_id' => $_POST['pid'],
        'treatment_plan_id' => null,
        'measure_type' => $_POST['measure_type'],
        'measurement_date' => $_POST['measurement_date'],
        'baseline_value' => $_POST['baseline_value'] ?? null,
        'current_value' => $_POST['current_value'],
        'target_value' => $_POST['target_value'] ?? null,
        'unit' => $_POST['unit'] ?? '',
        'notes' => $_POST['notes'] ?? '',
        'therapist_id' => $_POST['therapist_id']
    ];

    $result = $service->insert($data);
    if (!$result->hasErrors()) {
        $formid = $result->getData()[0]['id'] ?? null;
        if ($formid) {
            addForm($_POST['encounter'], "Vietnamese PT Outcome Measures", $formid, "vietnamese_pt_outcome", $_POST['pid'], 1);
        }
    }
} elseif ($_GET["mode"] == "update") {
    $data = [
        'measure_type' => $_POST['measure_type'],
        'measurement_date' => $_POST['measurement_date'],
        'baseline_value' => $_POST['baseline_value'] ?? null,
        'current_value' => $_POST['current_value'],
        'target_value' => $_POST['target_value'] ?? null,
        'unit' => $_POST['unit'] ?? '',
        'notes' => $_POST['notes'] ?? ''
    ];
    $service->update($_GET["id"], $data);
}

formHeader("Redirecting....");
formJump();
formFooter();
EOF

echo "✅ Outcome Measures Form created"
echo ""
echo "✨ All forms generated successfully!"
echo ""
echo "Created forms:"
echo "  1. ✅ vietnamese_pt_assessment (Enhanced)"
echo "  2. ✅ vietnamese_pt_exercise"
echo "  3. ✅ vietnamese_pt_treatment_plan"
echo "  4. ✅ vietnamese_pt_outcome"
echo ""
echo "Next steps:"
echo "  1. Register forms in SQL (already in vietnamese_pt_routes_and_acl.sql)"
echo "  2. Clear OpenEMR cache"
echo "  3. Test forms in patient encounter"