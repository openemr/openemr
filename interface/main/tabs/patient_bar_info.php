<?php

/**
 * The line under the patient's name in the patient bar, as JSON: {"parts": ["PID 43", "Male", …]}.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Services\PatientService;
use OpenEMR\Tabs\PatientBarInfoService;

// The same gate as the Patient Finder, which shows these fields for any patient.
// Read-only, so no CSRF token (as in dynamic_finder_ajax.php).
if (!AclMain::aclCheckCore('patients', 'demo')) {
    AccessDeniedHelper::deny('patient_bar_info.php: patients/demo required');
}

$rawPid = CurrentRequest::get()->query->getString('pid');
$info = ctype_digit($rawPid)
    ? (new PatientBarInfoService(new PatientService(), ServiceContainer::getClock()))->forPid((int) $rawPid)
    : null;
header('Content-Type: application/json');
if ($info === null) {
    http_response_code(404);
}
echo json_encode(['parts' => $info?->parts() ?? []], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
