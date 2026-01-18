<?php declare(strict_types = 1);

// total 11 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Instantiation of deprecated class Laminas\\\\View\\\\Model\\\\JsonModel\\:
Since 2\\.40\\.0\\. This class will be removed in 3\\.0 without replacement\\. Laminas\\\\View will no longer support
            rendering strategies in 3\\.0$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiation of deprecated class Laminas\\\\View\\\\Model\\\\JsonModel\\:
Since 2\\.40\\.0\\. This class will be removed in 3\\.0 without replacement\\. Laminas\\\\View will no longer support
            rendering strategies in 3\\.0$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiation of deprecated class Laminas\\\\View\\\\Model\\\\JsonModel\\:
Since 2\\.40\\.0\\. This class will be removed in 3\\.0 without replacement\\. Laminas\\\\View will no longer support
            rendering strategies in 3\\.0$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CcdController.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiation of deprecated class Laminas\\\\View\\\\Model\\\\JsonModel\\:
Since 2\\.40\\.0\\. This class will be removed in 3\\.0 without replacement\\. Laminas\\\\View will no longer support
            rendering strategies in 3\\.0$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncountermanagerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiation of deprecated class Laminas\\\\View\\\\Model\\\\JsonModel\\:
Since 2\\.40\\.0\\. This class will be removed in 3\\.0 without replacement\\. Laminas\\\\View will no longer support
            rendering strategies in 3\\.0$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiation of deprecated class Ramsey\\\\Uuid\\\\Codec\\\\TimestampFirstCombCodec\\:
Please migrate to \\{@link https\\://uuid\\.ramsey\\.dev/en/stable/rfc4122/version7\\.html Version 7, Unix Epoch Time UUIDs\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Instantiation of deprecated class Ramsey\\\\Uuid\\\\Generator\\\\CombGenerator\\:
Please migrate to \\{@link https\\://uuid\\.ramsey\\.dev/en/stable/rfc4122/version7\\.html Version 7, Unix Epoch Time UUIDs\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
