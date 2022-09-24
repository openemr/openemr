<?php

/**
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/*
 * Save or create an encounter questionnaire from lookup.
 * If admin want to register a new form then one is added to forms registry.
 * If already exists then inform user and redirect back to New Questionnaire form.
*/
require_once(__DIR__ . "/../../../src/Common/Forms/CoreFormToPortalUtility.php");
use OpenEMR\Common\Forms\CoreFormToPortalUtility;

$isPortal = CoreFormToPortalUtility::isPatientPortalSession($_GET);
if ($isPortal) {
    $ignoreAuth_onsite_portal = true;
}
$patientPortalOther = CoreFormToPortalUtility::isPatientPortalOther($_GET);

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$formid = $_GET["form_id"] ?? 0;
$form_name = $_POST['form_name'] ?? "";
$q_json = $_POST['questionnaire'] ?? '';
$qr_json = $_POST['questionnaire_response'] ?? '';
$lform_response = $_POST['lform_response'] ?? '';
$lform = $_POST['lform'] ?? '';
$qid = null;
$qrid = null;
// so form save will work
unset($_POST['select_item']);

if ($isPortal) {
    $service = new QuestionnaireService();
    $responseService = new QuestionnaireResponseService();
    $qr = json_decode($qr_json, true);
    $q = json_decode($q_json, true);
    $qrid = $qr['id'] ?? null;
    $qid = $q['id'] ?? null;
    // I'm still deciding if I want to store in repositories or leave encounter questionnaires as sole resource.
    //$qsaveid = $service->saveQuestionnaireResource($q_json, $form_name, null, $qid, null);
    //$qrsaveid = $responseService->saveQuestionnaireResponse($qr_json, $pid, $encounter, $qrid, null, $q, null, $lform_response,true);
}
// register new form
if (isset($_POST['save_registry'])) {
    unset($_POST['save_registry']);
    $check = sqlQuery("select id from registry where `directory` = ? And `name` = ? And `form_foreign_id` > 0", array("questionnaire_assessments", $form_name));
    if (empty($check['id'])) {
        $service = new QuestionnaireService();
        $form_foreign_id = $service->saveQuestionnaireResource($q_json, $form_name, null, null, $lform, 'encounter');
        $rtn = sqlInsert("insert into `registry` set
        `name`=?,
        `state`=?,
        `directory`=?,
        `sql_run`=?,
        `unpackaged`=?,
        `category`=?,
        `date`= NOW(),
        `form_foreign_id`=?
    ", array($form_name, 1, "questionnaire_assessments", 1, 1, "Questionnaires", $form_foreign_id));
    } else {
        /* TBD TODO do an update form and registry or error back to user for duplicate */
        $msg = "<br /><br /><div><h3 style='color: red;font-weight: normal;'>" . xlt("Error. Form already registered! Redirecting back to form in a couple seconds.") . "</h3></div>";
        $msg .= "<script>setTimeout(() => {history.back();}, 4000)</script>";
        die($msg);
    }
    formHeader("Redirecting....");
    formJump();
    formFooter();
}

if (empty($formid)) {
    $newid = formSubmit("form_questionnaire_assessments", $_POST, '', $userauthorized);
    addForm($encounter, $form_name, $newid, "questionnaire_assessments", $pid, $userauthorized);
    $formid = $newid;
} elseif (!empty($formid)) {
    $success = formUpdate("form_questionnaire_assessments", $_POST, $formid, $userauthorized);
}

if ($isPortal || $patientPortalOther) {
    echo CoreFormToPortalUtility::formQuestionnairePortalPostSave($formid, $qid, $qrid, $encounter);
} else {
    formHeader("Redirecting....");
    formJump();
    formFooter();
}
