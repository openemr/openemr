<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php',
];
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../ccr/transmitCCD.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../contrib/util/installScripts/InstallerAuto.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_X12Partner.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/batchcom/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/customize_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/edi_271.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/couchdb/couchdb_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxGlobals.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRx_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
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
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/logview/logview.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/templates/linked_documents.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages-ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/GlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/ReportDownload.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/moduleConfig.php',
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
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Repository/TeleHealthSessionRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Repository/TeleHealthUserRepository.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/TelehealthGlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/TelehealthGlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Util/CalendarUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/tests/Tests/Unit/TeleHealthVideoRegistrationControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/GlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/GlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/TwilioSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/VoiceClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/file_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/ModuleService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/WenoGlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/indexrx.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/config/autoload/global.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
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
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaGenerator.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncountermanagerTable.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Model/MultipledbTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/education.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment_cc.php',
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
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_widget.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/letter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/product_registration/product_registration_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/amc_full_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appointments_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/audit_log_tamper_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/cdr_log.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/smart/admin-client.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/smart/ehr-launch-client.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/manage_document_templates.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/manage_site_files.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_totp.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/webhooks/payment/rainforest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/immunization_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/messages/validate_messages_document_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/track_events.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/udi.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/CouchDB.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/postmaster.php',
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
    'count' => 5,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
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
    'path' => __DIR__ . '/../../library/telemetry_reporting_service.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/templates/address_save_handler.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/batch_phone_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 40,
    'path' => __DIR__ . '/../../portal/account/account.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../portal/account/account.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/index_reset.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/account/register.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/track_portal_events.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/report/portal_patient_report.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/config.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sites/default/statement.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sphere/process_response.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/BillingLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/X12RemoteTracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EDI270.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
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
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Acl/AccessDeniedHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthGlobal.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/MfaUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OAuth2KeyConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OneTimeAuth.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OneTimeAuth.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
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
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/RsaSha384Signer.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/Validation/UniqueID.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/AccessTokenRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php',
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
    'path' => __DIR__ . '/../../src/Common/Database/QueryUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormLocator.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/EventAuditLogger.php',
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
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/OEHttpKernel.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/Export/ExportStreamWriter.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ResourceConstraintFilterer.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/FHIR/SMART/SMARTLaunchToken.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/FHIR/SMART/SMARTLaunchToken.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/MedicalDevice/MedicalDevice.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/PaymentProcessing/PaymentProcessing.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Rainforest/Api.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SphereRevert.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
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
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirRelatedPersonRestController.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 11,
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
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/AuthorizationListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/OAuth2AuthorizationListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/RoutesExtensionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TokenIntrospectionRestController.php',
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
    'path' => __DIR__ . '/../../src/Services/BaseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/CDADocumentService.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
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
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocRefService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirExportJobService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGoalService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGroupService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMediaService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
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
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirRelatedPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirServiceRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirSpecimenService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirValueSetService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
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
    'path' => __DIR__ . '/../../src/Services/FacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/InsuranceCompanyService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/JWTClientAuthenticationService.php',
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
    'path' => __DIR__ . '/../../src/Services/PersonPatientLinkService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PhoneNumberService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ProcedureProviderService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/ExportCat3Service.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/USPS/USPSAddressVerifyV3.php',
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
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/BulkPatientExport311APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatient311APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatient700APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatientApi/CapabilityStatementTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Http\\\\Psr17Factory is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:get\\{PsrType\\}Factory\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Common/Auth/OpenIDConnect/SMARTSessionTokenContextIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/BaseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/CarePlanFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/EncounterFixtureManager.php',
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
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirServiceRequestRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirPractitionerRestControllerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/RestControllers/FHIR/FhirQuestionnaireRestControllerIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 7,
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
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirServiceRequestServiceCrudTest.php',
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
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Http/HttpRestRouteHandlerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Crypto\\\\CryptoGen is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getCrypto\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Logging/EventAuditLoggerTest.php',
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
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/api/InternalApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct instantiation of OpenEMR\\\\Common\\\\Logging\\\\SystemLogger is discouraged\\. Use OpenEMR\\\\BC\\\\ServiceContainer\\:\\:getLogger\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/api/InternalFhirTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
