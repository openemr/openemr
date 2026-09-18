<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Class DOMXPath referenced with incorrect case\\: DOMXpath\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
