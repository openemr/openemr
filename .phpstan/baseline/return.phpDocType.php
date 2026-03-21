<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\DateTime\\|null is not subtype of native type DateTime\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Models/TeleHealthUser.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type DateTime\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Models/TeleHealthUser.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type bool\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/ClickatellSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type bool\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type int\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/AuthorizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type void is incompatible with native type array\\|null\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type void is incompatible with native type never\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type array is incompatible with native type AmcItemizedActionData\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/library/AMC_Unimplemented.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type array is incompatible with native type AmcItemizedActionData\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/reports/AMC_315g_2c/Numerator.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type array is incompatible with native type AmcItemizedActionData\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/reports/AMC_315g_7/Numerator.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type void is incompatible with native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3/Error.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaim.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type string\\|null is incompatible with native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/BaseActionButtonHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Events\\\\UserInterface\\\\UserEditRenderEvent is not subtype of native type OpenEMR\\\\Events\\\\UserInterface\\\\PageHeadingRenderEvent\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/PageHeadingRenderEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type array\\|null is not subtype of native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/UserInterface/PageHeadingRenderEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type array is incompatible with native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareTeamViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type void is incompatible with native type Symfony\\\\Component\\\\HttpFoundation\\\\Response\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ApiApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Validators\\\\ProcessingResult is incompatible with native type never\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Services\\\\Search\\\\ISearchField\\|null is not subtype of native type OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/IFhirExportableResourceService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\|string\\|null is not subtype of native type OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\|string\\|null is not subtype of native type OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\|string\\|null is not subtype of native type OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\|string\\|null is not subtype of native type OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\|string\\|null is not subtype of native type OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\|string\\|null is not subtype of native type OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type string is incompatible with native type bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ImageUtilities/HandleImageService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type string\\|null is not subtype of native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/LogoService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type string is incompatible with native type OpenEMR\\\\Services\\\\Search\\\\SearchQueryFragment\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/FhirSearchWhereClauseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type array is incompatible with native type void\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/AuthorizationGrantFlowTest.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type string is incompatible with native type stdClass\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/BulkPatientExport311APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\the is incompatible with native type string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
