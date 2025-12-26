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
        'created_by' => $_POST['created_by'] ?? $_SESSION['authUserID']
    ];

    $result = $service->insert($data);
    if (!$result->hasErrors()) {
        $formid = $result->getData()[0]['id'] ?? null;
        if ($formid) {
            addForm($_POST['encounter'], "Vietnamese PT Treatment Plan", $formid, "vietnamese_pt_treatment_plan", $_POST['pid'], 1);
        }
    } else {
        error_log("Vietnamese PT Treatment Plan insert failed: " . print_r($result->getValidationMessages(), true));
        die("Error saving treatment plan: " . implode(", ", $result->getValidationMessages()));
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
    $result = $service->update($_GET["id"], $data);

    if ($result->hasErrors()) {
        error_log("Vietnamese PT Treatment Plan update failed: " . print_r($result->getValidationMessages(), true));
        die("Error updating treatment plan: " . implode(", ", $result->getValidationMessages()));
    }
}

formHeader("Redirecting....");
formJump();
formFooter();
