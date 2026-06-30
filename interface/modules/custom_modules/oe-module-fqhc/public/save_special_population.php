<?php

/**
 * FQHC module — add or remove a special-population status (Step 4, #16).
 *
 * Handles the special-populations card POST from the UDS Patient Snapshot:
 * verifies CSRF, parses the submitted values into a typed
 * SpecialPopulationStatus at the boundary, persists or removes it, and
 * redirects back to the snapshot (POST/redirect/GET).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../../../../globals.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\FQHC\SpecialPopulation\PatientSpecialPopulationRepository;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulation;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulationStatus;

if (!AclMain::aclCheckCore('patients', 'demo')) {
    echo xlt('Access denied');
    exit;
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$csrfToken = filter_input(INPUT_POST, 'csrf_token_form');
if (!is_string($csrfToken) || !CsrfUtils::verifyCsrfToken($csrfToken, $session)) {
    CsrfUtils::csrfNotVerified();
}

$sessionPid = $_SESSION['pid'] ?? 0;
$pid = is_numeric($sessionPid) ? (int) $sessionPid : 0;

$populationRaw = filter_input(INPUT_POST, 'population');
$population = is_string($populationRaw) ? SpecialPopulation::tryFrom($populationRaw) : null;

if ($pid > 0 && $population !== null) {
    $repository = new PatientSpecialPopulationRepository();
    $action = filter_input(INPUT_POST, 'action');

    if ($action === 'remove') {
        $repository->remove($pid, $population);
    } else {
        $subtypeRaw = filter_input(INPUT_POST, 'subtype');
        $subtype = is_string($subtypeRaw) && $subtypeRaw !== '' ? $subtypeRaw : null;

        $dateRaw = filter_input(INPUT_POST, 'as_of_date');
        $asOfDate = is_string($dateRaw) && $dateRaw !== '' ? $dateRaw : null;

        try {
            $status = new SpecialPopulationStatus($population, $subtype, $asOfDate);
        } catch (\DomainException) {
            // Subtype not valid for the chosen population: record without it.
            $status = new SpecialPopulationStatus($population, null, $asOfDate);
        }

        $authUser = $_SESSION['authUserID'] ?? null;
        $recordedBy = is_numeric($authUser) ? (int) $authUser : null;

        $repository->save($pid, $status, $recordedBy);
    }
}

header('Location: index.php');
exit;
