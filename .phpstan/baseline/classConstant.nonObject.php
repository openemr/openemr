<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_X12Partner.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Patient/Summary/Card/SectionEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot access constant class on array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationInsuranceService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
