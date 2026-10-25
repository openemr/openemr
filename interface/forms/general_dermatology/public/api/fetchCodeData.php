<?php

/**
 * CPT/HCPCS autocomplete endpoint for General Dermatology charges.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

// public/api -> public -> form -> forms -> interface
require_once dirname(__DIR__, 4) . '/globals.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use GeneralDermatology\Support\ApiRequestGuard;
use GeneralDermatology\Support\FormContext;
use OpenEMR\Common\Database\QueryUtils;

header('Content-Type: application/json; charset=utf-8');

try {
    $context = FormContext::fromRuntime();
    ApiRequestGuard::requireAuthenticatedSession($context);

    $raw = file_get_contents('php://input');
    $inputData = json_decode($raw ?: '[]', true);
    if (!is_array($inputData)) {
        $inputData = [];
    }

    ApiRequestGuard::assertCsrf($context, $inputData);

    $query = trim((string) ($inputData['query'] ?? $_POST['query'] ?? ''));
    if ($query === '' || mb_strlen($query) < 2) {
        echo json_encode([]);
        exit;
    }

    // Resolve billable code type ids (CPT4 / HCPCS) instead of hardcoding "1".
    $typeRows = QueryUtils::fetchRecords(
        "SELECT ct_id FROM code_types WHERE ct_key IN ('CPT4', 'HCPCS', 'CPT') AND ct_active = 1"
    ) ?: [];
    $typeIds = [];
    foreach ($typeRows as $row) {
        $typeIds[] = (int) ($row['ct_id'] ?? 0);
    }
    $typeIds = array_values(array_filter($typeIds, static fn (int $id): bool => $id > 0));
    if ($typeIds === []) {
        $typeIds = [1];
    }

    $placeholders = implode(',', array_fill(0, count($typeIds), '?'));
    $like = '%' . $query . '%';
    $params = array_merge([$like, $like], $typeIds);

    $searchResults = QueryUtils::fetchRecords(
        "SELECT code, code_text
         FROM codes
         WHERE active = 1
           AND (code LIKE ? OR code_text LIKE ?)
           AND code_type IN ($placeholders)
         ORDER BY code ASC
         LIMIT 50",
        $params
    ) ?: [];

    $out = [];
    foreach ($searchResults as $row) {
        $out[] = [
            'code' => (string) ($row['code'] ?? ''),
            'code_text' => (string) ($row['code_text'] ?? ''),
        ];
    }

    echo json_encode($out);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Charge code search failed',
        'message' => $e->getMessage(),
    ]);
}
