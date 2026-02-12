<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Trait OpenEMR\\\\Services\\\\Qrda\\\\Helpers\\\\AggregateObject is used zero times and is not analysed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/Helpers/AggregateObject.php',
];
$ignoreErrors[] = [
    'message' => '#^Trait OpenEMR\\\\Services\\\\Qrda\\\\Util\\\\EntityHelper is used zero times and is not analysed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/Util/EntityHelper.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
