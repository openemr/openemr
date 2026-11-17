<?php

/**
 * Confirms that a Direct reply filed with a referral answers it (#15137), which completes the
 * recipient's Task: what a reply's Confirm button on the referral's page posts to. Then back to that
 * page, which shows where it stands.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once("../../globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\ReferralStatus;
use OpenEMR\Services\ReferralTaskService;
use OpenEMR\Services\UserService;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
// Only a POST carries the token, so a link can't confirm.
CsrfUtils::checkCsrfInput(INPUT_POST, session: $session, dieOnFail: true);

if (!AclMain::aclCheckCore('patients', 'trans', '', 'write')) {
    AccessDeniedHelper::denyWithTemplate('ACL check failed for patients/trans: Transactions', xl('Transactions'));
}

// Read raw: the bag's typed getters throw on a field sent as an array, which is a server error here.
$post = CurrentRequest::get()->request->all();
$transactionId = is_numeric($post['transid'] ?? null) ? (int)$post['transid'] : 0;
$taskUuid = is_string($post['task'] ?? null) ? $post['task'] : '';
$replyId = is_numeric($post['reply'] ?? null) ? (int)$post['reply'] : 0;

// The referral has to be in the open chart, as add_transaction.php requires of what it edits.
$chart = $session->get('pid', 0);
$owner = QueryUtils::fetchSingleValue('SELECT pid FROM transactions WHERE id = ?', 'pid', [$transactionId]);
if (!is_numeric($chart) || (int)$chart <= 0 || !is_numeric($owner) || (int)$owner !== (int)$chart) {
    AccessDeniedHelper::deny('confirm_referral_reply: transid does not belong to the open chart');
}

$userId = $session->get('authUserID');
(new ReferralTaskService(new UserService()))->completeWithDirectReply($transactionId, $taskUuid, $replyId, is_numeric($userId) ? (int)$userId : null);

header('Location: add_transaction.php?transid=' . urlencode((string)$transactionId) . '&title=' . urlencode(ReferralStatus::FORM_ID) . '&inmode=edit');
