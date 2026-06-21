<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/user_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is always falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template_ui.php',
];
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
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Expression in empty\\(\\) is not falsy\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationInsuranceService.php',
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
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
