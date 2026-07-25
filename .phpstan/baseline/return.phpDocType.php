<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type an is incompatible with native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/review/fee_sheet_options_queries.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type base is incompatible with native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type int is incompatible with native type float\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
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
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type string\\|void is not subtype of native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type int\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/AuthorizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type array is incompatible with native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php',
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
    'message' => '#^PHPDoc tag @return with type bool is incompatible with native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type Patientvalidation\\\\Controller\\\\post is incompatible with native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/BaseController.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type ADORecordSet_mysqli is incompatible with native type array\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_models/group_statuses_model.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type ADORecordSet_mysqli is incompatible with native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_models/therapy_groups_encounters_model.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type array\\|bool is incompatible with native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
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
    'message' => '#^PHPDoc tag @return with type array is incompatible with native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type array is incompatible with native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type string is incompatible with native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type string is incompatible with native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/formdata.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type array\\|bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/lab.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type float\\|int is incompatible with native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type void is incompatible with native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3/Error.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type CSV is incompatible with native type string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type true is incompatible with native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingClaim.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormQuestionnaireAssessment.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type array\\|null is not subtype of native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Encounter/LoadEncounterFormFilterEvent.php',
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
    'message' => '#^PHPDoc tag @return with type string is incompatible with native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Pharmacy/Services/ImportPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type void is incompatible with native type Symfony\\\\Component\\\\HttpFoundation\\\\Response\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ApiApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type string is incompatible with native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type int\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type array\\|bool\\|null is not subtype of native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type array\\|bool\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateService.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\FHIR\\\\Export\\\\ExportJob is incompatible with native type bool\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirExportJobService.php',
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
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Services\\\\Utils\\\\SQLStatement is incompatible with native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
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
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\matching is incompatible with native type array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return with type OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\the is incompatible with native type string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
