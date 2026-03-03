<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertFalse\\(\\) with true and \'expected…\' will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'OpenEMR\\\\\\\\Validators\\\\\\\\ProcessingResult\' and true will always evaluate to false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/BaseValidatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertFalse\\(\\) with true will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Validators/ProcessingResultTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertNull\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRReference and \'Problems/health…\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService8_0_0Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertNull\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDocumentReference\\\\FHIRDocumentReferenceContext and \'DocumentReference…\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsInt\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDecimal and \'Dispense quantity…\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsInt\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUnsignedInt will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertNull\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBoolean and \'MedicationRequest…\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsString\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPatient will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertTrue\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBoolean will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertNull\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCode and \'Sex extension…\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertNull\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCoding and \'Sex extension must…\' will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertNotNull\\(\\) with null and \'Should have found…\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertNotNull\\(\\) with null and \'Should have found…\' will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertTrue\\(\\) with false and \'Should have survey…\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertNotNull\\(\\) with null and \'Should have found…\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertTrue\\(\\) with false and \'All observations…\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertFalse\\(\\) with true will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/ObservationServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertEmpty\\(\\) with array\\{_REWRITE_COMMAND\\: \'default/fhir…\'\\} and \'\\$_GET should be…\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Http/HttpRestRequestTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
