<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Static property RequestUtil\\:\\:\\$bodyCache \\(body\\) does not accept default value of type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Static property RequestUtil\\:\\:\\$bodyCacheIsReady \\(true\\) does not accept default value of type false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Static property VerySimpleStringUtil\\:\\:\\$DEFAULT_CHARACTER_SET \\(the\\) does not accept default value of type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/String/VerySimpleStringUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Static property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$localCall \\(OpenEMR\\\\RestControllers\\\\Config\\\\set\\) does not accept default value of type false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Static property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$notRestCall \\(OpenEMR\\\\RestControllers\\\\Config\\\\set\\) does not accept default value of type false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
