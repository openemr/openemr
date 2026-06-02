<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccdaservice/ccda_gateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>remove\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:unsetSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/tabs/main.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/setup_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/appointments_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>remove\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:unsetSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_totp.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_totp.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>remove\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:unsetSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/auth.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/auth.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../src/Common/Auth/OneTimeAuth.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>remove\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:unsetSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomClientCredentialsGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomPasswordGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Csrf/CsrfUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/SessionTracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>clear\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use \\$session\\-\\>clear\\(\\) requires a writable session \\(setSessionReadOnly\\(false\\)\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/SessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>remove\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:unsetSession\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Session/SessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Session/SessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/test.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>remove\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:unsetSession\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/SMARTAuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/AuthorizationGrantFlowTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/ClientCredentialsGrantFlowTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../tests/Tests/Common/Auth/OpenIDConnect/SMARTSessionTokenContextIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Session/ReadAndCloseNativeSessionStorageTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>remove\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:unsetSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Session/SessionUtilReadAndCloseTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Session/SessionUtilReadAndCloseTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Session/SessionWrapperFactoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>remove\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:unsetSession\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Portal/PatientControllerSecurityTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Portal/PatientControllerSecurityTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/RestControllers/Authorization/BearerTokenAuthorizationStrategyTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/RestControllers/Authorization/LocalApiAuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/RestControllers/Authorization/OAuth2DiscoveryControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/RestControllers/Authorization/OAuth2PublicJsonWebKeyControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/RestControllers/Authorization/SkipAuthorizationStrategyTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>clear\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use \\$session\\-\\>clear\\(\\) requires a writable session \\(setSessionReadOnly\\(false\\)\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/RestControllers/SMART/SMARTAuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/RestControllers/SMART/SMARTAuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/RestControllers/Subscriber/ApiResponseLoggerListenerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/RestControllers/Subscriber/SessionCleanupListenerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/Authorization/AuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>clear\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use \\$session\\-\\>clear\\(\\) requires a writable session \\(setSessionReadOnly\\(false\\)\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirQuestionnaireResponseRestControllerIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirQuestionnaireResponseRestControllerIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>clear\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use \\$session\\-\\>clear\\(\\) requires a writable session \\(setSessionReadOnly\\(false\\)\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirQuestionnaireRestControllerIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirQuestionnaireRestControllerIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Services/Common/Layouts/FieldRenderingSnapshotTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>clear\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use \\$session\\-\\>clear\\(\\) requires a writable session \\(setSessionReadOnly\\(false\\)\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>clear\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use \\$session\\-\\>clear\\(\\) requires a writable session \\(setSessionReadOnly\\(false\\)\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Questionnaire/FhirQuestionnaireFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Questionnaire/FhirQuestionnaireFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>clear\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use \\$session\\-\\>clear\\(\\) requires a writable session \\(setSessionReadOnly\\(false\\)\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>clear\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use \\$session\\-\\>clear\\(\\) requires a writable session \\(setSessionReadOnly\\(false\\)\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/ObservationServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Services/ObservationServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Logging/EventAuditLoggerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>clear\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use \\$session\\-\\>clear\\(\\) requires a writable session \\(setSessionReadOnly\\(false\\)\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Controllers/Interface/Forms/Observation/ObservationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Controllers/Interface/Forms/Observation/ObservationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct \\$session\\-\\>set\\(\\) is forbidden — it silently fails on read_and_close sessions\\. Use SessionUtil\\:\\:setSession\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/FHIR/SMART/ExternalClinicalDecisionSupport/RouteControllerTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
