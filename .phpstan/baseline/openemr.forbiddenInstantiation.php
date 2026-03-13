<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../config/services.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/C_EncounterVisitForm.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceDocumentRequestor.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/index_reset.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/FhirUserClaim.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomAuthCodeGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomClientCredentialsGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomRefreshTokenGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/IdTokenSMARTResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/AccessTokenRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/RefreshTokenRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/UserRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/SMARTSessionTokenContextBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestParsedRoute.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpSessionFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ResourceConstraintFilterer.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ApiApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/SkipAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirOrganizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPatientRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDefinitionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/RestControllers/RestControllerHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/SMARTAuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/ApiResponseLoggerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/RoutesExtensionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TokenIntrospectionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TokenIntrospectionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Document/BaseDocumentDownloader.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Common/Auth/OpenIDConnect/SMARTSessionTokenContextIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Crypto/CryptoGenTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
