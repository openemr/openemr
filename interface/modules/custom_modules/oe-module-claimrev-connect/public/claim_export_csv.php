<?php

/**
 * AJAX endpoint to export claims search as CSV via ClaimRev API.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\ClaimRevConnector\ClaimsPage;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;
use OpenEMR\Modules\ClaimRevConnector\TypeCoerce;

if (!AclMain::aclCheckCore('acct', 'bill')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!CsrfHelper::verifyCsrfToken(ModuleInput::postString('csrf_token'), 'claims')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Parse the search form fields the same way claims.php does so the same
// filter set produces the same CSV. Using ModuleInput routes through
// filter_input(INPUT_POST) instead of touching $_POST directly.
$exportFilters = [
    'patFirstName' => ModuleInput::postString('patFirstName'),
    'patLastName' => ModuleInput::postString('patLastName'),
    'patientGender' => ModuleInput::postString('patientGender'),
    'patientBirthDate' => ModuleInput::postString('patientBirthDate'),
    'startDate' => ModuleInput::postString('startDate'),
    'endDate' => ModuleInput::postString('endDate'),
    'serviceDateStart' => ModuleInput::postString('serviceDateStart'),
    'serviceDateEnd' => ModuleInput::postString('serviceDateEnd'),
    'payerName' => ModuleInput::postString('payerName'),
    'payerNumber' => ModuleInput::postString('payerNumber'),
    'payerPaidAmtStart' => ModuleInput::postString('payerPaidAmtStart'),
    'payerPaidAmtEnd' => ModuleInput::postString('payerPaidAmtEnd'),
    'traceNumber' => ModuleInput::postString('traceNumber'),
    'patientControlNumber' => ModuleInput::postString('patientControlNumber'),
    'payerControlNumber' => ModuleInput::postString('payerControlNumber'),
    'billingProviderNpi' => ModuleInput::postString('billingProviderNpi'),
    'errorMessage' => ModuleInput::postString('errorMessage'),
    'statusId' => ModuleInput::postString('statusId'),
    'sortField' => ModuleInput::postString('sortField'),
    'sortDirection' => ModuleInput::postString('sortDirection'),
];

try {
    $result = ClaimsPage::exportCsv($exportFilters);
    $fileText = TypeCoerce::asString($result['fileText'] ?? '');
    $fileNameRaw = TypeCoerce::asString($result['fileName'] ?? 'claims_export.csv');

    // The filename is supplied by the upstream API and emitted into a
    // response header. attr() escapes HTML attributes but leaves CR/LF
    // intact, which would let a tampered upstream value inject extra
    // headers (HTTP response splitting). Strip control chars, drop path
    // separators, restrict to a conservative charset, fall back to a
    // safe default if nothing legible remains.
    $fileName = basename($fileNameRaw);
    $fileName = preg_replace('/[\r\n\x00-\x1F\x7F]+/', '', $fileName) ?? '';
    $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName) ?? '';
    if (in_array($fileName, ['', '.', '..'], true)) {
        $fileName = 'claims_export.csv';
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    file_put_contents('php://output', $fileText);
} catch (\RuntimeException | \LogicException) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to export CSV']);
}
