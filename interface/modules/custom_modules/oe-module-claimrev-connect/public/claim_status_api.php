<?php

/**
 * AJAX endpoint for Claim Status Dashboard actions.
 *
 * Handles timeline loading, status checks, sync, and manual notes.
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
use OpenEMR\Modules\ClaimRevConnector\ClaimTrackingService;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('acct', 'bill')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

if (!CsrfHelper::verifyCsrfToken(ModuleInput::postString('csrf_token'), 'claim_status')) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

$action = ModuleInput::postString('action');

switch ($action) {
    case 'get_timeline':
        $pid = ModuleInput::postInt('pid');
        $encounter = ModuleInput::postInt('encounter');
        $payerType = ModuleInput::postInt('payer_type');

        if ($pid <= 0 || $encounter <= 0) {
            echo json_encode(['error' => 'Invalid pid/encounter']);
            exit;
        }

        $timeline = ClaimTrackingService::getClaimTimeline($pid, $encounter, $payerType);
        $record = ClaimTrackingService::getClaimRecord($pid, $encounter, $payerType !== 0 ? $payerType : 1);
        echo json_encode(['timeline' => $timeline, 'record' => $record]);
        break;

    case 'check_status':
        $pid = ModuleInput::postInt('pid');
        $encounter = ModuleInput::postInt('encounter');
        $payerType = ModuleInput::postInt('payer_type', 1);

        if ($pid <= 0 || $encounter <= 0) {
            echo json_encode(['error' => 'Invalid pid/encounter']);
            exit;
        }

        $result = ClaimTrackingService::checkStatus276($pid, $encounter, $payerType);
        echo json_encode($result);
        break;

    case 'batch_sync':
        $pcnsJson = ModuleInput::postString('pcns');
        $pcnsRaw = json_decode($pcnsJson, true);

        if (!is_array($pcnsRaw) || $pcnsRaw === []) {
            echo json_encode(['error' => 'Invalid PCN list']);
            exit;
        }

        $pcns = [];
        foreach ($pcnsRaw as $pcn) {
            if (is_string($pcn) && $pcn !== '') {
                $pcns[] = $pcn;
            }
        }

        $result = ClaimTrackingService::batchSyncFromClaimRev($pcns);
        echo json_encode($result);
        break;

    case 'add_note':
        $pid = ModuleInput::postInt('pid');
        $encounter = ModuleInput::postInt('encounter');
        $payerType = ModuleInput::postInt('payer_type', 1);
        $noteText = trim(ModuleInput::postString('note_text'));

        if ($pid <= 0 || $encounter <= 0 || $noteText === '') {
            echo json_encode(['error' => 'Invalid parameters']);
            exit;
        }

        $eventId = ClaimTrackingService::logEvent(
            $pid,
            $encounter,
            $payerType,
            ClaimTrackingService::EVENT_MANUAL_NOTE,
            ClaimTrackingService::SOURCE_USER,
            detailText: $noteText,
        );

        echo json_encode(['success' => true, 'event_id' => $eventId]);
        break;

    case 'get_stats':
        $statsFilters = [
            'dateStart' => ModuleInput::postString('dateStart'),
            'dateEnd' => ModuleInput::postString('dateEnd'),
        ];
        $stats = ClaimTrackingService::getDashboardStats($statsFilters);
        echo json_encode($stats);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action: ' . $action]);
        break;
}
