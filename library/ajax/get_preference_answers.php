<?php

/**
 * AJAX Handler - Get Answer List for LOINC Code
 * Returns answer options for a given LOINC code in JSON format
 *
 * @package   OpenEMR
 * @link      https://www.openemr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;

if (!CsrfUtils::verifyCsrfToken($_GET['csrf_token'] ?? '')) {
    CsrfUtils::csrfNotVerified();
}

$loincCode = $_GET['loinc_code'] ?? '';

if (empty($loincCode)) {
    http_response_code(400);
    die(json_encode(['error' => 'LOINC code required']));
}

$sql = "SELECT * FROM preference_value_sets 
        WHERE loinc_code = ? AND active = 1 
        ORDER BY sort_order";

$result = QueryUtils::fetchRecords($sql, [$loincCode]);

$answers = [];
foreach ($result as $row) {
    $answers[] = [
        'answer_code' => $row['answer_code'],
        'answer_system' => $row['answer_system'],
        'answer_display' => $row['answer_display'],
        'answer_definition' => $row['answer_definition']
    ];
}

header('Content-Type: application/json');
echo json_encode($answers);
