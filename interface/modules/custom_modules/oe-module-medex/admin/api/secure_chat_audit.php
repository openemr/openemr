<?php

/**
 * Secure Chat Audit API
 * Handles pagination, sorting, search, and export of secure chat audit logs
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx
 * @copyright Copyright (c) 2024 MedEx
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once dirname(__FILE__, 6) . '/globals.php';
require_once dirname(__FILE__, 6) . '/library/patient.inc.php';

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Acl\AclMain;

// Check ACL - must be able to view patients at minimum
if (!AclMain::aclCheckCore('patients', 'demo')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$action = $_REQUEST['action'] ?? 'get_secure_chat_audit';

// For export action, handle specially
if ($action === 'export') {
    if (!CsrfUtils::verifyCsrfToken($_GET['csrf_token_form'] ?? '', 'default')) {
        http_response_code(403);
        echo "CSRF verification failed";
        exit;
    }
    exportChatAudit();
    exit;
}

// For POST actions, verify CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '', 'default')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'CSRF verification failed']);
        exit;
    }
}

header('Content-Type: application/json');

switch ($action) {
    case 'get_secure_chat_audit':
        getSecureChatAudit();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}

/**
 * Get secure chat audit records with pagination and sorting
 */
function getSecureChatAudit()
{
    $page = max(1, intval($_POST['page'] ?? 1));
    $perPage = max(10, min(100, intval($_POST['per_page'] ?? 25)));
    $sortColumn = $_POST['sort_column'] ?? 'date';
    $sortDirection = strtoupper($_POST['sort_direction'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
    $search = trim($_POST['search'] ?? '');
    
    // Map sort column names to actual database columns
    $sortMap = [
        'patient' => 'CONCAT(p.lname, p.fname)',
        'date' => 'log.created_at',
        'action' => 'log.action',
        'user' => 'u.username'
    ];
    
    $orderBy = $sortMap[$sortColumn] ?? 'log.created_at';
    
    // Check if table exists
    $tableCheck = sqlQuery("SHOW TABLES LIKE 'medex_secure_chat_log'");
    if (empty($tableCheck)) {
        // Table doesn't exist yet - return empty results
        echo json_encode([
            'success' => true,
            'records' => [],
            'total' => 0,
            'page' => $page,
            'per_page' => $perPage
        ]);
        return;
    }
    
    // Build WHERE clause for search
    $whereClause = "1=1";
    $params = [];
    
    if (!empty($search)) {
        $whereClause .= " AND (
            p.lname LIKE ? OR 
            p.fname LIKE ? OR 
            CONCAT(p.fname, ' ', p.lname) LIKE ? OR
            log.action LIKE ? OR
            u.username LIKE ?
        )";
        $searchParam = "%{$search}%";
        $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
    }
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total 
                 FROM medex_secure_chat_log log
                 LEFT JOIN patient_data p ON log.pid = p.pid
                 LEFT JOIN users u ON log.created_by = u.id
                 WHERE {$whereClause}";
    
    $countResult = sqlQuery($countSql, $params);
    $total = intval($countResult['total'] ?? 0);
    
    // Get records with pagination
    $offset = ($page - 1) * $perPage;
    
    $sql = "SELECT 
                log.id,
                log.pid,
                log.action,
                log.method,
                log.details,
                log.created_at,
                log.created_by,
                CONCAT(p.fname, ' ', p.lname) as patient_name,
                u.username as user_name
            FROM medex_secure_chat_log log
            LEFT JOIN patient_data p ON log.pid = p.pid
            LEFT JOIN users u ON log.created_by = u.id
            WHERE {$whereClause}
            ORDER BY {$orderBy} {$sortDirection}
            LIMIT ?, ?";
    
    $params[] = $offset;
    $params[] = $perPage;
    
    $records = [];
    $result = sqlStatement($sql, $params);
    while ($row = sqlFetchArray($result)) {
        $records[] = [
            'id' => $row['id'],
            'pid' => $row['pid'],
            'patient_name' => $row['patient_name'] ?: 'Unknown Patient',
            'action' => $row['action'],
            'method' => $row['method'],
            'details' => $row['details'],
            'created_at' => $row['created_at'],
            'user_name' => $row['user_name']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'records' => $records,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage
    ]);
}

/**
 * Export chat audit to CSV
 */
function exportChatAudit()
{
    $sortColumn = $_GET['sort_column'] ?? 'date';
    $sortDirection = strtoupper($_GET['sort_direction'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
    $search = trim($_GET['search'] ?? '');
    
    // Map sort column names to actual database columns
    $sortMap = [
        'patient' => 'CONCAT(p.lname, p.fname)',
        'date' => 'log.created_at',
        'action' => 'log.action',
        'user' => 'u.username'
    ];
    
    $orderBy = $sortMap[$sortColumn] ?? 'log.created_at';
    
    // Check if table exists
    $tableCheck = sqlQuery("SHOW TABLES LIKE 'medex_secure_chat_log'");
    if (empty($tableCheck)) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="secure_chat_audit_' . date('Y-m-d') . '.csv"');
        echo "No audit data available\n";
        return;
    }
    
    // Build WHERE clause for search
    $whereClause = "1=1";
    $params = [];
    
    if (!empty($search)) {
        $whereClause .= " AND (
            p.lname LIKE ? OR 
            p.fname LIKE ? OR 
            CONCAT(p.fname, ' ', p.lname) LIKE ? OR
            log.action LIKE ? OR
            u.username LIKE ?
        )";
        $searchParam = "%{$search}%";
        $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
    }
    
    $sql = "SELECT 
                log.id,
                log.pid,
                log.action,
                log.method,
                log.details,
                log.created_at,
                CONCAT(p.fname, ' ', p.lname) as patient_name,
                u.username as user_name
            FROM medex_secure_chat_log log
            LEFT JOIN patient_data p ON log.pid = p.pid
            LEFT JOIN users u ON log.created_by = u.id
            WHERE {$whereClause}
            ORDER BY {$orderBy} {$sortDirection}";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="secure_chat_audit_' . date('Y-m-d_His') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write header row
    fputcsv($output, [
        'ID',
        'Patient ID',
        'Patient Name',
        'Action',
        'Method',
        'User',
        'Date/Time',
        'Details'
    ]);
    
    // Write data rows
    $result = sqlStatement($sql, $params);
    while ($row = sqlFetchArray($result)) {
        fputcsv($output, [
            $row['id'],
            $row['pid'],
            $row['patient_name'] ?: 'Unknown Patient',
            $row['action'],
            $row['method'],
            $row['user_name'] ?: 'System',
            $row['created_at'],
            $row['details']
        ]);
    }
    
    fclose($output);
}
