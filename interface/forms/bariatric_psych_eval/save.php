<?php

/**
 * Bariatric Psych Eval save.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Barbara Rix <admin@starbirdrisingwellness.com>
 * @copyright Copyright (c) 2026 Barbara Rix <admin@starbirdrisingwellness.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\EncounterFormAccess;
use OpenEMR\Common\Session\EncounterSessionUtil;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

$srcdir = OEGlobalsBag::getInstance()->getSrcDir();
$pid = PatientSessionUtil::getPid();
$encounter = EncounterSessionUtil::getEncounter();
$userauthorized = PatientSessionUtil::getUserAuthorized();

require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");
require_once(__DIR__ . "/bariatric_psych_eval.inc.php");

$session = SessionWrapperFactory::getInstance()->getActiveSession();

CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

$formIdInput = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$formId = is_int($formIdInput) && $formIdInput >= 0 ? $formIdInput : 0;

EncounterFormAccess::assertFormBelongsToSessionPatient($formId, 'bariatric_psych_eval');

if (!$encounter) {
    $encounter = date("Ymd");
}

// Flatten the section config into a plain field-name list, rather than maintaining a
// second hand-written list that could drift out of sync with bariatric_psych_eval.inc.php.
$scoreFields = [];
foreach ($str_sections as $fields) {
    foreach (array_keys($fields) as $fieldName) {
        $scoreFields[] = $fieldName;
    }
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_bariatric_psych_eval", $_POST, $formId, $userauthorized);
    addForm($encounter, "Bariatric Psych Eval Form", $newid, "bariatric_psych_eval", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    EncounterFormAccess::requirePositiveFormId($formId, 'bariatric_psych_eval');
    $setClause = implode(",\n            ", array_map(static fn($f) => "$f=?", $scoreFields));
    $params = [
        $session->get('pid'),
        $session->get('authProvider'),
        $session->get('authUser'),
        $userauthorized,
    ];
    foreach ($scoreFields as $f) {
        $params[] = $_POST[$f] ?? '';
    }
    $params[] = $formId;
    sqlStatement(
        "update form_bariatric_psych_eval set pid = ?,
            groupname = ?,
            user = ?,
            authorized = ?,
            activity = 1,
            $setClause
            where id=? ",
        $params
    );
}

formHeader("Redirecting....");
formJump();
formFooter();
