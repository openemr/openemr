<?php

/**
 * AJAX endpoint that returns the inventory lots eligible to fill a dispense
 * for a given drug. Backs the lot picker on the prescription Save-and-Dispense
 * form (templates/prescription/general_edit.html.twig).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Christoph Burnicki <christoph@cherrypielabs.com>
 * @copyright Copyright (c) 2026 Christoph Burnicki <christoph@cherrypielabs.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\DrugSalesService;

CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

if (!AclMain::aclCheckCore('patients', 'rx')) {
    http_response_code(403);
    echo json_encode(['error' => xl('Not authorized')]);
    exit;
}

$drugId = filter_input(INPUT_GET, 'drug_id', FILTER_VALIDATE_INT);
if ($drugId === null || $drugId === false || $drugId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => xl('Missing or invalid drug_id')]);
    exit;
}

// Resolve the user's default warehouse — same lookup sellDrug() does so the
// picker matches what the auto-selector would consume.
$session = SessionWrapperFactory::getInstance()->getActiveSession();
$authUserRaw = $session->get('authUser');
$user = is_scalar($authUserRaw) ? (string)$authUserRaw : '';
$defaultWarehouse = '';
if ($user !== '') {
    $userRow = QueryUtils::fetchRecords("SELECT default_warehouse FROM users WHERE username = ?", [$user])[0] ?? null;
    $rawWarehouse = is_array($userRow) ? ($userRow['default_warehouse'] ?? '') : '';
    $defaultWarehouse = is_scalar($rawWarehouse) ? (string)$rawWarehouse : '';
}

// The lot picker shows every available lot — including off-site ones —
// so the dispensing clinician can see what's where. Off-site rows are
// flagged in the payload. Whether off-site lots are actually selectable
// depends on the clinic-level restrict_sales_to_default_warehouse flag;
// the UI uses restrict_to_default to render off-site rows as disabled
// when the policy is strict.
$lots = (new DrugSalesService())->getAvailableLots($drugId, $defaultWarehouse, false);

header('Content-Type: application/json');
echo json_encode([
    'lots' => $lots,
    'has_default_warehouse' => $defaultWarehouse !== '',
    'restrict_to_default' => DrugSalesService::restrictSalesToDefaultWarehouse(),
]);
