<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Property C_Document\\:\\:\\$Document is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Property C_Pharmacy\\:\\:\\$pageno is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Forms\\\\NewPatient\\\\C_EncounterVisitForm\\:\\:\\$pageName is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/C_EncounterVisitForm.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Forms\\\\NewPatient\\\\C_EncounterVisitForm\\:\\:\\$rootdir is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/C_EncounterVisitForm.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Controller\\\\TeleconferenceRoomController\\:\\:\\$telehealthRegistrationController is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Comlink\\\\OpenEMR\\\\Modules\\\\TeleHealthModule\\\\Services\\\\TelehealthConfigurationVerifier\\:\\:\\$httpClient is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TelehealthConfigurationVerifier.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\DashboardContext\\\\Bootstrap\\:\\:\\$moduleDirectoryName is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\DashboardContext\\\\Bootstrap\\:\\:\\$modulePath is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\EhiExporter\\\\Bootstrap\\:\\:\\$logger is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\EhiExporter\\\\Bootstrap\\:\\:\\$twig is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\EhiExporter\\\\GlobalConfig\\:\\:\\$cryptoGen is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/GlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\EhiExporter\\\\Services\\\\EhiExporter\\:\\:\\$modulePublicDir is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\EhiExporter\\\\Services\\\\EhiExporter\\:\\:\\$modulePublicUrl is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Property ModuleManagerListener\\:\\:\\$authUser is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\AppDispatch\\:\\:\\$_cookies is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:\\$appKey is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:\\$appSecret is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:\\$sid is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:\\$apiToken is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:\\$projectId is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:\\$spaceUrl is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\TwilioSMSClient\\:\\:\\$accountSID is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/TwilioSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Static property OpenEMR\\\\Modules\\\\FaxSMS\\\\EtherFax\\\\EtherFaxClient\\:\\:\\$timeZone is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/EtherFax/EtherFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Bootstrap\\:\\:\\$logger is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Bootstrap\\:\\:\\$moduleDirectoryName is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\LogImportBuild\\:\\:\\$insertdata is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogImportBuild.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\TransmitProperties\\:\\:\\$csrf is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\TransmitProperties\\:\\:\\$encounter is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\TransmitProperties\\:\\:\\$ncpdp is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\TransmitProperties\\:\\:\\$subscriber is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\TransmitProperties\\:\\:\\$wenoProviderID is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\WenoGlobalConfig\\:\\:\\$cryptoGen is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/WenoGlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Application\\\\Plugin\\\\Phimail\\:\\:\\$listenerObject is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Plugin/Phimail.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Carecoordination\\\\Model\\\\CarecoordinationTable\\:\\:\\$codeService is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Property ESign\\\\ESign\\:\\:\\$_configuration is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/ESign.php',
];
$ignoreErrors[] = [
    'message' => '#^Property ClinicalType\\:\\:\\$_title is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/ClinicalTypes/ClinicalType.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Prescription\\:\\:\\$erx_source is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Prescription\\:\\:\\$persisted_values is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Property CacheMemCache\\:\\:\\$_lockFilePath is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/CacheMemCache.php',
];
$ignoreErrors[] = [
    'message' => '#^Property CacheNoCache\\:\\:\\$ram is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/CacheNoCache.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\Auth\\\\AuthUtils\\:\\:\\$otherAuth is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\Auth\\\\MfaUtils\\:\\:\\$var1U2F is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/MfaUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Grant\\\\CustomClientCredentialsGrant\\:\\:\\$authTokenUrl is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomClientCredentialsGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Grant\\\\CustomRefreshTokenGrant\\:\\:\\$session is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomRefreshTokenGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\IdTokenSMARTResponse\\:\\:\\$isAuthorizationGrant is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/IdTokenSMARTResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\Forms\\\\FormReportRenderer\\:\\:\\$locator is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormReportRenderer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Core\\\\AbstractModuleActionListener\\:\\:\\$_cookies is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\SMART\\\\ExternalClinicalDecisionSupport\\\\RouteController\\:\\:\\$repo is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\PaymentProcessing\\\\Rainforest\\\\Api\\:\\:\\$platformId is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Rainforest/Api.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\ApiApplication\\:\\:\\$webroot is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ApiApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\Authorization\\\\BearerTokenAuthorizationStrategy\\:\\:\\$globalsBag is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Static property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$localCall is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\EncounterRestController\\:\\:\\$session is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/EncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirPersonRestController\\:\\:\\$fhirValidate is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\FHIR\\\\FhirProvenanceRestController\\:\\:\\$serviceLocator is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProvenanceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\SMART\\\\PatientContextSearchController\\:\\:\\$logger is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/PatientContextSearchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\SMART\\\\SMARTAuthorizationController\\:\\:\\$oauthTemplateDir is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/SMARTAuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\RestControllers\\\\SMART\\\\ScopePermissionParser\\:\\:\\$scopeRepository is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/ScopePermissionParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\CarePlanService\\:\\:\\$codeTypesService is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\Cda\\\\CdaTemplateImportDispose\\:\\:\\$currentEncounter is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\Cda\\\\ClinicalNoteParser\\:\\:\\$xml is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/ClinicalNoteParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\FHIR\\\\FhirConditionService\\:\\:\\$conditionService is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\FHIR\\\\FhirLocationService\\:\\:\\$organizationService is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationDispenseService\\:\\:\\$innerServices is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\FHIR\\\\FhirObservationService\\:\\:\\$innerServices is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:\\$searchParameters is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\FHIR\\\\FhirSpecimenService\\:\\:\\$procedureService is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirSpecimenService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Api\\\\CapabilityFhirTest\\:\\:\\$baseUrl is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/CapabilityFhirTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Api\\\\CapabilityFhirTest\\:\\:\\$oauthBaseUrl is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/CapabilityFhirTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Static property OpenEMR\\\\Tests\\\\Certification\\\\HIT1\\\\G9_Certification\\\\CCDADocRefGenerationTest\\:\\:\\$baseUrl is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Fixtures\\\\FixtureManager\\:\\:\\$contactFixtures is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/FixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Isolated\\\\Billing\\\\SimpleBillingClaimMock\\:\\:\\$claimId is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Billing/BillingClaimBatchTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Isolated\\\\Core\\\\Traits\\\\SingletonC\\:\\:\\$argument is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/Traits/SingletonTraitTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\RestControllers\\\\FHIR\\\\FhirQuestionnaireResponseRestControllerIntegrationTest\\:\\:\\$questionnaireResponseTemplate is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirQuestionnaireResponseRestControllerIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\CarePlanServiceTest\\:\\:\\$fixture is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/CarePlanServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\EncounterServiceTest\\:\\:\\$fixture is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/EncounterServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test\\:\\:\\$compliantDnrOrderData is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test\\:\\:\\$compliantGenericAdiData is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test\\:\\:\\$compliantMentalHealthDirectiveData is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test\\:\\:\\$fixtureManager is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Integration\\\\Services\\\\FHIR\\\\FhirLocationServiceIntegrationTest\\:\\:\\$locationService is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\FhirPatientServiceCrudTest\\:\\:\\$patientFixture is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\FhirPatientServiceUSCore8Test\\:\\:\\$fixtureManager is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\FhirPractitionerServiceCrudTest\\:\\:\\$practitionerFixture is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPractitionerServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveServiceUSCore8Test\\:\\:\\$fixtureManager is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationEmployerServiceTest\\:\\:\\$userService is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationHistorySdohServiceTest\\:\\:\\$userService is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientServiceTest\\:\\:\\$createdRecords is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientServiceTest\\:\\:\\$listService is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientServiceTest\\:\\:\\$userService is never read, only written\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
