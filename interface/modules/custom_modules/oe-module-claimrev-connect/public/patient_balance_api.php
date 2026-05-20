<?php

/**
 * AJAX endpoint for Patient Balance queue actions.
 *
 * Handles balance detail, statement logging, history, notes, and stats.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;
use OpenEMR\Modules\ClaimRevConnector\PatientBalanceService;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('acct', 'bill')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

if (!CsrfHelper::verifyCsrfToken(ModuleInput::postString('csrf_token'), 'patient_balance')) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

$action = ModuleInput::postString('action');

switch ($action) {
    case 'get_detail':
        $pid = ModuleInput::postInt('pid');
        $encounter = ModuleInput::postInt('encounter');

        if ($pid <= 0 || $encounter <= 0) {
            echo json_encode(['error' => 'Invalid pid/encounter']);
            exit;
        }

        $detail = PatientBalanceService::getBalanceDetail($pid, $encounter);
        $history = PatientBalanceService::getStatementHistory($pid, $encounter);
        echo json_encode(['detail' => $detail, 'history' => $history]);
        break;

    case 'log_statement':
        $pid = ModuleInput::postInt('pid');
        $encounter = ModuleInput::postInt('encounter');
        $method = trim(ModuleInput::postString('method', 'openemr_print'));
        $amount = ModuleInput::postFloat('amount');
        $notes = trim(ModuleInput::postString('notes'));

        if ($pid <= 0 || $encounter <= 0) {
            echo json_encode(['error' => 'Invalid pid/encounter']);
            exit;
        }

        $allowedMethods = ['openemr_print', 'openemr_email', 'openemr_portal', 'claimrev'];
        if (!in_array($method, $allowedMethods, true)) {
            $method = 'openemr_print';
        }

        $id = PatientBalanceService::logStatement($pid, $encounter, $method, $amount, $notes);
        echo json_encode(['success' => true, 'id' => $id]);
        break;

    case 'get_history':
        $pid = ModuleInput::postInt('pid');
        $encounter = ModuleInput::postInt('encounter');

        if ($pid <= 0 || $encounter <= 0) {
            echo json_encode(['error' => 'Invalid pid/encounter']);
            exit;
        }

        $history = PatientBalanceService::getStatementHistory($pid, $encounter);
        echo json_encode(['history' => $history]);
        break;

    case 'add_note':
        $pid = ModuleInput::postInt('pid');
        $encounter = ModuleInput::postInt('encounter');
        $notes = trim(ModuleInput::postString('notes'));

        if ($pid <= 0 || $encounter <= 0 || $notes === '') {
            echo json_encode(['error' => 'Invalid parameters']);
            exit;
        }

        $id = PatientBalanceService::logStatement($pid, $encounter, 'openemr_print', 0, $notes);
        echo json_encode(['success' => true, 'id' => $id]);
        break;

    case 'get_stats':
        $statsFilters = [
            'dateStart' => ModuleInput::postString('dateStart'),
            'dateEnd' => ModuleInput::postString('dateEnd'),
            'patientName' => ModuleInput::postString('patientName'),
            'payerName' => ModuleInput::postString('payerName'),
            'minAmount' => ModuleInput::postString('minAmount'),
            'stmtFilter' => ModuleInput::postString('stmtFilter'),
        ];
        $stats = PatientBalanceService::getQueueStats($statsFilters);
        echo json_encode($stats);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action: ' . $action]);
        break;
}
