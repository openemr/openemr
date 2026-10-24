<?php

/**
 * dictation save.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\EncounterFormAccess;
use OpenEMR\Common\Session\EncounterSessionUtil;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

// Hoist legacy `globals.php` locals so PHPStan can see them (#11792 Phase 5).
$srcdir = OEGlobalsBag::getInstance()->getSrcDir();
$pid = PatientSessionUtil::getPid();
$encounter = EncounterSessionUtil::getEncounter();
$userauthorized = PatientSessionUtil::getUserAuthorized();

$session = SessionWrapperFactory::getInstance()->getActiveSession();

CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

$formIdInput = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$formId = is_int($formIdInput) && $formIdInput >= 0 ? $formIdInput : 0;

EncounterFormAccess::assertFormBelongsToSessionPatient($formId, 'dictation');

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_dictation", $_POST, $formId, $userauthorized);
    addForm($encounter, "Speech Dictation", $newid, "dictation", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    EncounterFormAccess::requirePositiveFormId($formId, 'dictation');
    sqlStatement("update form_dictation set pid = ?,groupname=?,user=?,authorized=?,activity=1, date = NOW(), dictation=?, additional_notes=? where id=?", [$session->get('pid'),$session->get('authProvider'),$session->get('authUser'),$userauthorized,$_POST["dictation"],$_POST["additional_notes"],$formId]);
}

formHeader("Redirecting....");
formJump();
formFooter();
