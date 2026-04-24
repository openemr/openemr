<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../apis/dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccdaservice/ccda_gateway.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccr/display.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../contrib/util/ccda_import/import_ccda.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controller.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/report.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/common.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/delete.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/new.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/report.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/observation/save.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/handle_deletions.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/patient_portal.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/questionnaire_assessments.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/report.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/save.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/templates/linked_documents.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages-ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthVideoRegistrationController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleconferenceRoomController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Services/TelehealthConfigurationVerifier.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/AdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/UserContextController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/EventSubscriber/DornLabSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/public/index.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/api_onetime.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/phone-services/voice_webhook.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 17,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/TwilioSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/VoiceClient.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/AuthorizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/DownloadWenoPharmacies.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogDataInsert.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/PharmacyService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/WenoLogService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/WenoValidate.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/synch.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/ajax/reporting_period_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncountermanagerController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Listener/CCDAEventsSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncountermanagerTable.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/CodeTypes/src/CodeTypes/Listener/CodeTypeEventsSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/FHIR/src/FHIR/Listener/CalculatedObservationEventsSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment_cc.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_save.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_save.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/ereqs/ereq_universal_form.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/ereq_form.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/libs/labs_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/product_registration/product_registration_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/amc_full_report.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/cdr_log.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/smart/admin-client.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/smart/ehr-launch-client.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(ValueError\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/load_codes.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/rules/index.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/templates/field_html_display_section.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/npi_lookup.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/ssl_certificates_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/webhooks/payment/rainforest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/Api.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/immunization_export.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/messages/validate_messages_document_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/classes/postmaster.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/deletedrug.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/templates/address_save_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/relation_display.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/relation_form.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../oauth2/authorize.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/index_reset.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/lib/doc_lib.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/CacheMemCache.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/index.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteActivityViewController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/report/portal_patient_report.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/sign/assets/signer_modal.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sphere/token.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/BC/Crypto/Crypto.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/BC/Crypto/LegacyKeychainLoader.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerAjax.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/JsonWebKeyParser.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CreateReleaseChangelogCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/GenerateAccessTokenCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/RegisterApiTestClientCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/Runner/CommandRunner.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/SymfonyCommandRunner.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(ValueError\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Crypto/CryptoGen.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(ValueError\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Filesystem/SafeIncludeResolver.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRouteHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/SystemLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Person.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/Controllers/Interface/Forms/Observation/ObservationController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/ModulesApplication.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/CacheCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/DatabaseCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/FilesystemCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/InstallationCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/OAuthKeysCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Health/Check/SessionCheck.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Rainforest/Webhooks/Dispatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/SMARTAuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/TelemetryListener.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/TokenIntrospectionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Background/BackgroundServiceRunner.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/ContactAddressService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/ContactRelationService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/ContactService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ContactTelecomService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/Email/EmailTestService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Error\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirValidationService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ImageUtilities/HandleImageService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/LogoService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PatientAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientTransactionService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PersonPatientLinkService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/PersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/ProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/QdmBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/TelemetryService.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/AaLoginTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 14,
    'path' => __DIR__ . '/../../tests/Tests/E2e/BbCreateStaffTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../tests/Tests/E2e/CcCreatePatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../tests/Tests/E2e/DdOpenPatientTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 24,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EeCreateEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EmailSendTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/EmailTestServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FaxSmsEmailTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 42,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FfOpenEncounterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FrontPaymentCssContrastTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/GgUserMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/HhMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 18,
    'path' => __DIR__ . '/../../tests/Tests/E2e/IiPatientContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 42,
    'path' => __DIR__ . '/../../tests/Tests/E2e/JjEncounterContextMainMenuLinksTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/E2e/SvcCodeFinancialReportTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/ECQM/AllPatientsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/ConditionFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Twig/TwigTemplateCompilationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(ErrorException\\) would suppress ErrorException, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/ErrorHandlerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Error\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Encryption/Keys/KeychainTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceIntegrationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Observation/FhirObservationHistorySdohServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/api/InternalApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^catch \\(Throwable\\) would suppress Error, which is forbidden\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/api/InternalFhirTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
