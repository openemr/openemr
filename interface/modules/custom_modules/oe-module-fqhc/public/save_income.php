<?php

/**
 * FQHC module — save a patient's income / FPL determination (Step 3, #15).
 *
 * Handles the income card POST from the UDS Patient Snapshot: verifies CSRF,
 * parses the submitted values into a typed IncomeDetermination at the boundary,
 * persists it, and redirects back to the snapshot (POST/redirect/GET).
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
use OpenEMR\FQHC\Fpl\IncomeDetermination;
use OpenEMR\FQHC\Income\PatientIncomeRepository;

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

if ($pid > 0) {
    $sizeRaw = filter_input(INPUT_POST, 'household_size');
    $householdSize = (is_numeric($sizeRaw) && (int) $sizeRaw >= 1) ? (int) $sizeRaw : null;

    $incomeRaw = filter_input(INPUT_POST, 'annual_income');
    $incomeClean = is_string($incomeRaw) ? str_replace([',', '$', ' '], '', $incomeRaw) : '';
    $annualIncome = (is_numeric($incomeClean) && (float) $incomeClean >= 0.0) ? (float) $incomeClean : null;

    $unknown = filter_input(INPUT_POST, 'income_unknown') === '1';

    $authUser = $_SESSION['authUserID'] ?? null;
    $recordedBy = is_numeric($authUser) ? (int) $authUser : null;

    (new PatientIncomeRepository())->save(
        $pid,
        new IncomeDetermination($householdSize, $annualIncome, $unknown),
        $recordedBy,
    );
}

header('Location: index.php');
exit;
