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

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;

$isPortal = CoreFormToPortalUtility::isPatientPortalSession($_GET);
if ($isPortal) {
    $ignoreAuth_onsite_portal = true;
}
$patientPortalOther = CoreFormToPortalUtility::isPatientPortalOther($_GET);

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$formid = $_GET["form_id"] ?? 0;
$mode = $_GET["mode"] ?? '';
$form_name = $_POST['form_name'] ?? "";
$q_json = $_POST['questionnaire'] ?? '';
$qr_json = $_POST['questionnaire_response'] ?? '';
$lform_response = $_POST['lform_response'] ?? '';
$lform = $_POST['lform'] ?? '';
$qid = null;
$qrid = null;
// so form save will work
unset($_POST['select_item']);
// security
if ($isPortal && $mode == 'update' && !empty($formid)) {
    CoreFormToPortalUtility::confirmFormBootstrapPatient($isPortal, $formid, 'questionnaire_assessments', $_SESSION['pid']);
}
if ($mode !== 'new' && $mode !== 'new_repository_form') {
    $service = new QuestionnaireService();
    $responseService = new QuestionnaireResponseService();
    try {
        if (!empty($_POST['response_meta'])) {
            $qr_json = $responseService->insertResponseMetaData($qr_json, $_POST['response_meta']);
        }
        $qrsaveid = $responseService->saveQuestionnaireResponse($qr_json, $pid, $encounter, null, null, $q_json, null, $lform_response, true);
        $_POST['response_id'] = $qrsaveid['response_id'] ?? null;
        if (empty($_POST['response_meta']) || $qrsaveid['new']) {
            $saved = $responseService->fetchQuestionnaireResponseById($qrsaveid['id'], $qrsaveid['response_id']);
            $_POST['response_meta'] = $responseService->extractResponseMetaData($saved['questionnaire_response'], true);
            $_POST['response_id'] = $saved['response_id'];
        }
    } catch (Exception $e) {
        // allow exception to pass onward with echoed notification to user.
        // The form has a backup copy of response and will save with the form.
        echo("<p>" . xlt("Questionnaire Response save failed because") . '<br />' . text($e->getMessage()) . '<br /><h3>' . xlt("Will attempt to save using backed up answers.") . "</h3></p>");
    }
}
// register new form
if (isset($_POST['save_registry'])) {
    unset($_POST['save_registry']);
    $check = sqlQuery("Select id From registry Where `directory` = ? And `name` = ? And `form_foreign_id` > 0", array("questionnaire_assessments", $form_name));
    if (empty($check['id'])) {
        $service = new QuestionnaireService();
        try {
            $form_foreign_id = $service->saveQuestionnaireResource($q_json, $form_name, null, null, $lform, 'encounter');
        } catch (Exception $e) {
            die(xlt("New Questionnaire insert failed") . '<br />' . text($e->getMessage()));
        }
        $rtn = sqlInsert("Insert Into `registry` Set
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
        $msg = "<br /><br /><div><h3 style='color: red;font-weight: normal;'>" . xlt("Error. Form already registered! Redirecting back to form.") . "</h3></div>";
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
    // just to be sure
    CoreFormToPortalUtility::confirmFormBootstrapPatient($isPortal, $formid, 'questionnaire_assessments', $_SESSION['pid']);
    $success = formUpdate("form_questionnaire_assessments", $_POST, $formid, $userauthorized);
}

if ($isPortal || $patientPortalOther) {
    echo CoreFormToPortalUtility::formQuestionnairePortalPostSave($formid, $qid, $qrid, $encounter);
} else {
    formHeader("Redirecting....");
    formJump();
    formFooter();
}
