<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @throws with type PEAR_Error is not subtype of Throwable$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/Cache_Lite/Lite.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @throws with type Installer\\\\Model\\\\Exception\\\\InvalidArgumentException is not subtype of Throwable$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTableGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @throws with type object is not subtype of Throwable$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @throws with type OpenEMR\\\\Common\\\\Crypto\\\\InvalidArgumentException is not subtype of Throwable$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Crypto/KeyVersion.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @throws with type OpenEMR\\\\Menu\\\\InvalidArgumentException is not subtype of Throwable$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/MenuItems.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
