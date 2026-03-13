<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getDbalConnection\\(\\) of class OpenEMR\\\\BC\\\\Database\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnAPI.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getGlobalSettingSectionConfiguration\\(\\) of class OpenEMR\\\\Modules\\\\WenoModule\\\\WenoGlobalConfig\\:
Left for legacy purposes and replaced by installation set up\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class Carecoordination\\\\Model\\\\CcdaServiceDocumentRequestor\\:
read from the \\-\\>logger property$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceDocumentRequestor.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Grant\\\\CustomAuthCodeGrant\\:
read from the \\-\\>logger property$#',
    'count' => 10,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomAuthCodeGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Grant\\\\CustomClientCredentialsGrant\\:
read from the \\-\\>logger property$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomClientCredentialsGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Grant\\\\CustomRefreshTokenGrant\\:
read from the \\-\\>logger property$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomRefreshTokenGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\IdTokenSMARTResponse\\:
read from the \\-\\>logger property$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/IdTokenSMARTResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\AccessTokenRepository\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/AccessTokenRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ClientRepository\\:
read from the \\-\\>logger property$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\RefreshTokenRepository\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/RefreshTokenRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ScopeRepository\\:
read from the \\-\\>logger property$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\UserRepository\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/UserRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\SMARTSessionTokenContextBuilder\\:
read from the \\-\\>logger property$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/SMARTSessionTokenContextBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Http\\\\HttpRestParsedRoute\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestParsedRoute.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getRequestMethod\\(\\) of class OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:
use getMethod\\(\\) instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Http\\\\HttpSessionFactory\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Http/HttpSessionFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Controllers\\\\Interface\\\\Forms\\\\Observation\\\\ObservationController\\:
read from the \\-\\>logger property$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\ApiApplication\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ApiApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\Authorization\\\\BearerTokenAuthorizationStrategy\\:
read from the \\-\\>logger property$#',
    'count' => 25,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\Authorization\\\\BearerTokenAuthorizationStrategy\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\AuthorizationController\\:
read from the \\-\\>logger property$#',
    'count' => 63,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Grant\\\\CustomAuthCodeGrant\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Grant\\\\CustomClientCredentialsGrant\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\IdTokenSMARTResponse\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\AccessTokenRepository\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ClientRepository\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\RefreshTokenRepository\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ScopeRepository\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\UserRepository\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\TokenIntrospectionRestController\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCareTeamRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPatientRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method requestHasScope\\(\\) of class OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:
use requestHasScopeEntity\\(\\) instead which receives a ScopeEntity object$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ClientRepository\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/SMARTAuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\Subscriber\\\\ApiResponseLoggerListener\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/ApiResponseLoggerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\Authorization\\\\SkipAuthorizationStrategy\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/AuthorizationListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\AuthorizationController\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/OAuth2AuthorizationListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\Subscriber\\\\RoutesExtensionListener\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/RoutesExtensionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\Subscriber\\\\SiteSetupListener\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\TokenIntrospectionRestController\\:
read from the \\-\\>logger property$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/RestControllers/TokenIntrospectionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ClientRepository\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TokenIntrospectionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\Cda\\\\CdaValidateDocuments\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirPatientDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirConditionService\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirDocRefService\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGoalService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirOrganizationService\\:
read from the \\-\\>logger property$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerRoleService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirRelatedPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\ObservationService\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
read from the \\-\\>logger property$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\PatientAdvanceDirectiveService\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PatientAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method getSystemLogger\\(\\) of class OpenEMR\\\\Tests\\\\Api\\\\ApiTestClient\\:
read from the \\-\\>logger property$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ClientRepository\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\AuthorizationController\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/AuthorizationGrantFlowTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ClientRepository\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/BulkAPITestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\IdTokenSMARTResponse\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Common/Auth/OpenIDConnect/SMARTSessionTokenContextIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\SMARTSessionTokenContextBuilder\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Common/Auth/OpenIDConnect/SMARTSessionTokenContextIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\AuthorizationController\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/Authorization/AuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\RestControllers\\\\Subscriber\\\\ApiResponseLoggerListener\\:
use setLogger\\(\\)$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/Subscriber/ApiResponseLoggerListenerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\Cda\\\\CdaValidateDocuments\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Cda/CdaValidateDocumentsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService8_0_0Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirAllergyIntoleranceServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPractitionerServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Questionnaire/FhirQuestionnaireFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Questionnaire/FhirQuestionnaireFormServiceUnitTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceUnitTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class Carecoordination\\\\Model\\\\CcdaServiceDocumentRequestor\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Modules/Carecoordination/Model/CcdaServiceDocumentRequestorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\IdTokenSMARTResponse\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Auth/OpenIDConnect/IdTokenSMARTResponseTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ScopeRepository\\:
use setLogger\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Auth/OpenIDConnect/Repositories/ScopeRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method requestHasScope\\(\\) of class OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:
use requestHasScopeEntity\\(\\) instead which receives a ScopeEntity object$#',
    'count' => 12,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Http/HttpRestRequestTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setSystemLogger\\(\\) of class OpenEMR\\\\Controllers\\\\Interface\\\\Forms\\\\Observation\\\\ObservationController\\:
use setLogger\\(\\)$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Controllers/Interface/Forms/Observation/ObservationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to deprecated method setAccessTokenScopes\\(\\) of class OpenEMR\\\\Common\\\\Http\\\\HttpRestRequest\\:
use setAccessTokenScopeValidationArray\\(\\) instead which receives a ResourceScopeEntityList\\[\\] that is built from the ScopeRepository\\-\\>buildValidationArray$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Services/FHIR/Utils/SearchRequestNormalizerTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
