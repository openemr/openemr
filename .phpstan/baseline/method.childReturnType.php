<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Return type \\(void\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:index\\(\\) should be compatible with return type \\(null\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\AppDispatch\\:\\:index\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(void\\) of method Carecoordination\\\\Controller\\\\EncounterccdadispatchController\\:\\:indexAction\\(\\) should be compatible with return type \\(Laminas\\\\View\\\\Model\\\\ViewModel\\) of method Laminas\\\\Mvc\\\\Controller\\\\AbstractActionController\\:\\:indexAction\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAllergyIntolerance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAllergyIntoleranceService\\:\\:createProvenanceResource\\(\\) should be compatible with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Tests\\\\Fixtures\\\\GaclFixtureManager\\:\\:getSingleFixture\\(\\) should be compatible with return type \\(OpenEMR\\\\Tests\\\\Fixtures\\\\a\\) of method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:getSingleFixture\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/GaclFixtureManager.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
