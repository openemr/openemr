<?php

/**
 * easipro_util.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Shiqiang Tao <StrongTSQ@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Shiqiang Tao <StrongTSQ@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Easipro\Easipro;

// Will start the (patient) portal OpenEMR session/cookie
//  (in case the request is from the patient portal; note it will get destroyed if request is not from patient portal).
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
$session = SessionWrapperFactory::getInstance()->getActiveSession();

$portalFlag = $session->get('patient_portal_onsite_two');
$portalPid = PatientSessionUtil::getPid();
if (
    $portalPid > 0
    && is_scalar($portalFlag) && (int) $portalFlag > 0
) {
    // request is from patient portal
    $pid = $portalPid;
    $ignoreAuth = true;
} else {
    // request is from openemr core
    SessionWrapperFactory::getInstance()->destroyPortalSession();
    $ignoreAuth = false;
    $session = SessionWrapperFactory::getInstance()->getCoreSession();
    $pid = PatientSessionUtil::getPid();
}

require_once(__DIR__ . "/../../interface/globals.php");


CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

// process requested function
if ($_POST['function'] == 'request_assessment') {
    // Request assessment -- writes a new pro_assessments row for the patient.
    // Session pid drives the write; core users need patients/med write access,
    // portal callers ride the existing $ignoreAuth path.
    if (!$ignoreAuth && !AclMain::aclCheckCore('patients', 'med', '', ['write', 'addonly'])) {
        AccessDeniedHelper::deny("ACL check failed for patients/med: Easipro request_assessment");
    }
    if ($pid <= 0) {
        AccessDeniedHelper::deny("Easipro request_assessment missing session pid");
    }
    $expiration = date_format(date_create_from_format('n/j/Y g:i:s A', $_POST['expiration']), 'Y-m-d H:i:s');
    Easipro::requestAssessment($pid, $session->get('authUserID'), $_POST['formOID'], $_POST['formName'], $expiration, $_POST['assessmentOID'], $_POST['status']);
} elseif ($_POST['function'] == 'start_assessment') {
    // Start assessment
    header('Content-Type: application/json');
    echo Easipro::startAssessment($_POST['assessmentOID']);
} elseif ($_POST['function'] == 'select_response') {
    // Render screen during assessment
    header('Content-Type: application/json');
    echo Easipro::selectResponse($_POST['assessmentOID'], $_POST['ItemResponseOID'], $_POST['Response']);
} elseif ($_POST['function'] == 'collect_results') {
    // Collect results after completing assessment
    header('Content-Type: application/json');
    echo Easipro::collectResults($_POST['assessmentOID']);
} elseif ($_POST['function'] == 'record_result') {
    // Record result of assessment -- UPDATE pro_assessments scoped to pid+assessment_oid.
    // Requires write access; addonly is insert-only and does not authorize the UPDATE this branch performs.
    if (!$ignoreAuth && !AclMain::aclCheckCore('patients', 'med', '', 'write')) {
        AccessDeniedHelper::deny("ACL check failed for patients/med: Easipro record_result");
    }
    if ($pid <= 0) {
        AccessDeniedHelper::deny("Easipro record_result missing session pid");
    }
    // Confirm the target assessment row belongs to the session pid before
    // updating. Equivalent to checking that the UPDATE would affect at least
    // one row (its WHERE clause is patient_id+assessment_oid), but done as a
    // pre-flight so a mismatched assessment_oid short-circuits cleanly.
    $assessmentOid = CurrentRequest::get()->request->getString('assessmentOID');
    $ownerPid = QueryUtils::fetchSingleValue(
        "SELECT patient_id FROM pro_assessments WHERE assessment_oid = ? AND patient_id = ?",
        'patient_id',
        [$assessmentOid, $pid]
    );
    if ($ownerPid === null) {
        AccessDeniedHelper::deny("Easipro record_result assessment_oid does not belong to session pid");
    }
    Easipro::recordResult($pid, $_POST['score'], $_POST['assessmentOID'], $_POST['stdErr']);
} elseif ($_POST['function'] == 'list_forms') {
    // Provide list of forms
    header('Content-Type: application/json');
    echo Easipro::listForms();
} elseif ($_POST['function'] == 'order_form') {
    // Order form
    header('Content-Type: application/json');
    echo Easipro::orderForm($_POST['formOID']);
}
