<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Class User referenced with incorrect case\\: user\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/AbstractClickmapModel.php',
];
$ignoreErrors[] = [
    'message' => '#^Class ZipArchive referenced with incorrect case\\: ZIPARCHIVE\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Class DOMXPath referenced with incorrect case\\: DOMXpath\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Class DOMXPath referenced with incorrect case\\: DOMXpath\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Authentication/IAuthenticatable.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/CacheMemCache.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/CacheNoCache.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/CacheRam.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Criteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ICache.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/IObservable.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/IRenderEngine.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Observable.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PHPRenderEngine.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/SavantRenderEngine.php',
];
$ignoreErrors[] = [
    'message' => '#^Class VARIANT referenced with incorrect case\\: variant\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Util/MemCacheProxy.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
