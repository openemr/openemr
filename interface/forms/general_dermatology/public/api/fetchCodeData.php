<?php

/**
 * CPT/code autocomplete endpoint for General Dermatology charges.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once dirname(__DIR__, 4) . '/globals.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use GeneralDermatology\Support\ApiRequestGuard;
use GeneralDermatology\Support\FormContext;
use OpenEMR\Common\Database\QueryUtils;

header('Content-Type: application/json');

$context = FormContext::fromRuntime();
ApiRequestGuard::requireAuthenticatedSession($context);

$raw = file_get_contents('php://input');
$inputData = json_decode($raw ?: '[]', true);
if (!is_array($inputData)) {
    $inputData = [];
}

ApiRequestGuard::assertCsrf($context, $inputData);

$query = trim((string) ($inputData['query'] ?? ''));
if ($query === '') {
    echo json_encode([]);
    exit;
}

$like = '%' . $query . '%';
$searchResults = QueryUtils::fetchRecords(
    'SELECT code, code_text FROM codes WHERE (code_text LIKE ? OR code LIKE ?) AND code_type = 1 LIMIT 50',
    [$like, $like]
) ?: [];

echo json_encode($searchResults);
