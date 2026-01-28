<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Dead catch \\- Exception is never thrown in the try block\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Dead catch \\- Exception is never thrown in the try block\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Dead catch \\- Exception is never thrown in the try block\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/QueryBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Dead catch \\- Exception is never thrown in the try block\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/RsaSha384Signer.php',
];
$ignoreErrors[] = [
    'message' => '#^Dead catch \\- Error is never thrown in the try block\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Utils/RandomGenUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Dead catch \\- InvalidArgumentException is never thrown in the try block\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Dead catch \\- Exception is never thrown in the try block\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
