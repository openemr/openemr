<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Expression "\\$encRow\\[\'encounter\'\\]" on a separate line does not do anything\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression "\\$patientCountHash\\[\\$patientNameIndex\\]" on a separate line does not do anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression "\\$loopid \\=\\= \'2010A\'" on a separate line does not do anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression "clone \\$instance" on a separate line does not do anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/Traits/SingletonTraitTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
