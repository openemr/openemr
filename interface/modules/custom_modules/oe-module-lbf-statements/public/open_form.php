<?php

/**
 * Set the session patient/encounter, then print or open the encounter view.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\EncounterSessionUtil;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Core\OEGlobalsBag;

require_once dirname(__DIR__, 4) . "/globals.php";

CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

if (!AclMain::aclCheckCore('encounters', 'notes')) {
    http_response_code(401);
    echo xlt('Access Denied');
    exit;
}

$request = CurrentRequest::get();
$pid = Values::asInt($request->query->getString('pid'));
$instanceId = Values::asInt($request->query->getString('instance_id'));
$formId = $request->query->getString('form_id');
$dest = $request->query->getString('dest', 'form');
if ($dest !== 'print') {
    $dest = 'form';
}

if ($pid <= 0 || $instanceId <= 0 || !Identifiers::isFieldId($formId)) {
    http_response_code(400);
    echo xlt('Select a patient and form instance.');
    exit;
}

$reader = new LbfReader();
$row = $reader->instanceRow($instanceId, $formId);
if ($row === null || Values::rowInt($row, 'pid') !== $pid) {
    http_response_code(404);
    echo xlt('Form instance not found.');
    exit;
}

$encounter = Values::rowInt($row, 'encounter');
if ($encounter <= 0 || !$reader->encounterOwnedBy($pid, $encounter)) {
    http_response_code(404);
    echo xlt('Encounter does not belong to this patient.');
    exit;
}

PatientSessionUtil::setPid($pid);
EncounterSessionUtil::setEncounter((string) $encounter);

$webroot = OEGlobalsBag::getInstance()->getWebRoot();

if ($dest === 'print') {
    $url = $webroot . '/interface/forms/LBF/printable.php?formname=' . rawurlencode($formId)
        . '&formid=' . $instanceId
        . '&patientid=' . $pid
        . '&visitid=' . $encounter;
    header('Location: ' . $url, true, 302);
    exit;
}

$url = $webroot . '/interface/patient_file/encounter/encounter_top.php?set_encounter='
    . rawurlencode((string) $encounter)
    . '&set_pid=' . rawurlencode((string) $pid);
header('Location: ' . $url, true, 302);
exit;
