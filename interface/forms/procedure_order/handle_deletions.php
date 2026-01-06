<?php

/**
 * AJAX handler for real-time deletions of procedures and specimens
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF validation failed']);
    exit;
}

$action = $_POST['action'] ?? '';
$response = ['success' => false];

try {
    switch ($action) {
        case 'delete_procedure':
            $response = deleteProcedure();
            break;

        case 'delete_specimen':
            $response = deleteSpecimen();
            break;

        default:
            $response['error'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit;

/**
 * Delete a procedure order code and its related data
 */
function deleteProcedure()
{
    $orderId = (int)($_POST['order_id'] ?? 0);
    $orderSeq = (int)($_POST['order_seq'] ?? 0);

    if (!$orderId || !$orderSeq) {
        return ['success' => false, 'error' => 'Missing required parameters'];
    }

    sqlBeginTrans();

    try {
        // Delete procedure answers (QOE)
        sqlStatement(
            "DELETE FROM procedure_answers
             WHERE procedure_order_id = ? AND procedure_order_seq = ?",
            [$orderId, $orderSeq]
        );

        // Soft delete specimens (set deleted = 1)
        sqlStatement(
            "UPDATE procedure_specimen
             SET deleted = 1, updated_by = ?
             WHERE procedure_order_id = ? AND procedure_order_seq = ?",
            [($_SESSION['authUserID'] ?? null), $orderId, $orderSeq]
        );

        // Hard delete the procedure order code
        sqlStatement(
            "DELETE FROM procedure_order_code
             WHERE procedure_order_id = ? AND procedure_order_seq = ?",
            [$orderId, $orderSeq]
        );

        // Check if this was the last procedure in the order
        $remaining = sqlQuery(
            "SELECT COUNT(*) as cnt FROM procedure_order_code WHERE procedure_order_id = ?",
            [$orderId]
        );

        if ($remaining['cnt'] == 0) {
            // Mark the entire order as inactive
            sqlStatement(
                "UPDATE procedure_order SET activity = 0 WHERE procedure_order_id = ?",
                [$orderId]
            );
        }

        sqlCommitTrans();

        return [
            'success' => true,
            'orderEmpty' => ($remaining['cnt'] == 0)
        ];
    } catch (Exception $e) {
        sqlRollbackTrans();
        throw $e;
    }
}

/**
 * Soft delete a specimen
 */
function deleteSpecimen()
{
    $specimenId = (int)($_POST['specimen_id'] ?? 0);

    if (!$specimenId) {
        return ['success' => false, 'error' => 'Missing specimen ID'];
    }

    try {
        sqlStatement(
            "UPDATE procedure_specimen
             SET deleted = 1, updated_by = ?
             WHERE procedure_specimen_id = ?",
            [($_SESSION['authUserID'] ?? null), $specimenId]
        );

        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
