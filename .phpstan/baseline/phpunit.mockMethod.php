<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Trying to mock an undefined method getProjectDir\\(\\) on class OpenEMR\\\\Core\\\\Kernel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/Authorization/AuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to mock an undefined method getWebRoot\\(\\) on class OpenEMR\\\\Core\\\\Kernel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/Authorization/AuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to mock an undefined method getProjectDir\\(\\) on class OpenEMR\\\\Core\\\\Kernel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Controllers/Interface/Forms/Observation/ObservationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to mock an undefined method getWebRoot\\(\\) on class OpenEMR\\\\Core\\\\Kernel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Controllers/Interface/Forms/Observation/ObservationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to mock an undefined method getProjectDir\\(\\) on class OpenEMR\\\\Core\\\\Kernel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/FHIR/SMART/ClientAdminControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to mock an undefined method getWebRoot\\(\\) on class OpenEMR\\\\Core\\\\Kernel\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/FHIR/SMART/ClientAdminControllerTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
