<?php

/**
 * Vietnamese PT Assessment Form - save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2025 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\VietnamesePT\PTAssessmentService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$service = new PTAssessmentService();

if ($_GET["mode"] == "new") {
    $data = [
        'patient_id' => $_POST['pid'],
        'encounter_id' => $_POST['encounter'],
        'assessment_date' => date('Y-m-d H:i:s'),
        'therapist_id' => $_POST['therapist_id'],
        'chief_complaint_vi' => $_POST['chief_complaint_vi'] ?? '',
        'chief_complaint_en' => $_POST['chief_complaint_en'] ?? '',
        'pain_level' => $_POST['pain_level'] ?? null,
        'pain_location_vi' => $_POST['pain_location_vi'] ?? '',
        'pain_location_en' => $_POST['pain_location_en'] ?? '',
        'pain_description_vi' => $_POST['pain_description_vi'] ?? '',
        'pain_description_en' => $_POST['pain_description_en'] ?? '',
        'functional_goals_vi' => $_POST['functional_goals_vi'] ?? '',
        'functional_goals_en' => $_POST['functional_goals_en'] ?? '',
        'treatment_plan_vi' => $_POST['treatment_plan_vi'] ?? '',
        'treatment_plan_en' => $_POST['treatment_plan_en'] ?? '',
        'language_preference' => $_POST['language_preference'] ?? 'both',
        'status' => $_POST['status'] ?? 'completed'
    ];

    $result = $service->insert($data);

    if (!$result->hasErrors()) {
        $formid = $result->getData()[0]['id'] ?? null;

        if ($formid) {
            addForm(
                $data['encounter_id'],
                "Vietnamese PT Assessment",
                $formid,
                "vietnamese_pt_assessment",
                $data['patient_id'],
                1
            );
        }
    }
} elseif ($_GET["mode"] == "update") {
    $data = [
        'chief_complaint_vi' => $_POST['chief_complaint_vi'] ?? '',
        'chief_complaint_en' => $_POST['chief_complaint_en'] ?? '',
        'pain_level' => $_POST['pain_level'] ?? null,
        'pain_location_vi' => $_POST['pain_location_vi'] ?? '',
        'pain_location_en' => $_POST['pain_location_en'] ?? '',
        'pain_description_vi' => $_POST['pain_description_vi'] ?? '',
        'pain_description_en' => $_POST['pain_description_en'] ?? '',
        'functional_goals_vi' => $_POST['functional_goals_vi'] ?? '',
        'functional_goals_en' => $_POST['functional_goals_en'] ?? '',
        'treatment_plan_vi' => $_POST['treatment_plan_vi'] ?? '',
        'treatment_plan_en' => $_POST['treatment_plan_en'] ?? '',
        'language_preference' => $_POST['language_preference'] ?? 'both',
        'status' => $_POST['status'] ?? 'completed'
    ];

    $service->update($_GET["id"], $data);
}

formHeader("Redirecting....");
formJump();
formFooter();