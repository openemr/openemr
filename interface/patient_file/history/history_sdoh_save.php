<?php

/**
 * SDOH (USCDI v3) SDOH list page (all assessments for a patient)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\Sdoh\HistorySdohService;

$pid = (int)($_GET['pid'] ?? 0);

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
    CsrfUtils::csrfNotVerified();
}
if (!AclMain::aclCheckCore('patients', 'med', '', ['write', 'addonly'])) {
    die(xlt("Not authorized"));
}

$rec_id = (int)($_POST['history_sdoh_id'] ?? 0);
$encounter = isset($_POST['encounter']) ? (int)$_POST['encounter'] : null;
$uid = $_SESSION['authUserID'] ?? null;

$clean = fn($k): string => trim($_POST[$k] ?? '');
$intOrNull = function ($k) {
    $v = trim($_POST[$k] ?? '');
    return ($v === '') ? null : (int)$v;
};
$dateOrNull = function ($k) {
    $v = trim($_POST[$k] ?? '');
    return ($v === '') ? null : $v;
};
$goals = HistorySdohService::buildGoals($_POST, $pid);
$goalsSave = ($goals !== []) ? json_encode($goals) : '';

// Build data array using new (prefixless) names
$data = [
    'assessment_date' => $dateOrNull('assessment_date'),
    'screening_tool' => $clean('screening_tool'),
    'assessor' => $clean('assessor'),

    // Domains
    'food_insecurity' => $clean('food_insecurity'),
    'food_insecurity_notes' => $clean('food_insecurity_notes'),
    'housing_instability' => $clean('housing_instability'),
    'housing_instability_notes' => $clean('housing_instability_notes'),
    'transportation_insecurity' => $clean('transportation_insecurity'),
    'transportation_insecurity_notes' => $clean('transportation_insecurity_notes'),
    'utilities_insecurity' => $clean('utilities_insecurity'),
    'utilities_insecurity_notes' => $clean('utilities_insecurity_notes'),
    'interpersonal_safety' => $clean('interpersonal_safety'),
    'interpersonal_safety_notes' => $clean('interpersonal_safety_notes'),
    'financial_strain' => $clean('financial_strain'),
    'financial_strain_notes' => $clean('financial_strain_notes'),
    'social_isolation' => $clean('social_isolation'),
    'social_isolation_notes' => $clean('social_isolation_notes'),
    'childcare_needs' => $clean('childcare_needs'),
    'childcare_needs_notes' => $clean('childcare_needs_notes'),
    'digital_access' => $clean('digital_access'),
    'digital_access_notes' => $clean('digital_access_notes'),

    // Context
    'employment_status' => $clean('employment_status'),
    'education_level' => $clean('education_level'),
    'caregiver_status' => $clean('caregiver_status'),
    'veteran_status' => $clean('veteran_status'),

    // Pregnancy
    'pregnancy_status' => $clean('pregnancy_status'),
    'pregnancy_edd' => $dateOrNull('pregnancy_edd'),
    'pregnancy_gravida' => $intOrNull('pregnancy_gravida'),
    'pregnancy_para' => $intOrNull('pregnancy_para'),
    'postpartum_status' => $clean('postpartum_status'),
    'postpartum_end' => $dateOrNull('postpartum_end'),

    // Care plan
    'goals' => $goalsSave,
    'interventions' => $_POST['interventions'] ?? ''
];

if ($rec_id) {
    // UPDATE existing
    $setSql = [];
    $params = [];
    foreach ($data as $col => $val) {
        $setSql[] = "`$col` = ?";
        $params[] = $val;
    }
    $params[] = $uid;  // updated_by
    $params[] = $rec_id;
    $params[] = $pid;

    $sql = "UPDATE form_history_sdoh
            SET " . implode(", ", $setSql) . ", updated_by = ?, updated_at = NOW()
            WHERE id = ? AND pid = ?";
    sqlStatement($sql, $params);
    $id = $rec_id;
} else {
    // INSERT new
    $cols = "`pid`" . ($encounter ? ",`encounter`" : "");
    $qs = "?" . ($encounter ? ",?" : "");
    $params = [$pid];
    if ($encounter) {
        $params[] = $encounter;
    }

    foreach ($data as $col => $val) {
        $cols .= ",`$col`";
        $qs .= ",?";
        $params[] = $val;
    }
    $cols .= ",`created_by`,`updated_by`";
    $qs .= ",?,?";
    $params[] = $uid;
    $params[] = $uid;

    $id = sqlInsert("INSERT INTO form_history_sdoh ($cols) VALUES ($qs)", $params);
}

// Return to demographics (or wherever you prefer)
header("Location: " . $GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh_widget.php?pid=" . urlencode($pid));
exit;
