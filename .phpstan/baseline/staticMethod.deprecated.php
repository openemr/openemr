<?php declare(strict_types = 1);

// total 75 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method createFromPath\\(\\) of class League\\\\Csv\\\\Reader\\:
since version 9\\.27\\.0

Returns a new instance from a file path\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/billing/load_fee_schedule.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method createFromPath\\(\\) of class League\\\\Csv\\\\Reader\\:
since version 9\\.27\\.0

Returns a new instance from a file path\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method createFromString\\(\\) of class League\\\\Csv\\\\AbstractCsv\\:
since version 9\\.27\\.0

Returns a new instance from a string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerLog.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method forUnsecuredSigner\\(\\) of class Lcobucci\\\\JWT\\\\Configuration\\:
Deprecated since v4\\.3$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/JsonWebKeyParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method fromArray\\(\\) of class Laminas\\\\Code\\\\Generator\\\\DocBlockGenerator\\:
this API is deprecated, and will be removed in the next major release\\. Please
            use the other constructors of this class instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Cqm/Generator.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method fromArray\\(\\) of class Laminas\\\\Code\\\\Generator\\\\FileGenerator\\:
this API is deprecated, and will be removed in the next major release\\. Please
            use the other constructors of this class instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/Generator.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/RestControllers/AllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/RestControllers/ConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/RestControllers/DrugRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EmployerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/RestControllers/ImmunizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../src/RestControllers/InsuranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/RestControllers/PrescriptionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/RestControllers/ProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/RestControllers/TransactionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/RestControllers/UserRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method forUnsecuredSigner\\(\\) of class Lcobucci\\\\JWT\\\\Configuration\\:
Deprecated since v4\\.3$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/JWTClientAuthenticationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method isType\\(\\) of class PHPUnit\\\\Framework\\\\Assert\\:
https\\://github\\.com/sebastianbergmann/phpunit/issues/6052$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Common/Auth/OpenIDConnect/SMARTSessionTokenContextIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method isType\\(\\) of class PHPUnit\\\\Framework\\\\Assert\\:
https\\://github\\.com/sebastianbergmann/phpunit/issues/6052$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method isType\\(\\) of class PHPUnit\\\\Framework\\\\Assert\\:
https\\://github\\.com/sebastianbergmann/phpunit/issues/6052$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Telemetry/TelemetryServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method handleProcessingResult\\(\\) of class OpenEMR\\\\RestControllers\\\\RestControllerHelper\\:
use createProcessingResultResponse\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/HandleProcessingResultTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
