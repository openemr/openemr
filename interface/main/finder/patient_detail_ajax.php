<?php

/**
 * patient_detail_ajax.php
 *
 * The details shown when a Patient Finder row is expanded: the Patient Finder
 * Detail layout rendered for one patient. Returns JSON {"html": "..."}; 404 when
 * the patient does not exist or the finder hides them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once(__DIR__ . "/../../globals.php");
require_once(\OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . "/options.inc.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\PatientFinder\PatientFinderDetailService;

// The same gate as the finder and its search endpoint: patients/demo.
if (!AclMain::aclCheckCore('patients', 'demo')) {
    AccessDeniedHelper::deny('patient_detail_ajax.php: patients/demo required');
}

// No CSRF check, for the same reasons as dynamic_finder_ajax.php: the session cookie is
// SameSite=Strict, and this script only reads.

header('Content-Type: application/json');

// getInt() would throw on a value that is not a number; anything that is not a positive
// integer is simply not found.
$rawPid = CurrentRequest::get()->query->getString('pid');
$pid = ctype_digit($rawPid) ? intval($rawPid) : 0;
$service = new PatientFinderDetailService(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
$values = $service->values($pid);
if ($values === null) {
    http_response_code(404);
    echo json_encode(['html' => '']);
    exit;
}

ob_start();
display_layout_tabs_data(PatientFinderDetailService::LAYOUT_ID, $values);
$html = (string) ob_get_clean();

// Encoding with options for escaping a special chars - JSON_HEX_TAG (<)(>), JSON_HEX_AMP(&), JSON_HEX_APOS('), JSON_HEX_QUOT(").
echo json_encode(['html' => $html], 15);
