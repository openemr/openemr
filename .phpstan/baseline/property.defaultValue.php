<?php declare(strict_types = 1);

$ignoreErrors = [];
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
