<?php

/**
 * sets pid
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\PatientService;
use OpenEMR\Tabs\PatientBarInfoEndpoint;
use OpenEMR\Tabs\PatientBarInfoService;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$request = CurrentRequest::get();

CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

$setPid = $request->query->getString('set_pid');
$sessionPid = $session->get('pid');
$sessionPidText = is_int($sessionPid) || is_string($sessionPid) ? (string) $sessionPid : '';
if ($setPid !== '' && $setPid !== '0' && $setPid !== $sessionPidText) {
    // The only live caller of this branch is the Messages Center document-attach
    // flow in interface/main/messages/templates/linked_documents.php, which
    // requires patients/docs. Widen if future callers legitimately need it.
    if (!AclMain::aclCheckCore('patients', 'docs')) {
        http_response_code(403);
        exit;
    }
    setpid($setPid);
}

$mode = $request->request->getString('mode');

// Session-key read used by the tab-bar getSessionValue() helper; returns the
// user's own session state (pid/encounter). CSRF + session auth are sufficient.
if ($mode === 'session_key') {
    $key = $request->request->getString('key');
    $allowedKeys = ['pid', 'encounter'];

    if (in_array($key, $allowedKeys, true)) {
        $current = $session->get($key) ?? ($key === 'pid' ? ($pid ?? 0) : 0);
        echo text(json_encode($current, JSON_THROW_ON_ERROR));
    }
}

// The line under the patient's name in the tab bar's patient bar (PID, sex, DOB, age), as JSON.
// The same gate as the Patient Finder, which shows these fields for any patient.
if ($mode === 'patient_bar_info') {
    if (!AclMain::aclCheckCore('patients', 'demo')) {
        AccessDeniedHelper::deny('set_pt.php patient_bar_info: patients/demo required');
    }
    (new PatientBarInfoEndpoint(new PatientBarInfoService(new PatientService(), ServiceContainer::getClock())))
        ->respond($request->request->getString('pid'))
        ->send();
}
