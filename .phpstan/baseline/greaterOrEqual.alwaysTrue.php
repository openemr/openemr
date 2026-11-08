<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>\\=" between 0\\|int\\<2, max\\> and 0 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/php-barcode.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>\\=" between int\\<1, max\\> and 1 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
