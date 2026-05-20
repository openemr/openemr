<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;

header('Content-Type: application/json');

if (!CsrfHelper::verifyCsrfToken(ModuleInput::postString('csrf_token'))) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

if (!AclMain::aclCheckCore('acct', 'bill')) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$id = ModuleInput::postInt('id');
if ($id === 0) {
    echo json_encode(['success' => false, 'message' => 'Missing tracker ID']);
    exit;
}

QueryUtils::sqlStatementThrowException(
    "UPDATE x12_remote_tracker SET status = 'waiting', messages = NULL, updated_at = NOW() WHERE id = ?",
    [$id]
);

echo json_encode(['success' => true]);
