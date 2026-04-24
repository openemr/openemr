<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\ClaimRevConnector\\\\Bootstrap\\:\\:getAssetPath\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\Dorn\\\\Bootstrap\\:\\:getAssetPath\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:formatFaxDataUrl\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:downloadFaxMediaContent\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\SignalWireClient\\:\\:storeInboundFax\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Carecoordination\\\\Model\\\\EncounterccdadispatchTable\\:\\:is_snomed_codes_installed\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\ZendModules\\\\FHIR\\\\Listener\\\\UuidMappingEventsSubscriber\\:\\:getPatientResourcePathForCode\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/FHIR/src/FHIR/Listener/UuidMappingEventsSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Controller\\\\InstallerController\\:\\:getContent\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Installer\\\\Controller\\\\InstallerController\\:\\:installACL\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method MedExApi\\\\Events\\:\\:getDatesInRecurring\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Method ObserveToFile\\:\\:FormatTrace\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ObserveToFile.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Phreezer\\:\\:SetLevel1CacheProvider\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\AccessTokenEntity\\:\\:convertToJWT\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Entities/AccessTokenEntity.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Command\\\\CreateReleaseChangelogCommand\\:\\:getTestIssues\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CreateReleaseChangelogCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Common\\\\Command\\\\CreateReleaseChangelogCommand\\:\\:getTestMilestoneNumberFromName\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CreateReleaseChangelogCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Controllers\\\\Interface\\\\Forms\\\\Observation\\\\ObservationController\\:\\:getFormJumpHtml\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Controllers\\\\Interface\\\\Forms\\\\Observation\\\\ObservationController\\:\\:getQuestionnaireResponseDetails\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\AbstractModuleActionListener\\:\\:disable\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\AbstractModuleActionListener\\:\\:enable\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\AbstractModuleActionListener\\:\\:install\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\AbstractModuleActionListener\\:\\:install_sql\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\AbstractModuleActionListener\\:\\:unregister\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Core\\\\AbstractModuleActionListener\\:\\:upgrade_sql\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Patient\\\\Cards\\\\InsuranceViewCard\\:\\:getInsuranceTypeArray\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/InsuranceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\Cda\\\\ClinicalNoteParser\\:\\:innerXML\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/ClinicalNoteParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirCareTeamService\\:\\:populatePatientMember\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\FhirSpecimenService\\:\\:matchesRequestedStatus\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirSpecimenService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationLaboratoryService\\:\\:getDescriptionForCode\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationTreatmentInterventionPreferenceService\\:\\:getPatientUuidFromPid\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureOEProcedureService\\:\\:normalizeProcedureCodingSystem\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\ObservationLabService\\:\\:getSampleLaboratoryResults\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationLabService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\SDOH\\\\HistorySdohService\\:\\:ccText\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\SDOH\\\\HistorySdohService\\:\\:codeFromListOption\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Services\\\\SDOH\\\\HistorySdohService\\:\\:parseCodeFromRecord\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Fixtures\\\\PractitionerFixtureManager\\:\\:getNextId\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/PractitionerFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test\\:\\:findCategoryByCode\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\FhirCareTeamServiceUSCore8Test\\:\\:findParticipantByMemberType\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirCareTeamServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Integration\\\\Services\\\\FHIR\\\\FhirLocationServiceIntegrationTest\\:\\:createTestFacility\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Integration\\\\Services\\\\FHIR\\\\FhirLocationServiceIntegrationTest\\:\\:createTestPatientLocation\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Integration\\\\Services\\\\FHIR\\\\FhirLocationServiceIntegrationTest\\:\\:createTestPatient\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Integration\\\\Services\\\\FHIR\\\\FhirLocationServiceIntegrationTest\\:\\:getFacilityLocationUuid\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Integration\\\\Services\\\\FHIR\\\\FhirLocationServiceIntegrationTest\\:\\:parseAndValidateExportedResources\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\FhirLocationServiceTest\\:\\:createMockFhirLocation\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveServiceUSCore8Test\\:\\:findCategoryBySystem\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveServiceUSCore8Test\\:\\:findExtensionByUrl\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationAdvanceDirectiveServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Method OpenEMR\\\\Tests\\\\Services\\\\Modules\\\\CareCoordination\\\\Model\\\\CcdaServiceDocumentRequestorTest\\:\\:getDocumentGenerationTime\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Modules/Carecoordination/Model/CcdaServiceDocumentRequestorTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
