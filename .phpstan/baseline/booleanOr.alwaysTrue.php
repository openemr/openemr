<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/patient_list_creation.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/FhirSearchWhereClauseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/SearchFieldStatementResolver.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of \\|\\| is always true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
