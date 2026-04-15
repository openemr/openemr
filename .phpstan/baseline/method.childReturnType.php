<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Return type \\(void\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:index\\(\\) should be compatible with return type \\(null\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\AppDispatch\\:\\:index\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(string\\|null\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:index\\(\\) should be covariant with return type \\(null\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\AppDispatch\\:\\:index\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\<string\\>\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Events\\\\NotificationEventListener\\:\\:getSubscribedEvents\\(\\) should be covariant with return type \\(array\\<string, list\\<array\\{0\\: string, 1\\?\\: int\\}\\|int\\|string\\>\\|string\\>\\) of method Symfony\\\\Component\\\\EventDispatcher\\\\EventSubscriberInterface\\:\\:getSubscribedEvents\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(void\\) of method Carecoordination\\\\Controller\\\\EncounterccdadispatchController\\:\\:indexAction\\(\\) should be compatible with return type \\(Laminas\\\\View\\\\Model\\\\ViewModel\\) of method Laminas\\\\Mvc\\\\Controller\\\\AbstractActionController\\:\\:indexAction\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\<string, mixed\\>\\) of method OpenEMR\\\\ZendModules\\\\PatientFlowBoard\\\\Listener\\\\PatientFlowBoardEventsSubscriber\\:\\:getSubscribedEvents\\(\\) should be covariant with return type \\(array\\<string, list\\<array\\{0\\: string, 1\\?\\: int\\}\\|int\\|string\\>\\|string\\>\\) of method Symfony\\\\Component\\\\EventDispatcher\\\\EventSubscriberInterface\\:\\:getSubscribedEvents\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PatientFlowBoard/src/PatientFlowBoard/Listener/PatientFlowBoardEventsSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(Laminas\\\\Stdlib\\\\ResponseInterface\\) of method Patientvalidation\\\\Controller\\\\PatientvalidationController\\:\\:indexAction\\(\\) should be covariant with return type \\(Laminas\\\\View\\\\Model\\\\ViewModel\\) of method Laminas\\\\Mvc\\\\Controller\\\\AbstractActionController\\:\\:indexAction\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/PatientvalidationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mysqli\\) of method DataDriverMySQLi\\:\\:Open\\(\\) should be covariant with return type \\(connection\\) of method IDataDriver\\:\\:Open\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQLi.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ClientEntity\\|false\\) of method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ClientRepository\\:\\:getClientEntity\\(\\) should be covariant with return type \\(League\\\\OAuth2\\\\Server\\\\Entities\\\\ClientEntityInterface\\|null\\) of method League\\\\OAuth2\\\\Server\\\\Repositories\\\\ClientRepositoryInterface\\:\\:getClientEntity\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\) of method OpenEMR\\\\Services\\\\DocumentService\\:\\:search\\(\\) should be covariant with return type \\(OpenEMR\\\\Validators\\\\ProcessingResult\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\) of method OpenEMR\\\\Services\\\\EncounterService\\:\\:search\\(\\) should be covariant with return type \\(OpenEMR\\\\Validators\\\\ProcessingResult\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionHealthConcernService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionProblemListItemService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportClinicalNotesService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportLaboratoryService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirClinicalNotesService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirDocumentReferenceAdvanceCareDirectiveService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAllergyIntolerance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAllergyIntoleranceService\\:\\:createProvenanceResource\\(\\) should be compatible with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAllergyIntoleranceService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCarePlanService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCareTeam\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCareTeamService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCareTeamService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirConditionService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCoverageService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCoverageService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirEncounterService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirGoalService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGoalService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirImmunizationService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirLocationService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedia\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMediaService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMediaService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMediaService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMediaService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationRequestService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationRequestService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationRequestService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:getProfileURIs\\(\\) should be covariant with return type \\(array\\<string\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceUSCIGProfileService\\:\\:getProfileURIs\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPersonService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPractitionerRoleService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerRoleService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPractitionerService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirProcedureService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirSpecimenService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirSpecimenService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirSpecimenService\\:\\:getProfileURIs\\(\\) should be covariant with return type \\(array\\<string\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceUSCIGProfileService\\:\\:getProfileURIs\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirSpecimenService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationDispense\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\MedicationDispense\\\\FhirMedicationDispenseLocalDispensaryService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationCareExperiencePreferenceService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationCareExperiencePreferenceService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationEmployerService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationEmployerService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationHistorySdohService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationHistorySdohService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationLaboratoryService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationLaboratoryService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationObservationFormService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationObservationFormService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationQuestionnaireItemService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationQuestionnaireItemService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationSocialHistoryService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationSocialHistoryService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationTreatmentInterventionPreferenceService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationTreatmentInterventionPreferenceService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationVitalsService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationVitalsService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationFacilityService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationFacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationInsuranceService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationInsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationProcedureProviderService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationProcedureProviderService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureOEProcedureService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureOEProcedureService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureSurgeryService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureSurgeryService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Questionnaire\\\\FhirQuestionnaireFormService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Questionnaire/FhirQuestionnaireFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Questionnaire\\\\FhirQuestionnaireFormService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Questionnaire/FhirQuestionnaireFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\QuestionnaireResponse\\\\FhirQuestionnaireResponseFormService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\QuestionnaireResponse\\\\FhirQuestionnaireResponseFormService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\ObservationService\\:\\:createResultRecordFromDatabaseResult\\(\\) should be covariant with return type \\(array\\<string, mixed\\>\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:createResultRecordFromDatabaseResult\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\Services\\\\Search\\\\BasicSearchField\\:\\:getField\\(\\) should be covariant with return type \\(string\\) of method OpenEMR\\\\Services\\\\Search\\\\ISearchField\\:\\:getField\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/BasicSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\) of method OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\:\\:setRenderOutputToScreen\\(\\) should be covariant with return type \\(\\$this\\(OpenEMR\\\\Services\\\\Utils\\\\Interfaces\\\\ISQLUpgradeService\\)\\) of method OpenEMR\\\\Services\\\\Utils\\\\Interfaces\\\\ISQLUpgradeService\\:\\:setRenderOutputToScreen\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\) of method OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\:\\:setThrowExceptionOnError\\(\\) should be covariant with return type \\(\\$this\\(OpenEMR\\\\Services\\\\Utils\\\\Interfaces\\\\ISQLUpgradeService\\)\\) of method OpenEMR\\\\Services\\\\Utils\\\\Interfaces\\\\ISQLUpgradeService\\:\\:setThrowExceptionOnError\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
