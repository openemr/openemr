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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\SDOH\HistorySdohService;
use OpenEMR\Common\Logging\SystemLogger;

$pid = (int)$_SESSION['pid'];

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
    CsrfUtils::csrfNotVerified();
}
if (!AclMain::aclCheckCore('patients', 'med', '', ['write', 'addonly'])) {
    die(xlt("Not authorized"));
}

if (empty($pid)) {
    // we should never hit this if the app is used correctly.
    die(xlt("No patient selected"));
}

$instrument_score = isset($_POST['instrument_score']) ? (int)$_POST['instrument_score'] : null;
$declined_flag = !empty($_POST['declined_flag']) ? 1 : 0;
$logger = new SystemLogger();

// --- Functional sub-observations -> JSON (disability_scale)
$dscale = $_POST['dscale'] ?? [];
// Normalize into {"walk_climb":{"code":"LA33-6","notes":"..."} , ...}
$scaleOut = [];
foreach (['walk_climb','seeing','hearing','cognitive','dressing_bathing','errands'] as $k) {
    $row = $dscale[$k] ?? [];
    $code = trim($row['code'] ?? '');
    $notes = trim($row['notes'] ?? '');
    if ($code !== '' || $notes !== '') {
        $scaleOut[$k] = ['code' => $code, 'notes' => $notes];
    }
}
$disability_scale_json = json_encode($scaleOut, JSON_UNESCAPED_UNICODE);

$data['instrument_score'] = $instrument_score;
$data['declined_flag'] = $declined_flag;

// Optional convenience metric for reporting dashboards
$data['positive_domain_count'] = HistorySdohService::countPositiveDomains($_POST);

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

$interventions = HistorySdohService::buildInterventions($_POST, $pid, [
    'include_category' => true,
    'include_measure' => true,
    'include_due' => true]);
$interventionsSave = ($interventions !== []) ? json_encode($interventions) : '';
// --- Disability Status (overall)
$disability_status       = trim($_POST['disability_status'] ?? '');
$disability_status_notes = trim($_POST['disability_status_notes'] ?? '');

// Build data array for save
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
    'postpartum_status' => $clean('postpartum_status'),
    'postpartum_end' => $dateOrNull('postpartum_end'),
    'pregnancy_intent' => $clean('pregnancy_intent'),

    // Care plan
    'goals' => $goalsSave,
    'interventions' => $interventionsSave,
    // Disability
    'disability_status' => $clean('disability_status'),
    'disability_status_notes' => $clean('disability_status_notes'),
    'disability_scale' => $disability_scale_json,
    // Hunger Vital Sign
    'hunger_q1' => $clean('hunger_q1'),
    'hunger_q2' => $clean('hunger_q2'),
    'hunger_score' => $clean('hunger_score'),
    'pid' => $pid,
    'updated_by' => $uid,
];

try {
    $sdohService = new HistorySdohService();
    if ($rec_id) {
        $data['updated_by'] = $uid;
        $result = $sdohService->update($rec_id, $data);
        if (!$result->isValid()) {
            $logger->errorLogCaller("Failed to insert sdoh record", ['validationErrors' => $result->getValidationMessages(), 'internalErrors' => $result->getInternalErrors()]);
        }
        $id = $rec_id;
    } else {
        // we only set encounter on new records.
        $data['encounter'] = $encounter ?? null;
        $data['created_by'] = $uid;
        $result = $sdohService->insert($data);
        if (!$result->isValid()) {
            $logger->errorLogCaller("Failed to insert sdoh record", ['validationErrors' => $result->getValidationMessages(), 'internalErrors' => $result->getInternalErrors()]);
            throw new Exception("Failed to insert sdoh record.");
        } else {
            $id = $result->getFirstDataResult()['id'];
        }
    }
    // TODO: not sure we need to do this here but it doesn't hurt.
    UuidRegistry::createMissingUuidsForTables(['form_history_sdoh']);
    // TODO: there doesn't appear to be any error handling if the save fails... this seems pretty important.
    // Return to demographics (or wherever you prefer)
    // Redirect to health concerns selection page
    $redirectUrl = $GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh_health_concerns.php"
        . "?pid=" . urlencode((string) $pid)
        . "&sdoh_id=" . urlencode((string) $id);
    header("Location: $redirectUrl");
} catch (Exception $e) {
    $logger->errorLogCaller("Exception saving sdoh record: " . $e->getMessage());
    die(xlt("Error saving SDOH record."));
}
//header("Location: " . $GLOBALS['webroot'] . "/interface/patient_file/history/history_sdoh_widget.php?pid=" . urlencode((string) $pid));
exit;
