<?php declare(strict_types = 1);

// total 18 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getGlobalSettingSectionConfiguration\\(\\) of class OpenEMR\\\\Modules\\\\WenoModule\\\\WenoGlobalConfig\\:
Left for legacy purposes and replaced by installation set up\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getRequestMethod\\(\\) of class OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:
use getMethod\\(\\) instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method requestHasScope\\(\\) of class OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:
use requestHasScopeEntity\\(\\) instead which receives a ScopeEntity object$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method requestHasScope\\(\\) of class OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:
use requestHasScopeEntity\\(\\) instead which receives a ScopeEntity object$#',
    'count' => 12,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Http/HttpRestRequestTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setAccessTokenScopes\\(\\) of class OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:
use setAccessTokenScopeValidationArray\\(\\) instead which receives a ResourceScopeEntityList\\[\\] that is built from the ScopeRepository\\-\\>buildValidationArray$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Services/FHIR/Utils/SearchRequestNormalizerTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
