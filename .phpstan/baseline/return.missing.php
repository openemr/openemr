<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\FacilityProperties\\:\\:updateFacilityNumber\\(\\) should return mixed but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/FacilityProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Method MockRouter\\:\\:GetRoute\\(\\) should return array but return statement is missing\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/MockRouter.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
