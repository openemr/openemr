<?php

/**
 * Vietnamese PT Exercise Prescription - save.php
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
use OpenEMR\Services\VietnamesePT\PTExercisePrescriptionService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$service = new PTExercisePrescriptionService();

if ($_GET["mode"] == "new") {
    $data = [
        'patient_id' => $_POST['pid'],
        'encounter_id' => $_POST['encounter'],
        'exercise_name' => $_POST['exercise_name'],
        'exercise_name_vi' => $_POST['exercise_name_vi'],
        'description' => $_POST['description'] ?? '',
        'description_vi' => $_POST['description_vi'] ?? '',
        'sets_prescribed' => $_POST['sets_prescribed'],
        'reps_prescribed' => $_POST['reps_prescribed'] ?? null,
        'duration_minutes' => $_POST['duration_minutes'] ?? null,
        'frequency_per_week' => $_POST['frequency_per_week'],
        'intensity_level' => $_POST['intensity_level'] ?? 'moderate',
        'instructions' => $_POST['instructions'] ?? '',
        'instructions_vi' => $_POST['instructions_vi'] ?? '',
        'equipment_needed' => $_POST['equipment_needed'] ?? '',
        'precautions' => $_POST['precautions'] ?? '',
        'precautions_vi' => $_POST['precautions_vi'] ?? '',
        'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
        'end_date' => $_POST['end_date'] ?? null,
        'prescribed_by' => $_POST['prescribed_by'] ?? $_SESSION['authUserID']
    ];

    $result = $service->insert($data);

    if (!$result->hasErrors()) {
        $formid = $result->getData()[0]['id'] ?? null;

        if ($formid) {
            addForm(
                $data['encounter_id'],
                "Vietnamese PT Exercise Prescription",
                $formid,
                "vietnamese_pt_exercise",
                $data['patient_id'],
                1
            );
        }
    } else {
        error_log("Vietnamese PT Exercise insert failed: " . print_r($result->getValidationMessages(), true));
        die("Error saving exercise prescription: " . implode(", ", $result->getValidationMessages()));
    }
} elseif ($_GET["mode"] == "update") {
    $data = [
        'exercise_name' => $_POST['exercise_name'],
        'exercise_name_vi' => $_POST['exercise_name_vi'],
        'description' => $_POST['description'] ?? '',
        'description_vi' => $_POST['description_vi'] ?? '',
        'sets_prescribed' => $_POST['sets_prescribed'],
        'reps_prescribed' => $_POST['reps_prescribed'] ?? null,
        'duration_minutes' => $_POST['duration_minutes'] ?? null,
        'frequency_per_week' => $_POST['frequency_per_week'],
        'intensity_level' => $_POST['intensity_level'] ?? 'moderate',
        'instructions' => $_POST['instructions'] ?? '',
        'instructions_vi' => $_POST['instructions_vi'] ?? '',
        'equipment_needed' => $_POST['equipment_needed'] ?? '',
        'precautions' => $_POST['precautions'] ?? '',
        'precautions_vi' => $_POST['precautions_vi'] ?? '',
        'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
        'end_date' => $_POST['end_date'] ?? null
    ];

    $result = $service->update($_GET["id"], $data);

    if ($result->hasErrors()) {
        error_log("Vietnamese PT Exercise update failed: " . print_r($result->getValidationMessages(), true));
        die("Error updating exercise prescription: " . implode(", ", $result->getValidationMessages()));
    }
}

formHeader("Redirecting....");
formJump();
formFooter();