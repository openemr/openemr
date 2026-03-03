<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset 1 to list\\<string\\>\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset \'CLM01\' to array\\<string, mixed\\>\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset \'Claim_ct\' to array\\<string, mixed\\>\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset \'Date\' to array\\<string, mixed\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset \'Denied\' to array\\<string, mixed\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset \'InsLevel\' to array\\<string, mixed\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset \'Payer\' to array\\<string, mixed\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset \'Provider\' to array\\<string, mixed\\>\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset \'PtName\' to array\\<string, mixed\\>\\|string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset \'PtPaid\' to array\\<string, mixed\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset \'SvcDate\' to array\\<string, mixed\\>\\|string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset \'Trace\' to array\\<string, mixed\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset mixed to array\\<array\\<string, mixed\\>\\>\\|string\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset mixed to array\\<int\\<0, max\\>, array\\<string, mixed\\>\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign offset mixed to array\\|string\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_parse.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot assign new offset to list\\<string\\>\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EDI270.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
