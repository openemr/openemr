<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:addItem\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:addItem\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_DocumentCategory.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Pharmacy\\:\\:set_patient_id\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Pharmacy\\:\\:set_provider\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Pharmacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/batchcom/batch_navigation.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getThemesRelative\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/edih_view.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/print.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinical_notes/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/a_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getIncludeRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/delete.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:set_authorized\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:set_encounter\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:set_pid\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/authorizations/authorizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getProjectDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/tabs/main.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/tabs/templates/patient_data_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Symfony\\\\Contracts\\\\EventDispatcher\\\\EventDispatcherInterface\\:\\:addListener\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Events\\\\User\\\\UserUpdatedEvent\\:\\:getUsername\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/TeleHealthVideoRegistrationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Modules\\\\DashboardContext\\\\Services\\\\DashboardContextAdminService\\:\\:removeUserAssignment\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/AdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Symfony\\\\Contracts\\\\EventDispatcher\\\\EventDispatcherInterface\\:\\:addListener\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getAssetsRelative\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/messageUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Symfony\\\\Contracts\\\\EventDispatcher\\\\EventDispatcherInterface\\:\\:addListener\\(\\)\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Symfony\\\\Contracts\\\\EventDispatcher\\\\Event\\:\\:getPid\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Symfony\\\\Contracts\\\\EventDispatcher\\\\Event\\:\\:getRecipientPhone\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Symfony\\\\Contracts\\\\EventDispatcher\\\\EventDispatcherInterface\\:\\:addListener\\(\\)\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getVendorDir\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/config/application.config.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\RequestInterface\\:\\:getPost\\(\\)\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Controller/AclController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Application\\\\Controller\\\\IndexController\\:\\:CommonPlugin\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\RequestInterface\\:\\:getPost\\(\\)\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Application\\\\Controller\\\\SendtoController\\:\\:escapeHtml\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Application\\\\Model\\\\SendtoTable\\:\\:getCombinationFormComponents\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\RequestInterface\\:\\:getPost\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Ccr\\\\Controller\\\\CcrController\\:\\:CommonPlugin\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\RequestInterface\\:\\:getPost\\(\\)\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\RequestInterface\\:\\:getQuery\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Documents\\\\Controller\\\\DocumentsController\\:\\:Documents\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\ResponseInterface\\:\\:getHeaders\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\ResponseInterface\\:\\:setHeaders\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Immunization\\\\Controller\\\\ImmunizationController\\:\\:CommonPlugin\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Form\\\\ElementInterface\\:\\:setValueOptions\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\RequestInterface\\:\\:getPost\\(\\)\\.$#',
    'count' => 21,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Installer\\\\Model\\\\InstModuleTable\\:\\:DeleteAcl\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\RequestInterface\\:\\:getPost\\(\\)\\.$#',
    'count' => 24,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\RequestInterface\\:\\:isPost\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\RequestInterface\\:\\:getQuery\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/BaseController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\ResponseInterface\\:\\:setStatusCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/BaseController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Mvc\\\\Controller\\\\Plugin\\\\Layout\\|Laminas\\\\View\\\\Model\\\\ModelInterface\\:\\:setVariable\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/PatientvalidationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Laminas\\\\Stdlib\\\\RequestInterface\\:\\:getPost\\(\\)\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Controller/SyndromicsurveillanceController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Syndromicsurveillance\\\\Controller\\\\SyndromicsurveillanceController\\:\\:CommonPlugin\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Controller/SyndromicsurveillanceController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/birthday_alert/birthday_pop.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getIncludeRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/cash_receipt.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/delete_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/forms.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getAssetsRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getThemesRelative\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/letter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getThemesRelative\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/summary/labdata.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getIncludeRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/lbf_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getIncludeRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/vitals_fragment.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getThemesRelative\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_tracker/patient_tracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Psr\\\\Log\\\\LoggerInterface\\:\\:logError\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appointments_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/reports/patient_list_creation.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesAbsolute\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/smart/register-app.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/appointmentComponent.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsGeneralData.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/groupDetailsParticipants.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_views/listGroups.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Psr\\\\Log\\\\LoggerInterface\\:\\:logError\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/immunization_export.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getTemplateDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getVendorDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method HTML_TreeMenu_Presentation\\:\\:toHTML\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:_ensureVisible\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:Initialize\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/sasl.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:Start\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/sasl.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/custom_template/add_context.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/custom_template/ajax_code.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getAssetsRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/dicom_frame.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getAssetsRelative\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getAssetsRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.js.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getIncludeRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:_compile_file\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:get\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:set_file_contents\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\ContactService\\:\\:getContactsForPatient\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/address_list_display.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\ContactService\\:\\:getContactsForPatient\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/address_list_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getAssetsRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/validation/validation_script.js.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/validation/validation_script.js.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:htmlAttribs\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3/resources/Savant3_Plugin_image.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:IsTerminated\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Dispatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:GetPrimaryKeyValue\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:GetPrimaryKeyName\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:OnBeforeDelete\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:OnDelete\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:OnInsert\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:OnSave\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:OnUpdate\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Phreezer.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Patient\\:\\:GetValidationErrors\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Patient\\:\\:Save\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Patient\\:\\:Validate\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/AbstractGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesAbsolute\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorHCFA_PDF_IMG.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EDI270.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Omnipay\\\\Common\\\\GatewayInterface\\:\\:setApiKey\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Omnipay\\\\Common\\\\GatewayInterface\\:\\:setAuthName\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Omnipay\\\\Common\\\\GatewayInterface\\:\\:setTestMode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Omnipay\\\\Common\\\\GatewayInterface\\:\\:setTransactionKey\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/PaymentGateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method BaseController\\:\\:_action_default\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/ActionRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method BaseController\\:\\:getControllerName\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/ActionRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/ActionRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getIncludeRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Common.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getSrcDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Common.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getTemplateDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Common.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Common.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getProjectDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/CdrAlertManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getSrcDir\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleTemplateExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method League\\\\OAuth2\\\\Server\\\\Repositories\\\\UserRepositoryInterface\\:\\:getCustomUserEntityByUserCredentials\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomPasswordGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/IdTokenSMARTResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Lcobucci\\\\JWT\\\\Token\\:\\:claims\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/JsonWebKeyParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method phpseclib3\\\\Crypt\\\\Common\\\\AsymmetricKey\\:\\:withPadding\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/RsaSha384Signer.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Lcobucci\\\\JWT\\\\Token\\:\\:claims\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/JWT/Validation/UniqueID.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method GuzzleHttp\\\\Exception\\\\GuzzleException\\:\\:getResponse\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Command/CreateReleaseChangelogCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getSrcDir\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Common/Forms/CoreFormToPortalUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:getBody\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:getHeader\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:getHeaders\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:getStatusCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpResponse.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Common\\\\ORDataObject\\\\ORDataObject\\:\\:get_id\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ORDataObject.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Common\\\\Logging\\\\SystemLogger\\:\\:errorLogCaller\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/Predis/PredisSessionHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getProjectDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Twig/TwigContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getAssetsRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Twig/TwigExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Twig/TwigExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getSrcDir\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Common/Twig/TwigExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Twig/TwigExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getProjectDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/Header.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getIncludeRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/OEGlobalsBag.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getProjectDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/OEGlobalsBag.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getSrcDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/OEGlobalsBag.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Core/OEGlobalsBag.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getProjectDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Encounter/LoadEncounterFormFilterEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclAdminApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getProjectDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/MainMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getProjectDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/PatientMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Menu/PatientMenuRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareExperiencePreferenceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/PortalCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/TreatmentPreferenceViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SphereRevert.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getVendorDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Pdf/PdfCreator.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\FHIR\\\\IFhirExportableResourceService\\:\\:setServiceLocator\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationExportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/SMARTAuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Symfony\\\\Component\\\\HttpFoundation\\\\Request\\:\\:getHeader\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/CORSListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Symfony\\\\Component\\\\HttpFoundation\\\\Request\\:\\:getRequestMethod\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/TelemetryListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getProjectDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CDADocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method DOMNameSpaceNode\\|DOMNode\\:\\:getAttribute\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaComponentParseHelpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getProjectDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method DOMNameSpaceNode\\|DOMNode\\:\\:C14N\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/ClinicalNoteParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method DOMNameSpaceNode\\|DOMNode\\:\\:getAttribute\\(\\)\\.$#',
    'count' => 11,
    'path' => __DIR__ . '/../../src/Services/Cda/ClinicalNoteParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Validators\\\\ProcessingResult\\:\\:addValidationError\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Services/ContactRelationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Common\\\\ORDataObject\\\\Contact\\:\\:toArray\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ContactService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Validators\\\\ProcessingResult\\:\\:addProcessingError\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../src/Services/ContactService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDiagnosticReport\\:\\:setDate\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\Search\\\\ISearchField\\:\\:setModifier\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\Search\\\\ISearchField\\:\\:setModifier\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirPatientDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:supportsCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:supportsCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDiagnosticReportService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:supportsCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDocumentReferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:supportsCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationDispenseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:supportsCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:supportsCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\Search\\\\ISearchField\\:\\:hasCodeValue\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirValueSetService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Validators\\\\ProcessingResult\\:\\:addProcessingError\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/InsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Lcobucci\\\\JWT\\\\Token\\:\\:claims\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/JWTClientAuthenticationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesAbsolute\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/LogoService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/LogoService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\Search\\\\ISearchField\\:\\:getModifier\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:addCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:addCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:addCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/Services/AbstractObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Mustache_Context\\:\\:get\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat1.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Mustache_Context\\:\\:get\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qrda/Cat3.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:jsonSerialize\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method object\\:\\:jsonSerialize\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\Search\\\\TableSearchProcessor\\:\\:createResultRecordFromDatabaseResult\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/TableSearchProcessor.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\Search\\\\TableSearchProcessor\\:\\:getSelectJoinClauses\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Search/TableSearchProcessor.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\Search\\\\TableSearchProcessor\\:\\:getTable\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Search/TableSearchProcessor.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\Search\\\\TableSearchProcessor\\:\\:selectHelper\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Search/TableSearchProcessor.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\USPS\\\\USPSBase\\:\\:getPostFields\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/USPS/USPSBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Particle\\\\Validator\\\\Chain\\:\\:listOption\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Validators/CoverageValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method DOMNameSpaceNode\\|DOMNode\\:\\:getAttribute\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method DOMNameSpaceNode\\|DOMNode\\:\\:hasAttribute\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method DOMNameSpaceNode\\|DOMNode\\:\\:setAttribute\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Validators\\\\ProcessingResult\\:\\:getMessages\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/MedicationDispenseFixtureManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getAssetsRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesAbsolute\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getImagesRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getIncludeRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getProjectDir\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getRootDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getSiteDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getSiteWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getSitesBase\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getSrcDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getTemplateDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getThemesRelative\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getVendorDir\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Core/KernelPathsTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\SpreadSheetService\\|PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:buildSpreadsheet\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Services/SpreadSheetServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\SpreadSheetService\\|PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:downloadSpreadsheet\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Services/SpreadSheetServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\SpreadSheetService\\|PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:expects\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Services/SpreadSheetServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Lcobucci\\\\JWT\\\\Token\\:\\:claims\\(\\)\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Tools/OAuth2/ClientCredentialsAssertionGeneratorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:add_initial_user\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:add_version_info\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:create_database\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:create_database_user\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:create_site_directory\\(\\)\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:displayNewThemeDiv\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:displaySelectedThemeDiv\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:displayThemesDivs\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:drop_database\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:getCurrentTheme\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:get_initial_user_mfa_totp\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:grant_privileges\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:install_additional_users\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:install_gacl\\(\\)\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:listThemes\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:load_dumpfiles\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:on_care_coordination\\(\\)\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:quick_install\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:root_database_connection\\(\\)\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:setCurrentTheme\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:setupHelpModal\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:upsertCustomGlobals\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:user_database_connection\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method PHPUnit\\\\Framework\\\\MockObject\\\\MockObject\\:\\:write_configuration_file\\(\\)\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/library/classes/InstallerTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\:\\:getCategory\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\:\\:getClinicalStatus\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\:\\:getCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\:\\:getSubject\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\:\\:getVerificationStatus\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/Condition/FhirConditionService3_1_1Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Services\\\\Search\\\\ISearchField\\:\\:getChildren\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirLocationServiceTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\FHIR\\\\SMART\\\\ExternalClinicalDecisionSupport\\\\DecisionSupportInterventionEntity\\:\\:populateServiceWithFhirQuestionnaire\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/FHIR/SMART/ExternalClinicalDecisionSupport/PredictiveDSIServiceEntityTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/api/InternalApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Core\\\\Kernel\\:\\:getWebRoot\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/api/InternalFhirTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
