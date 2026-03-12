<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccdaservice/ccda_gateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/display.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../config/services.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/C_EncounterVisitForm.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/common.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/delete.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/templates/linked_documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/Admin/TeleHealthUserAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthCalendarController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthVideoRegistrationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Repository/TeleHealthUserRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TeleHealthParticipantInvitationMailerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TeleHealthRemoteRegistrationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/tests/Tests/Unit/TeleHealthVideoRegistrationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncountermanagerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Listener/CCDAEventsSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceDocumentRequestor.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncountermanagerTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/CodeTypes/src/CodeTypes/Listener/CodeTypeEventsSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/FHIR/src/FHIR/Listener/CalculatedObservationEventsSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_health_concerns.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/amc_full_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/cdr_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/rules/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/templates/field_html_display_section.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/messages/validate_messages_document_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/classes/postmaster.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/Amc/library/AbstractAmcReport.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/ReportManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/pnotes.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/relation_display.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/relation_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/index_reset.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/sign/assets/signer_modal.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerLog.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerReview.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OneTimeAuth.php',
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
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomPasswordGrant.php',
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
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/JsonWebKeySet.php',
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
    'path' => __DIR__ . '/../../src/Common/Forms/FormReportRenderer.php',
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
    'path' => __DIR__ . '/../../src/Common/Session/Predis/SentinelUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 19,
    'path' => __DIR__ . '/../../src/Common/Session/SessionUtil.php',
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
    'count' => 2,
    'path' => __DIR__ . '/../../src/FHIR/SMART/SMARTLaunchToken.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SphereRevert.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/ApiApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AppointmentRestController.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
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
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/CDADocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentTemplates/DocumentTemplateRender.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/DrugService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Document/BaseDocumentDownloader.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Document/BaseDocumentDownloader.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirExportJobService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirValueSetService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Questionnaire/FhirQuestionnaireFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Utils/SearchRequestNormalizer.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/UtilsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/AuthorizationGrantFlowTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/BulkAPITestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Common/Auth/OpenIDConnect/SMARTSessionTokenContextIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/Authorization/AuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/Authorization/BearerTokenAuthorizationStrategyTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/Authorization/LocalApiAuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirOrganizationRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirPatientRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirPractitionerRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/SMART/SMARTAuthorizationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/Subscriber/ApiResponseLoggerListenerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Cda/CdaValidateDocumentsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService8_0_0Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirAllergyIntoleranceServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirOrganizationServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceMappingTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPatientServiceQueryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirPractitionerServiceCrudTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationEmployerServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationPatientServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Questionnaire/FhirQuestionnaireFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Questionnaire/FhirQuestionnaireFormServiceUnitTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormServiceUnitTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/Modules/Carecoordination/Model/CcdaServiceDocumentRequestorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Auth/OpenIDConnect/Repositories/ScopeRepositoryTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Crypto/CryptoGenTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Controllers/Interface/Forms/Observation/ObservationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Services/FHIR/Utils/SearchRequestNormalizerTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
