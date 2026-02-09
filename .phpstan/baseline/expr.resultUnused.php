<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Expression "\\$encRow\\[\'encounter\'\\]" on a separate line does not do anything\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression "\\$data" on a separate line does not do anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/gad7/report.php',
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
    'message' => '#^Expression "\\$output" on a separate line does not do anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/html2text.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression "\\$this\\-\\>LastServerError" on a separate line does not do anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/CacheMemCache.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression "\\$url \\. "/"" on a separate line does not do anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/GenericRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression "\\$this\\-\\>model \\=\\= \\[\\]" on a separate line does not do anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PHPRenderEngine.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression "\\$this\\-\\>algo \\=\\= "DEFAULT"" on a separate line does not do anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthHash.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression "clone \\$instance" on a separate line does not do anything\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/Traits/SingletonTraitTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
