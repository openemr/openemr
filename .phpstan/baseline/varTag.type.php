<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var with type mixed is not subtype of type array\\<mixed\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var with type int is not subtype of type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/AddressService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var with type OpenEMR\\\\Services\\\\Search\\\\TokenSearchField is not subtype of type OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var with type OpenEMR\\\\Services\\\\Search\\\\TokenSearchField is not subtype of type OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var with type array\\{next_pid\\: int\\|string\\}\\|null is not subtype of type array\\<mixed\\>\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/DuplicatePatientDetectionTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
