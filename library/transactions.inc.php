<?php

/**
 * Thin delegators kept for the existing call sites of library/transactions.inc.php.
 * The bodies live in PatientTransactionService; see the migration tracker, openemr/openemr#11674.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\PatientTransactionService;

/**
 * Transaction row plus its lbt_data fields, by id; null when $id is not int|string or unmatched.
 */
function getTransById($id, $cols = "*")
{
    if (!is_int($id) && !is_string($id)) {
        return null;
    }
    $cols = is_scalar($cols) ? (string) $cols : '*';

    return PatientTransactionService::getTransById($id, $cols);
}

/**
 * Transactions for a patient plus their lbt_data fields; empty array when $pid is not int|string.
 */
function getTransByPid($pid, $cols = "*")
{
    if (!is_int($pid) && !is_string($pid)) {
        return [];
    }
    $cols = is_scalar($cols) ? (string) $cols : '*';

    return PatientTransactionService::getTransByPid($pid, $cols);
}

/**
 * Creates a transaction using the current session's user/groupname; $status and $assigned_to
 * are accepted for backward compatibility but were already unused by the original function.
 * Returns 0 without touching the database when $pid or $authorized is not int|string.
 */
function newTransaction($pid, $body, $title, $authorized = "0", $status = "1", $assigned_to = "*"): int
{
    if ((!is_int($pid) && !is_string($pid)) || (!is_int($authorized) && !is_string($authorized))) {
        return 0;
    }
    $body = is_scalar($body) ? (string) $body : '';
    $title = is_scalar($title) ? (string) $title : '';

    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $user = $session->get('authUser');
    $user = is_string($user) ? $user : '';
    $groupname = $session->get('authProvider');
    $groupname = is_string($groupname) ? $groupname : '';

    return PatientTransactionService::newTransaction($pid, $body, $title, $user, $groupname, $authorized);
}

/**
 * Sets a transaction's authorized flag; does nothing when $id or $authorized is not int|string.
 */
function authorizeTransaction($id, $authorized = "1"): void
{
    if ((!is_int($id) && !is_string($id)) || (!is_int($authorized) && !is_string($authorized))) {
        return;
    }

    PatientTransactionService::authorizeTransaction($id, $authorized);
}
