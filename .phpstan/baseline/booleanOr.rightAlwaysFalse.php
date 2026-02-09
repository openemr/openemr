<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Right side of \\|\\| is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of \\|\\| is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of \\|\\| is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/pat_ledger.php',
];
$ignoreErrors[] = [
    'message' => '#^Right side of \\|\\| is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/SearchFieldComparableValue.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
