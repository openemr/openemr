<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactAddress.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ContactTelecom.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/UtilsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/FHIRSearchFieldFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/ProcessingResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
