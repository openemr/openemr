<?php

/**
 * AJAX endpoint: post payment advice(s) to OpenEMR.
 *
 * The browser sends only `paymentAdviceId` (or a JSON list for batch mode).
 * The server re-fetches the authoritative ClaimRev payment-advice
 * aggregation by id and posts that — never the browser-supplied JSON — so
 * a tampered request cannot redirect amounts, encounters, or line items.
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
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ClaimRevConnector\Bootstrap;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;
use OpenEMR\Modules\ClaimRevConnector\PaymentAdvicePage;
use OpenEMR\Modules\ClaimRevConnector\PaymentAdvicePostingService;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('acct', 'bill')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

if (!CsrfHelper::verifyCsrfToken(ModuleInput::postString('csrf_token'), 'payment_advice')) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

$bootstrap = new Bootstrap(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher());
$isTestModeEnabled = $bootstrap->getGlobalConfig()->isTestModeEnabled();

// Honor the caller's testMode flag only when the global config actually
// enables test mode. Otherwise a regular user could opt out of the
// mark-as-worked side effect on the ClaimRev side by adding `testMode` to
// the POST body.
$skipMarkWorked = $isTestModeEnabled && ModuleInput::postExists('testMode');

$mode = ModuleInput::postString('mode', 'single');

if ($mode === 'batch') {
    $idsJson = ModuleInput::postString('paymentAdviceIds');
    $decodedIds = json_decode($idsJson, true);
    if (!is_array($decodedIds)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid paymentAdviceIds']);
        exit;
    }

    $paymentDataList = [];
    foreach ($decodedIds as $id) {
        if (!is_string($id) || $id === '') {
            continue;
        }
        $advice = PaymentAdvicePage::getPaymentAdviceById($id);
        if ($advice !== null) {
            $paymentDataList[] = $advice;
        }
    }

    $result = PaymentAdvicePostingService::batchPost($paymentDataList, $skipMarkWorked);
    echo json_encode($result);
    exit;
}

$paymentAdviceId = ModuleInput::postString('paymentAdviceId');
if ($paymentAdviceId === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing paymentAdviceId']);
    exit;
}

$paymentData = PaymentAdvicePage::getPaymentAdviceById($paymentAdviceId);
if ($paymentData === null) {
    http_response_code(404);
    echo json_encode(['error' => 'Payment advice not found']);
    exit;
}

$approved = ModuleInput::postExists('approved');
$result = PaymentAdvicePostingService::post($paymentData, $skipMarkWorked, $approved);
echo json_encode($result);
