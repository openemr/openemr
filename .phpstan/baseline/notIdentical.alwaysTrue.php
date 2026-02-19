<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between float and 0 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/edit_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between null and mixed will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between string and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxStore.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between array\\<array\\<string, mixed\\>\\> and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between int and \'0\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between int\\<0, max\\>\\|false and true will always evaluate to true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between \'text\\-muted\'\\|\'text\\-muted fa\\-xs\' and \'\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/tabs/templates/patient_data_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between ADORecordSet and false will always evaluate to true\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Services/DashboardContextAdminService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between ADORecordSet and false will always evaluate to true\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Services/DashboardContextService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between int and false will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceDocumentRequestor.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between Application\\\\Model\\\\type and false will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between non\\-falsy\\-string and false will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between 0\\.0\\|\'\'\\|false and \'0\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between string and false will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between string and false will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_278_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between bool and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between non\\-falsy\\-string and \'\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between string and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between Phreezable and false will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between int and false will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between ADORecordSet and false will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between \\*NEVER\\* and false will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/Types/LocalProviderListType.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between ADORecordSet and false will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between SimpleXMLElement and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/Generator.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between non\\-falsy\\-string and \'\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/CacheCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between array and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between int and \'200\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCode and null will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeableConcept and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCoding and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRPeriod and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirRelatedPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between string and false will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirUrlResolver.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRString and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTiming and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between mixed and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between mixed and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\Services\\\\Globals\\\\Effective and string will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between ADORecordSet and false will always evaluate to true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/ProcedureOrderRelationshipService.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between mixed and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/CqmCalculator.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between mixed and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/GeoTelemetry.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between null and false will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FaxSmsEmailTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCode and null will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBase64Binary and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCode and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCoding and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExtension and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRReference and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRString and null will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUri and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUrl and null will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDocumentReference\\\\FHIRDocumentReferenceContext and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRPeriod and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirCareTeamServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRReference and null will always evaluate to true\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirCareTeamServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRString and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirCareTeamServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeableConcept and null will always evaluate to true\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDateTime and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuantity and null will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRReference and null will always evaluate to true\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRString and null will always evaluate to true\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUnsignedInt and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRMedicationRequest\\\\FHIRMedicationRequestDispenseRequest and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRTiming and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExtension and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Strict comparison using \\!\\=\\= between OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRInstant and null will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
