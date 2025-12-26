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
        'therapist_id' => $_POST['therapist_id'] ?? $_SESSION['authUserID']
    ];

    $result = $service->insert($data);
    if (!$result->hasErrors()) {
        $formid = $result->getData()[0]['id'] ?? null;
        if ($formid) {
            addForm($_POST['encounter'], "Vietnamese PT Outcome Measures", $formid, "vietnamese_pt_outcome", $_POST['pid'], 1);
        }
    } else {
        error_log("Vietnamese PT Outcome Measures insert failed: " . print_r($result->getValidationMessages(), true));
        die("Error saving outcome measures: " . implode(", ", $result->getValidationMessages()));
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
    $result = $service->update($_GET["id"], $data);

    if ($result->hasErrors()) {
        error_log("Vietnamese PT Outcome Measures update failed: " . print_r($result->getValidationMessages(), true));
        die("Error updating outcome measures: " . implode(", ", $result->getValidationMessages()));
    }
}

formHeader("Redirecting....");
formJump();
formFooter();
