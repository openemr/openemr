<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Class ZipArchive referenced with incorrect case\\: ZIPARCHIVE\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Class DOMXPath referenced with incorrect case\\: DOMXpath\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
