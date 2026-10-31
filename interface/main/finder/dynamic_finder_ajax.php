<?php

/**
 * dynamic_finder_ajax.php
 *
 * Server-side (DataTables) data source for the Patient Finder. This is now a
 * thin entry point: it enforces authentication/authorisation and delegates the
 * request parsing, query building and response assembly to
 * {@see \OpenEMR\Services\PatientFinder\PatientFinderQueryService}. The service
 * dispatches the module extension events (column registration, custom filter,
 * row enrichment). The wire contract (DataTables request params and the
 * sEcho/iTotalRecords/iTotalDisplayRecords/aaData response) is unchanged.
 *
 * Sponsored by David Eschelbacher, MD
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2012 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019, 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once(\OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . "/options.inc.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\PatientFinder\PatientFinderQueryService;
use Symfony\Component\HttpFoundation\Request;

// Align the permission check with the Patient Finder page (`patients/demo`
// per `standard.json`). The AJAX endpoint reads the same `patient_data`
// table as the page and should carry the same role gate.
if (!AclMain::aclCheckCore('patients', 'demo')) {
    AccessDeniedHelper::deny('dynamic_finder_ajax.php: patients/demo required');
}

// Not checking csrf since it breaks when opening up a patient in a new frame.
//  Also note that csrf checking is not needed in this script because of following 2 reasons.
//  1. cookie_samesite in OpenEMR is set to 'Strict', which is an effective security measure to stop csrf vulnerabilities.
//  2. Additionally, in this script there are no state changes, thus it is not even sensitive to csrf vulnerabilities.

$service = new PatientFinderQueryService(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
$out = $service->handle(Request::createFromGlobals()->query->all());

// Encoding with options for escaping a special chars - JSON_HEX_TAG (<)(>), JSON_HEX_AMP(&), JSON_HEX_APOS('), JSON_HEX_QUOT(").
echo json_encode($out, 15);
