<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../apis/dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drugs.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/LBF/printable.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/taskman_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/language/csv/translation_utilities.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pntemplates/default/views/monthSelector.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ConnectorApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/DornGenHl7Order.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/FaxDocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 55,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/AuthorizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/ModuleManagerListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/file_download.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/WenoPharmaciesJson.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/WenoValidate.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/synch.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/ajax/reporting_period_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/find_code_dynamic_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/manage_site_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/usergroup/npi_lookup.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Api.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/login_counter_ip_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Totp.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/postmaster.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/thumbnail/Thumbnail.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/ThumbnailGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/formdata.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/sanitize.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../oauth2/authorize.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/index_reset.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/get_patient_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/doc_lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Authentication/Authenticator.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/Context.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../setup.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/X125010837P.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthHash.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Auth/MfaUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OAuth2KeyConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Auth/UuidUserAccount.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../src/Common/Crypto/CryptoGen.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Common/Csrf/CsrfUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Session/PatientSessionUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Common/Session/SessionTracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/System/System.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Common/Utils/RandomGenUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Uuid/UuidRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Core/ModulesApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/CqmClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/Generator.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Easipro/Easipro.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/TelemetryListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/DocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/ImageUtilities/HandleImageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/LogoService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/ProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/Qdm/QdmBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractQdmService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/QrdaReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Telemetry/GeoTelemetry.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/ApiTestClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/ECQM/AllPatientsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/ConditionFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Use OpenEMR\\\\Common\\\\Logging\\\\SystemLogger instead of error_log\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
