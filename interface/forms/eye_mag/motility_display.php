<?php

/** Save the practice-wide Eye Form motility-chart presentation. */

require_once(__DIR__ . '/../../globals.php');
require_once(__DIR__ . '/php/eye_mag_functions.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Forms\EyeMag\EyeFormData;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('patients', 'med', '', 'write')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => xl('Access denied')]);
    exit;
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$posted = CurrentRequest::get()->request->all();
if (!CsrfUtils::verifyCsrfToken(EyeFormData::text($posted['csrf_token_form'] ?? ''), session: $session)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => xl('Invalid security token')]);
    exit;
}

$display = strtoupper(trim(EyeFormData::text($posted['display'] ?? '')));
if (!in_array($display, ['X', 'H'], true)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => xl('Invalid motility display')]);
    exit;
}

QueryUtils::inTransaction(static function () use ($display): void {
    QueryUtils::sqlStatementThrowException(
        "UPDATE list_options SET is_default = 0 WHERE list_id = 'Eye_Motility_Display'"
    );
    QueryUtils::sqlStatementThrowException(
        "UPDATE list_options SET is_default = 1
          WHERE list_id = 'Eye_Motility_Display' AND option_id = ?",
        [$display]
    );
});

echo json_encode(['ok' => true, 'display' => $display]);
